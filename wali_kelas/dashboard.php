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
        wali_kelas.id,
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
    ORDER BY kelas.tingkat, kelas.nama_kelas
");

mysqli_stmt_bind_param($query_wali, "i", $guru_id);
mysqli_stmt_execute($query_wali);
$result_wali = mysqli_stmt_get_result($query_wali);

$data_wali = mysqli_fetch_assoc($result_wali);

$total_siswa = 0;
$total_absensi = 0;
$absen_hari_ini = 0;

if ($data_wali) {
    $kelas_id = $data_wali['kelas_id'];

    $query_siswa = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM siswa
        WHERE kelas_id = ?
        AND status = 'aktif'
    ");

    mysqli_stmt_bind_param($query_siswa, "i", $kelas_id);
    mysqli_stmt_execute($query_siswa);
    $result_siswa = mysqli_stmt_get_result($query_siswa);
    $data_siswa = mysqli_fetch_assoc($result_siswa);

    $total_siswa = $data_siswa['total'];

    $query_absensi = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM absensi_harian
        WHERE kelas_id = ?
    ");

    mysqli_stmt_bind_param($query_absensi, "i", $kelas_id);
    mysqli_stmt_execute($query_absensi);
    $result_absensi = mysqli_stmt_get_result($query_absensi);
    $data_absensi = mysqli_fetch_assoc($result_absensi);

    $total_absensi = $data_absensi['total'];

    $tanggal_hari_ini = date('Y-m-d');

    $query_hari_ini = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM absensi_harian
        WHERE kelas_id = ?
        AND tanggal = ?
    ");

    mysqli_stmt_bind_param($query_hari_ini, "is", $kelas_id, $tanggal_hari_ini);
    mysqli_stmt_execute($query_hari_ini);
    $result_hari_ini = mysqli_stmt_get_result($query_hari_ini);
    $data_hari_ini = mysqli_fetch_assoc($result_hari_ini);

    $absen_hari_ini = $data_hari_ini['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali Kelas</title>
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

        .card-menu {
            text-decoration: none;
            color: #333;
        }

        .card-menu:hover {
            text-decoration: none;
            color: #333;
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

            .card-menu {
                display: block;
                margin-bottom: 15px;
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

            <h3>Dashboard Wali Kelas</h3>

            <?php if (!$data_wali) { ?>

                <div class="alert alert-warning">
                    Anda belum ditetapkan sebagai wali kelas pada tahun ajaran aktif.
                </div>

            <?php } else { ?>

                <div class="alert alert-info">
                    Wali Kelas:
                    <strong><?php echo htmlspecialchars($data_wali['nama_kelas']); ?></strong>
                    |
                    Tahun Ajaran:
                    <strong><?php echo htmlspecialchars($data_wali['tahun']); ?></strong>
                    |
                    Semester:
                    <strong><?php echo htmlspecialchars($data_wali['semester']); ?></strong>
                </div>

                <div class="row">

                    <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h4><?php echo $total_siswa; ?></h4>
                                <p class="mb-0">Total Siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h4><?php echo $total_absensi; ?></h4>
                                <p class="mb-0">Total Absensi Harian</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h4><?php echo $absen_hari_ini > 0 ? 'Sudah' : 'Belum'; ?></h4>
                                <p class="mb-0">Absensi Hari Ini</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">
                        <a href="rekap.php" class="card-menu">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h4>📊 Rekap Absensi</h4>
                                    <p class="mb-0">Melihat rekap absensi siswa kelas.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6">
                        <a href="siswa.php" class="card-menu">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h4>👨‍🎓 Data Siswa</h4>
                                    <p class="mb-0">Melihat data siswa pada kelas yang menjadi tanggung jawab.</p>
                                </div>
                            </div>
                        </a>
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