<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/koneksi.php";

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM guru
    WHERE user_id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_guru = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_guru) == 0) {
    die("Data guru belum terdaftar.");
}

$data_guru = mysqli_fetch_assoc($result_guru);
$guru_id = $data_guru['id'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total_mengajar
    FROM mengajar
    WHERE guru_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result_mengajar = mysqli_stmt_get_result($stmt);
$data_mengajar = mysqli_fetch_assoc($result_mengajar);
$total_mengajar = $data_mengajar['total_mengajar'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total_kelas
    FROM mengajar
    WHERE guru_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result_kelas = mysqli_stmt_get_result($stmt);

$data_kelas = mysqli_fetch_assoc($result_kelas);
$total_kelas = $data_kelas['total_kelas'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total_wali
    FROM wali_kelas
    WHERE guru_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$result_wali = mysqli_stmt_get_result($stmt);
$data_wali = mysqli_fetch_assoc($result_wali);
$total_wali = $data_wali['total_wali'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total_absensi
    FROM absensi_mapel
    WHERE dibuat_oleh = ?
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_absensi = mysqli_stmt_get_result($stmt);
$data_absensi = mysqli_fetch_assoc($result_absensi);
$total_absensi = $data_absensi['total_absensi'];
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Sistem Absensi TKJ</title>

    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css">

    <style>
        body {
            background: #f5f6f8;
        }

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
            padding: 20px 15px;
            text-align: center;
        }

        .content h3 {
            font-size: 22px;
        }

        .welcome-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e5e5e5;
            text-align: center;
            height: 100%;
        }

        .stat-card h2 {
            margin: 0;
            font-size: 30px;
        }

        .stat-card p {
            margin: 5px 0 0;
            color: #6c757d;
        }

        .dashboard-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px 15px;
            text-align: center;
            border: 1px solid #e5e5e5;
            height: 100%;
        }

        .dashboard-card h5 {
            margin-top: 10px;
            margin-bottom: 8px;
        }

        .dashboard-card p {
            color: #6c757d;
            margin-bottom: 15px;
        }

        .dashboard-icon {
            font-size: 38px;
        }

        #mobileMenu {
            display: none;
        }

        #mobileMenu.active {
            display: block;
            background: #ffffff;
            border-bottom: 1px solid #ddd;
        }

        #mobileMenu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
        }

        #mobileMenu a:hover {
            background: #f1f1f1;
        }

        #mobileMenu .mobile-logout {
            color: #dc3545;
            border-top: 1px solid #ddd;
        }

        @media (min-width: 768px) {
            .content {
                padding: 30px;
                text-align: left;
            }

            .content h3 {
                font-size: 24px;
            }

            .welcome-box {
                padding: 25px;
            }

            .dashboard-card {
                padding: 25px 15px;
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
            }

            .welcome-box {
                text-align: center;
            }

            .stat-card {
                margin-bottom: 15px;
            }

            .dashboard-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Sistem Absensi TKJ</span>
        <span class="admin-name"><?= htmlspecialchars($_SESSION['nama']); ?></span>

        <a href="../auth/logout.php" class="logout-link">Logout</a>

        <button class="menu-toggle" type="button" onclick="$('#mobileMenu').toggleClass('active');">
            ☰
        </button>
    </div>
</nav>

<div id="mobileMenu">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="absensi/index.php">📝 Absensi</a>
    <a href="absensi/harian.php">📋 Absensi Harian</a>
    <a href="absensi/mapel.php">📚 Absensi Mata Pelajaran</a>
    <a href="../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <strong>MENU GURU</strong>
            </div>

            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="absensi/index.php">📝 Absensi</a>
            <a href="absensi/harian.php">📋 Absensi Harian</a>
            <a href="absensi/mapel.php">📚 Absensi Mata Pelajaran</a>
        </div>

        <div class="col-md-10 content">

            <div class="welcome-box shadow-sm">
                <h3>Dashboard Guru</h3>
                <p class="text-muted mb-1">
                    Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>
                </p>
                <p class="mb-0">
                    Kelola absensi siswa sesuai kelas dan mata pelajaran yang Anda ampu.
                </p>
            </div>

            <div class="row mb-4">

                <div class="col-md-3 mb-3">
                    <div class="stat-card shadow-sm">
                        <h2><?= $total_mengajar; ?></h2>
                        <p>Data Mengajar</p>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="stat-card shadow-sm">
                        <h2><?= $total_kelas; ?></h2>
                        <p>Kelas Mengajar</p>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="stat-card shadow-sm">
                        <h2><?= $total_wali; ?></h2>
                        <p>Wali Kelas</p>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="stat-card shadow-sm">
                        <h2><?= $total_absensi; ?></h2>
                        <p>Absensi Mapel</p>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-4 mb-4">
                    <div class="dashboard-card shadow-sm">
                        <div class="dashboard-icon">📝</div>
                        <h5>Absensi</h5>
                        <p>
                            Melihat dan mengelola absensi siswa.
                        </p>
                        <a href="absensi/index.php" class="btn btn-primary">
                            Buka Absensi
                        </a>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dashboard-card shadow-sm">
                        <div class="dashboard-icon">📋</div>
                        <h5>Absensi Harian</h5>
                        <p>
                            Melakukan absensi awal siswa berdasarkan kelas.
                        </p>
                        <a href="absensi/harian.php" class="btn btn-primary">
                            Absen Harian
                        </a>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dashboard-card shadow-sm">
                        <div class="dashboard-icon">📚</div>
                        <h5>Absensi Mata Pelajaran</h5>
                        <p>
                            Melakukan absensi siswa berdasarkan mata pelajaran.
                        </p>
                        <a href="absensi/mapel.php" class="btn btn-primary">
                            Absen Mapel
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="../bootstrap/js/jquery.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>