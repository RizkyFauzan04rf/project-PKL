<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/koneksi.php";

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

$query_wali = mysqli_prepare($koneksi, "
    SELECT
        wali_kelas.kelas_id,
        kelas.nama_kelas,
        kelas.tingkat,
        tahun_ajaran.tahun,
        tahun_ajaran.semester
    FROM wali_kelas
    INNER JOIN kelas ON wali_kelas.kelas_id = kelas.id
    INNER JOIN tahun_ajaran ON wali_kelas.tahun_ajaran_id = tahun_ajaran.id
    WHERE wali_kelas.guru_id = ?
    AND tahun_ajaran.status = 'Aktif'
    LIMIT 1
");

mysqli_stmt_bind_param($query_wali, "i", $guru_id);
mysqli_stmt_execute($query_wali);
$result_wali = mysqli_stmt_get_result($query_wali);
$data_wali = mysqli_fetch_assoc($result_wali);

$tanggal_mulai = isset($_GET['tanggal_mulai']) && $_GET['tanggal_mulai'] != ''
    ? $_GET['tanggal_mulai']
    : date('Y-m-01');

$tanggal_akhir = isset($_GET['tanggal_akhir']) && $_GET['tanggal_akhir'] != ''
    ? $_GET['tanggal_akhir']
    : date('Y-m-d');

$data_rekap = [];
$data_cabut = [];

if ($data_wali) {
    $kelas_id = $data_wali['kelas_id'];

    $query_rekap = mysqli_prepare($koneksi, "
        SELECT
            siswa.id,
            siswa.nis,
            siswa.nama,
            SUM(CASE WHEN detail_absensi_harian.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
            SUM(CASE WHEN detail_absensi_harian.status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
            SUM(CASE WHEN detail_absensi_harian.status = 'Izin' THEN 1 ELSE 0 END) AS izin,
            SUM(CASE WHEN detail_absensi_harian.status = 'Alpa' THEN 1 ELSE 0 END) AS alpa
        FROM siswa
        LEFT JOIN detail_absensi_harian
            ON detail_absensi_harian.siswa_id = siswa.id
        LEFT JOIN absensi_harian
            ON detail_absensi_harian.absensi_id = absensi_harian.id
            AND absensi_harian.kelas_id = siswa.kelas_id
            AND absensi_harian.tanggal BETWEEN ? AND ?
        WHERE siswa.kelas_id = ?
        GROUP BY siswa.id, siswa.nis, siswa.nama
        ORDER BY siswa.nama
    ");

    mysqli_stmt_bind_param(
        $query_rekap,
        "ssi",
        $tanggal_mulai,
        $tanggal_akhir,
        $kelas_id
    );

    mysqli_stmt_execute($query_rekap);
    $result_rekap = mysqli_stmt_get_result($query_rekap);

    while ($row = mysqli_fetch_assoc($result_rekap)) {
        $data_rekap[] = $row;
    }

    $query_cabut = mysqli_prepare($koneksi, "
        SELECT
            siswa.nis,
            siswa.nama,
            mata_pelajaran.nama_mapel,
            absensi_mapel.tanggal,
            absensi_mapel.jam_ke,
            detail_absensi_mapel.keterangan
        FROM detail_absensi_mapel
        INNER JOIN siswa
            ON detail_absensi_mapel.siswa_id = siswa.id
        INNER JOIN absensi_mapel
            ON detail_absensi_mapel.absensi_mapel_id = absensi_mapel.id
        INNER JOIN mengajar
            ON absensi_mapel.mengajar_id = mengajar.id
        INNER JOIN mata_pelajaran
            ON mengajar.mapel_id = mata_pelajaran.id
        WHERE siswa.kelas_id = ?
        AND detail_absensi_mapel.status = 'Cabut'
        AND absensi_mapel.tanggal BETWEEN ? AND ?
        ORDER BY absensi_mapel.tanggal DESC, siswa.nama
    ");

    mysqli_stmt_bind_param(
        $query_cabut,
        "iss",
        $kelas_id,
        $tanggal_mulai,
        $tanggal_akhir
    );

    mysqli_stmt_execute($query_cabut);
    $result_cabut = mysqli_stmt_get_result($query_cabut);

    while ($row = mysqli_fetch_assoc($result_cabut)) {
        $data_cabut[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Wali Kelas</title>
    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css">
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

        .table-responsive {
            overflow-x: auto;
        }

        @media print {
            .navbar,
            .sidebar,
            .filter-box,
            .btn {
                display: none !important;
            }

            .content {
                padding: 0;
            }
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
                min-width: 750px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Sistem Absensi TKJ</a>
        <span class="admin-name"><?php echo htmlspecialchars($nama_guru); ?></span>
        <a class="logout-link" href="../auth/logout.php">Logout</a>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar" id="sidebar">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="rekap.php">📊 Rekap Absensi</a>
            <a href="siswa.php">👨‍🎓 Data Siswa</a>
            <a href="../auth/logout.php" style="color:#dc3545;">Logout</a>
        </div>

        <div class="col-md-10 content">

            <h3>Rekap Absensi</h3>

            <?php if (!$data_wali) { ?>

                <div class="alert alert-warning">
                    Anda belum ditetapkan sebagai wali kelas pada tahun ajaran aktif.
                </div>

            <?php } else { ?>

                <div class="alert alert-info">
                    Kelas:
                    <strong><?php echo htmlspecialchars($data_wali['nama_kelas']); ?></strong>
                    |
                    Tahun Ajaran:
                    <strong><?php echo htmlspecialchars($data_wali['tahun']); ?></strong>
                    |
                    Semester:
                    <strong><?php echo htmlspecialchars($data_wali['semester']); ?></strong>
                </div>

                <div class="card filter-box mb-4">
                    <div class="card-body">

                        <form method="get">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Mulai</label>
                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            class="form-control"
                                            value="<?php echo htmlspecialchars($tanggal_mulai); ?>"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Akhir</label>
                                        <input
                                            type="date"
                                            name="tanggal_akhir"
                                            class="form-control"
                                            value="<?php echo htmlspecialchars($tanggal_akhir); ?>"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            Tampilkan
                                        </button>

                                        <button type="button" onclick="window.print()" class="btn btn-secondary">
                                            Cetak
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">

                        <h4>Rekap Absensi Harian</h4>

                        <p>
                            Periode:
                            <strong><?php echo htmlspecialchars($tanggal_mulai); ?></strong>
                            sampai
                            <strong><?php echo htmlspecialchars($tanggal_akhir); ?></strong>
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Hadir</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Alpa</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (empty($data_rekap)) { ?>

                                        <tr>
                                            <td colspan="8" class="text-center">
                                                Belum ada data absensi.
                                            </td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php $no = 1; ?>

                                        <?php foreach ($data_rekap as $row) { ?>

                                            <?php
                                            $total = $row['hadir'] + $row['sakit'] + $row['izin'] + $row['alpa'];
                                            ?>

                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td><?php echo $row['hadir']; ?></td>
                                                <td><?php echo $row['sakit']; ?></td>
                                                <td><?php echo $row['izin']; ?></td>
                                                <td><?php echo $row['alpa']; ?></td>
                                                <td><?php echo $total; ?></td>
                                            </tr>

                                        <?php } ?>

                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <h4>Rekap Siswa Cabut</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Tanggal</th>
                                        <th>Jam Ke</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (empty($data_cabut)) { ?>

                                        <tr>
                                            <td colspan="7" class="text-center">
                                                Tidak ada data siswa cabut pada periode ini.
                                            </td>
                                        </tr>

                                    <?php } else { ?>

                                        <?php $no = 1; ?>

                                        <?php foreach ($data_cabut as $row) { ?>

                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_mapel']); ?></td>
                                                <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                                <td><?php echo htmlspecialchars($row['jam_ke']); ?></td>
                                                <td>
                                                    <?php echo !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-'; ?>
                                                </td>
                                            </tr>

                                        <?php } ?>

                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            <?php } ?>

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