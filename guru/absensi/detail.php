<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/koneksi.php";

$user_id = $_SESSION['user_id'];

$query_guru = mysqli_prepare($koneksi, "SELECT id, nama FROM guru WHERE user_id = ?");
mysqli_stmt_bind_param($query_guru, "i", $user_id);
mysqli_stmt_execute($query_guru);
$result_guru = mysqli_stmt_get_result($query_guru);
$data_guru = mysqli_fetch_assoc($result_guru);

if (!$data_guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = $data_guru['id'];
$nama_guru = $data_guru['nama'];

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0 || !in_array($jenis, ['harian', 'mapel'])) {
    die("Data absensi tidak valid.");
}

$judul = "";
$info = [];
$detail = [];

if ($jenis == 'harian') {
    $query = mysqli_prepare($koneksi, "
        SELECT 
            absensi_harian.id,
            absensi_harian.tanggal,
            absensi_harian.waktu_absen,
            kelas.id AS kelas_id,
            kelas.nama_kelas,
            users.nama AS dibuat_oleh
        FROM absensi_harian
        INNER JOIN kelas ON absensi_harian.kelas_id = kelas.id
        INNER JOIN users ON absensi_harian.dibuat_oleh = users.id
        INNER JOIN mengajar ON mengajar.kelas_id = absensi_harian.kelas_id
        WHERE absensi_harian.id = ?
        AND mengajar.guru_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query, "ii", $id, $guru_id);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data absensi harian tidak ditemukan atau tidak dapat diakses.");
    }

    $judul = "Detail Absensi Harian";

    $info = [
        'Tanggal' => $data['tanggal'],
        'Kelas' => $data['nama_kelas'],
        'Dibuat Oleh' => $data['dibuat_oleh'],
        'Waktu Absen' => $data['waktu_absen']
    ];

    $query_detail = mysqli_prepare($koneksi, "
        SELECT 
            siswa.nis,
            siswa.nama,
            detail_absensi_harian.status,
            detail_absensi_harian.keterangan
        FROM detail_absensi_harian
        INNER JOIN siswa ON detail_absensi_harian.siswa_id = siswa.id
        WHERE detail_absensi_harian.absensi_id = ?
        ORDER BY siswa.nama
    ");

    mysqli_stmt_bind_param($query_detail, "i", $id);
    mysqli_stmt_execute($query_detail);
    $result_detail = mysqli_stmt_get_result($query_detail);

    while ($row = mysqli_fetch_assoc($result_detail)) {
        $detail[] = $row;
    }

} else {
    $query = mysqli_prepare($koneksi, "
        SELECT 
            absensi_mapel.id,
            absensi_mapel.tanggal,
            absensi_mapel.jam_ke,
            absensi_mapel.waktu_absen,
            kelas.id AS kelas_id,
            kelas.nama_kelas,
            mata_pelajaran.nama_mapel,
            tahun_ajaran.tahun,
            tahun_ajaran.semester,
            users.nama AS dibuat_oleh
        FROM absensi_mapel
        INNER JOIN mengajar ON absensi_mapel.mengajar_id = mengajar.id
        INNER JOIN kelas ON mengajar.kelas_id = kelas.id
        INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
        INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
        INNER JOIN users ON absensi_mapel.dibuat_oleh = users.id
        WHERE absensi_mapel.id = ?
        AND mengajar.guru_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query, "ii", $id, $guru_id);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data absensi mata pelajaran tidak ditemukan atau tidak dapat diakses.");
    }

    $judul = "Detail Absensi Mata Pelajaran";

    $info = [
        'Tanggal' => $data['tanggal'],
        'Kelas' => $data['nama_kelas'],
        'Mata Pelajaran' => $data['nama_mapel'],
        'Jam Ke' => $data['jam_ke'],
        'Tahun Ajaran' => $data['tahun'],
        'Semester' => $data['semester'],
        'Dibuat Oleh' => $data['dibuat_oleh'],
        'Waktu Absen' => $data['waktu_absen']
    ];

    $query_detail = mysqli_prepare($koneksi, "
        SELECT 
            siswa.nis,
            siswa.nama,
            detail_absensi_mapel.status,
            detail_absensi_mapel.keterangan,
            detail_absensi_mapel.sumber
        FROM detail_absensi_mapel
        INNER JOIN siswa ON detail_absensi_mapel.siswa_id = siswa.id
        WHERE detail_absensi_mapel.absensi_mapel_id = ?
        ORDER BY siswa.nama
    ");

    mysqli_stmt_bind_param($query_detail, "i", $id);
    mysqli_stmt_execute($query_detail);
    $result_detail = mysqli_stmt_get_result($query_detail);

    while ($row = mysqli_fetch_assoc($result_detail)) {
        $detail[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($judul); ?></title>
    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/bootstrap.min.css">
    <style>
        .navbar {
            background: #343a40;
            min-height: 50px;
        }

        .navbar .container-fluid {
            display: flex;
            align-items: center;
        }

        .navbar-brand {
            color: white !important;
            margin-right: 15px;
        }

        .admin-name {
            color: white;
        }

        .logout-link {
            color: #dc3545 !important;
            text-decoration: none;
            margin-left: auto;
        }

        .logout-link:hover {
            color: #b02a37 !important;
            text-decoration: none;
        }

        .menu-toggle {
            display: none;
            background: transparent;
            border: 1px solid #777;
            color: white;
            font-size: 18px;
            line-height: 1;
            padding: 5px 9px;
            border-radius: 4px;
            cursor: pointer;
        }

        .sidebar {
            min-height: calc(100vh - 50px);
            background: #ffffff;
            border-right: 1px solid #ddd;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #f1f1f1;
        }

        .content {
            padding: 25px;
        }

        .info-table {
            margin-bottom: 25px;
        }

        .info-table th {
            width: 180px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 767px) {
            .logout-link {
                display: none;
            }

            .menu-toggle {
                display: inline-block;
                margin-left: auto;
            }

            .sidebar {
                display: none;
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }

            .sidebar.active {
                display: block;
            }

            .content {
                padding: 15px;
            }

            .table {
                min-width: 650px;
            }

            .info-table {
                min-width: 500px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">Sistem Absensi TKJ</a>
        <span class="admin-name"><?php echo htmlspecialchars($nama_guru); ?></span>
        <a class="logout-link" href="../../auth/logout.php">Logout</a>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar" id="sidebar">
            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="index.php">📝 Absensi</a>
            <a href="harian.php">📅 Absensi Harian</a>
            <a href="mapel.php">📚 Absensi Mapel</a>
            <a href="../../auth/logout.php" style="color:#dc3545;">Logout</a>
        </div>

        <div class="col-md-10 content">
            <h3><?php echo htmlspecialchars($judul); ?></h3>

            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered info-table">
                            <tbody>
                                <?php foreach ($info as $label => $value) { ?>
                                    <tr>
                                        <th><?php echo htmlspecialchars($label); ?></th>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <h4>Data Siswa</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status</th>
                                    <?php if ($jenis == 'mapel') { ?>
                                        <th>Sumber</th>
                                    <?php } ?>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (empty($detail)) { ?>

                                    <tr>
                                        <td colspan="<?php echo ($jenis == 'mapel') ? '6' : '5'; ?>" class="text-center">
                                            Belum ada detail absensi.
                                        </td>
                                    </tr>

                                <?php } else { ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($detail as $row) { ?>

                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>

                                            <?php if ($jenis == 'mapel') { ?>
                                                <td><?php echo htmlspecialchars($row['sumber']); ?></td>
                                            <?php } ?>

                                            <td>
                                                <?php echo !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-'; ?>
                                            </td>
                                        </tr>

                                    <?php } ?>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                    <a href="index.php" class="btn btn-secondary">← Kembali</a>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>