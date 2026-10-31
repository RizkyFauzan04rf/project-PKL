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

$success = isset($_GET['success']);
$error = "";

$kelas_mapel = [];

$query_mengajar = mysqli_prepare($koneksi, "
    SELECT 
        mengajar.id,
        kelas.id AS kelas_id,
        kelas.nama_kelas,
        kelas.tingkat,
        mata_pelajaran.id AS mapel_id,
        mata_pelajaran.nama_mapel,
        tahun_ajaran.id AS tahun_ajaran_id,
        tahun_ajaran.tahun,
        tahun_ajaran.semester
    FROM mengajar
    INNER JOIN kelas ON mengajar.kelas_id = kelas.id
    INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
    INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
    WHERE mengajar.guru_id = ?
    AND tahun_ajaran.status = 'Aktif'
    ORDER BY kelas.tingkat, kelas.nama_kelas, mata_pelajaran.nama_mapel
");

mysqli_stmt_bind_param($query_mengajar, "i", $guru_id);
mysqli_stmt_execute($query_mengajar);
$result_mengajar = mysqli_stmt_get_result($query_mengajar);

while ($row = mysqli_fetch_assoc($result_mengajar)) {
    $kelas_mapel[] = $row;
}

$mengajar_id = isset($_POST['mengajar_id']) ? (int) $_POST['mengajar_id'] : (isset($_GET['mengajar_id']) ? (int) $_GET['mengajar_id'] : 0);
$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : (isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d'));
$jam_ke = isset($_POST['jam_ke']) ? (int) $_POST['jam_ke'] : (isset($_GET['jam_ke']) ? (int) $_GET['jam_ke'] : 1);

$data_mengajar = null;

if ($mengajar_id > 0) {
    $query_data = mysqli_prepare($koneksi, "
        SELECT 
            mengajar.id,
            mengajar.kelas_id,
            kelas.nama_kelas,
            kelas.tingkat,
            mata_pelajaran.nama_mapel,
            tahun_ajaran.tahun,
            tahun_ajaran.semester
        FROM mengajar
        INNER JOIN kelas ON mengajar.kelas_id = kelas.id
        INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
        INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
        WHERE mengajar.id = ?
        AND mengajar.guru_id = ?
        AND tahun_ajaran.status = 'Aktif'
    ");

    mysqli_stmt_bind_param($query_data, "ii", $mengajar_id, $guru_id);
    mysqli_stmt_execute($query_data);
    $result_data = mysqli_stmt_get_result($query_data);
    $data_mengajar = mysqli_fetch_assoc($result_data);

    if (!$data_mengajar) {
        $mengajar_id = 0;
    }
}

$absensi_mapel_id = 0;
$data_detail = [];

if ($mengajar_id > 0 && $data_mengajar) {
    $query_absen = mysqli_prepare($koneksi, "
        SELECT id
        FROM absensi_mapel
        WHERE mengajar_id = ?
        AND tanggal = ?
        AND jam_ke = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query_absen, "isi", $mengajar_id, $tanggal, $jam_ke);
    mysqli_stmt_execute($query_absen);
    $result_absen = mysqli_stmt_get_result($query_absen);
    $data_absen = mysqli_fetch_assoc($result_absen);

    if ($data_absen) {
        $absensi_mapel_id = $data_absen['id'];

        $query_detail = mysqli_prepare($koneksi, "
            SELECT siswa_id, status, keterangan
            FROM detail_absensi_mapel
            WHERE absensi_mapel_id = ?
        ");

        mysqli_stmt_bind_param($query_detail, "i", $absensi_mapel_id);
        mysqli_stmt_execute($query_detail);
        $result_detail = mysqli_stmt_get_result($query_detail);

        while ($row = mysqli_fetch_assoc($result_detail)) {
            $data_detail[$row['siswa_id']] = $row;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $mengajar_id = (int) $_POST['mengajar_id'];
    $tanggal = $_POST['tanggal'];
    $jam_ke = (int) $_POST['jam_ke'];

    $query_validasi = mysqli_prepare($koneksi, "
        SELECT 
            mengajar.id,
            mengajar.kelas_id
        FROM mengajar
        INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
        WHERE mengajar.id = ?
        AND mengajar.guru_id = ?
        AND tahun_ajaran.status = 'Aktif'
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query_validasi, "ii", $mengajar_id, $guru_id);
    mysqli_stmt_execute($query_validasi);
    $result_validasi = mysqli_stmt_get_result($query_validasi);
    $data_validasi = mysqli_fetch_assoc($result_validasi);

    if (!$data_validasi) {
        $error = "Data mengajar tidak valid.";
    } elseif (empty($tanggal)) {
        $error = "Tanggal wajib diisi.";
    } elseif ($jam_ke < 1) {
        $error = "Jam ke tidak valid.";
    } else {
        $kelas_id = $data_validasi['kelas_id'];

        $query_siswa = mysqli_prepare($koneksi, "
            SELECT id
            FROM siswa
            WHERE kelas_id = ?
            AND status = 'aktif'
            ORDER BY nama
        ");

        mysqli_stmt_bind_param($query_siswa, "i", $kelas_id);
        mysqli_stmt_execute($query_siswa);
        $result_siswa = mysqli_stmt_get_result($query_siswa);

        $siswa_list = [];

        while ($siswa = mysqli_fetch_assoc($result_siswa)) {
            $siswa_list[] = $siswa['id'];
        }

        if (empty($siswa_list)) {
            $error = "Tidak ada siswa aktif pada kelas tersebut.";
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                $query_cek = mysqli_prepare($koneksi, "
                    SELECT id
                    FROM absensi_mapel
                    WHERE mengajar_id = ?
                    AND tanggal = ?
                    AND jam_ke = ?
                    LIMIT 1
                ");

                mysqli_stmt_bind_param($query_cek, "isi", $mengajar_id, $tanggal, $jam_ke);
                mysqli_stmt_execute($query_cek);
                $result_cek = mysqli_stmt_get_result($query_cek);
                $data_cek = mysqli_fetch_assoc($result_cek);

                if ($data_cek) {
                    $absensi_mapel_id = $data_cek['id'];

                    $query_update = mysqli_prepare($koneksi, "
                        UPDATE detail_absensi_mapel
                        SET status = ?, sumber = 'guru', keterangan = ?
                        WHERE absensi_mapel_id = ?
                        AND siswa_id = ?
                    ");

                    foreach ($siswa_list as $siswa_id) {
                        $status = isset($_POST['status'][$siswa_id]) ? $_POST['status'][$siswa_id] : 'Hadir';
                        $keterangan = isset($_POST['keterangan'][$siswa_id]) ? trim($_POST['keterangan'][$siswa_id]) : '';

                        $status_valid = ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Cabut'];

                        if (!in_array($status, $status_valid)) {
                            $status = 'Hadir';
                        }

                        mysqli_stmt_bind_param($query_update, "ssii", $status, $keterangan, $absensi_mapel_id, $siswa_id);
                        mysqli_stmt_execute($query_update);
                    }
                } else {
                    $query_insert_absen = mysqli_prepare($koneksi, "
                        INSERT INTO absensi_mapel
                        (mengajar_id, tanggal, jam_ke, dibuat_oleh)
                        VALUES (?, ?, ?, ?)
                    ");

                    mysqli_stmt_bind_param($query_insert_absen, "isii", $mengajar_id, $tanggal, $jam_ke, $user_id);
                    mysqli_stmt_execute($query_insert_absen);

                    $absensi_mapel_id = mysqli_insert_id($koneksi);

                    $query_insert_detail = mysqli_prepare($koneksi, "
                        INSERT INTO detail_absensi_mapel
                        (absensi_mapel_id, siswa_id, status, sumber, keterangan)
                        VALUES (?, ?, ?, 'guru', ?)
                    ");

                    foreach ($siswa_list as $siswa_id) {
                        $status = isset($_POST['status'][$siswa_id]) ? $_POST['status'][$siswa_id] : 'Hadir';
                        $keterangan = isset($_POST['keterangan'][$siswa_id]) ? trim($_POST['keterangan'][$siswa_id]) : '';

                        $status_valid = ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Cabut'];

                        if (!in_array($status, $status_valid)) {
                            $status = 'Hadir';
                        }

                        mysqli_stmt_bind_param($query_insert_detail, "iiss", $absensi_mapel_id, $siswa_id, $status, $keterangan);
                        mysqli_stmt_execute($query_insert_detail);
                    }
                }

                mysqli_commit($koneksi);

                header("Location: mapel.php?success=1&mengajar_id=" . $mengajar_id . "&tanggal=" . urlencode($tanggal) . "&jam_ke=" . $jam_ke);
                exit;
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = "Gagal menyimpan absensi.";
            }
        }
    }

    $data_mengajar = null;

    if ($mengajar_id > 0) {
        $query_data = mysqli_prepare($koneksi, "
            SELECT 
                mengajar.id,
                mengajar.kelas_id,
                kelas.nama_kelas,
                kelas.tingkat,
                mata_pelajaran.nama_mapel,
                tahun_ajaran.tahun,
                tahun_ajaran.semester
            FROM mengajar
            INNER JOIN kelas ON mengajar.kelas_id = kelas.id
            INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
            INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
            WHERE mengajar.id = ?
            AND mengajar.guru_id = ?
            AND tahun_ajaran.status = 'Aktif'
            LIMIT 1
        ");

        mysqli_stmt_bind_param($query_data, "ii", $mengajar_id, $guru_id);
        mysqli_stmt_execute($query_data);
        $result_data = mysqli_stmt_get_result($query_data);
        $data_mengajar = mysqli_fetch_assoc($result_data);
    }

    if ($data_mengajar) {
        $query_absen = mysqli_prepare($koneksi, "
            SELECT id
            FROM absensi_mapel
            WHERE mengajar_id = ?
            AND tanggal = ?
            AND jam_ke = ?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($query_absen, "isi", $mengajar_id, $tanggal, $jam_ke);
        mysqli_stmt_execute($query_absen);
        $result_absen = mysqli_stmt_get_result($query_absen);
        $data_absen = mysqli_fetch_assoc($result_absen);

        if ($data_absen) {
            $absensi_mapel_id = $data_absen['id'];

            $query_detail = mysqli_prepare($koneksi, "
                SELECT siswa_id, status, keterangan
                FROM detail_absensi_mapel
                WHERE absensi_mapel_id = ?
            ");

            mysqli_stmt_bind_param($query_detail, "i", $absensi_mapel_id);
            mysqli_stmt_execute($query_detail);
            $result_detail = mysqli_stmt_get_result($query_detail);

            $data_detail = [];

            while ($row = mysqli_fetch_assoc($result_detail)) {
                $data_detail[$row['siswa_id']] = $row;
            }
        }
    }
}

$siswa_data = [];

if ($data_mengajar) {
    $kelas_id = $data_mengajar['kelas_id'];

    $query_siswa = mysqli_prepare($koneksi, "
        SELECT id, nis, nama
        FROM siswa
        WHERE kelas_id = ?
        AND status = 'aktif'
        ORDER BY nama
    ");

    mysqli_stmt_bind_param($query_siswa, "i", $kelas_id);
    mysqli_stmt_execute($query_siswa);
    $result_siswa = mysqli_stmt_get_result($query_siswa);

    while ($row = mysqli_fetch_assoc($result_siswa)) {
        $siswa_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mapel - Guru</title>
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

            .form-control,
            .btn {
                width: 100%;
            }

            .table {
                min-width: 700px;
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
            <h3>Absensi Mata Pelajaran</h3>
            <p>Silakan pilih kelas, mata pelajaran, tanggal, dan jam ke.</p>

            <?php if ($success) { ?>
                <div class="alert alert-success">
                    Absensi mata pelajaran berhasil disimpan.
                </div>
            <?php } ?>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <div class="card">
                <div class="card-body">

                    <form method="post">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelas - Mata Pelajaran</label>
                                    <select name="mengajar_id" class="form-control" onchange="this.form.submit()" required>
                                        <option value="">-- Pilih Kelas - Mata Pelajaran --</option>

                                        <?php foreach ($kelas_mapel as $item) { ?>
                                            <option value="<?php echo $item['id']; ?>" <?php echo ($mengajar_id == $item['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($item['nama_kelas'] . ' - ' . $item['nama_mapel'] . ' - ' . $item['tahun'] . ' ' . $item['semester']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jam Ke</label>
                                    <select name="jam_ke" class="form-control">
                                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($jam_ke == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <?php if ($data_mengajar) { ?>

                            <div class="alert alert-info">
                                Kelas: <strong><?php echo htmlspecialchars($data_mengajar['nama_kelas']); ?></strong>
                                |
                                Mata Pelajaran: <strong><?php echo htmlspecialchars($data_mengajar['nama_mapel']); ?></strong>
                            </div>

                            <?php if ($absensi_mapel_id > 0) { ?>
                                <div class="alert alert-warning">
                                    Absensi untuk mata pelajaran, tanggal, dan jam ke ini sudah ada.
                                    Data di bawah dapat diperbarui.
                                </div>
                            <?php } ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="60">No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th width="180">Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php if (empty($siswa_data)) { ?>

                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    Tidak ada siswa aktif.
                                                </td>
                                            </tr>

                                        <?php } else { ?>

                                            <?php $no = 1; ?>

                                            <?php foreach ($siswa_data as $siswa) { ?>

                                                <?php
                                                $status_siswa = isset($data_detail[$siswa['id']]) ? $data_detail[$siswa['id']]['status'] : 'Hadir';
                                                $keterangan_siswa = isset($data_detail[$siswa['id']]) ? $data_detail[$siswa['id']]['keterangan'] : '';
                                                ?>

                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                                    <td><?php echo htmlspecialchars($siswa['nama']); ?></td>
                                                    <td>
                                                        <select name="status[<?php echo $siswa['id']; ?>]" class="form-control">
                                                            <option value="Hadir" <?php echo ($status_siswa == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                            <option value="Sakit" <?php echo ($status_siswa == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                                                            <option value="Izin" <?php echo ($status_siswa == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                                                            <option value="Alpa" <?php echo ($status_siswa == 'Alpa') ? 'selected' : ''; ?>>Alpa</option>
                                                            <option value="Cabut" <?php echo ($status_siswa == 'Cabut') ? 'selected' : ''; ?>>Cabut</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="keterangan[<?php echo $siswa['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($keterangan_siswa); ?>">
                                                    </td>
                                                </tr>

                                            <?php } ?>

                                        <?php } ?>

                                    </tbody>
                                </table>
                            </div>

                            <?php if (!empty($siswa_data)) { ?>
                                <button type="submit" name="simpan" class="btn btn-primary">
                                    <?php echo ($absensi_mapel_id > 0) ? 'Update Absensi' : 'Simpan Absensi'; ?>
                                </button>
                            <?php } ?>

                        <?php } ?>

                    </form>

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