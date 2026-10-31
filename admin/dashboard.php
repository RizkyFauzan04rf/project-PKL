<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Sistem Absensi TKJ</title>

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

        .mobile-menu {
            display: none;
            background: #ffffff;
            border-bottom: 1px solid #ddd;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
        }

        .mobile-menu a:hover {
            background: #f1f1f1;
        }

        .mobile-menu .logout {
            color: #dc3545 !important;
            border-top: 1px solid #ddd;
        }

        .mobile-menu .logout:hover {
            color: #b02a37 !important;
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
            padding: 30px;
        }

        .menu-card {
            border: none;
            border-radius: 10px;
            transition: 0.2s;
            height: 100%;
        }

        .menu-card:hover {
            transform: translateY(-3px);
        }

        @media (max-width: 767px) {

            .navbar {
                padding: 8px 10px;
            }

            .navbar-brand {
                font-size: 16px;
            }

            .admin-name {
                font-size: 12px;
            }

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

            .sidebar a {
                padding: 11px 20px;
            }

            .content {
                padding: 15px;
            }

            .content h3 {
                font-size: 22px;
            }

            .menu-card {
                margin-bottom: 5px;
            }

            .menu-card .card-body {
                padding: 18px;
            }

            .menu-card h5 {
                font-size: 17px;
            }

            .menu-card p {
                font-size: 14px;
            }

            .menu-card .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {

            .navbar {
                padding: 8px 10px;
            }

            .navbar-brand {
                font-size: 14px;
            }

            .admin-name {
                font-size: 10px;
            }

            .menu-toggle {
                font-size: 18px;
                padding: 5px 9px;
            }

            .content {
                padding: 15px 10px;
            }
        }
    </style>

</head>

<body>

<nav class="navbar navbar-dark">

    <div class="container-fluid">

        <span class="navbar-brand">
            Sistem Absensi TKJ
        </span>

        <span class="admin-name">
            <?= htmlspecialchars($_SESSION['nama']); ?>
        </span>

        <a href="../auth/logout.php" class="logout-link">
            Logout
        </a>

        <button type="button" class="menu-toggle" onclick="toggleMenu()">
            ☰
        </button>

    </div>

</nav>

<div class="mobile-menu" id="mobileMenu">

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="siswa/index.php">
        👨‍🎓 Data Siswa
    </a>

    <a href="guru/index.php">
        👨‍🏫 Data Guru
    </a>

    <a href="kelas/index.php">
        🏫 Data Kelas
    </a>

    <a href="mapel/index.php">
        📚 Mata Pelajaran
    </a>

    <a href="tahun_ajaran/index.php">
        📅 Tahun Ajaran
    </a>

    <a href="mengajar/index.php">
        📝 Data Mengajar
    </a>

    <a href="wali_kelas/index.php">
        👤 Wali Kelas
    </a>

    <a href="sekretaris/index.php">
        📋 Sekretaris
    </a>

    <a href="absensi/index.php">
        📝 Absensi
    </a>

    <a href="rekap/index.php">
        📊 Rekap Absensi
    </a>

    <a href="../auth/logout.php" class="logout">
        Logout
    </a>

</div>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 sidebar p-0" id="sidebar">

            <div class="p-3">
                <strong>MENU ADMIN</strong>
            </div>

            <a href="dashboard.php">
                🏠 Dashboard
            </a>

            <a href="siswa/index.php">
                👨‍🎓 Data Siswa
            </a>

            <a href="guru/index.php">
                👨‍🏫 Data Guru
            </a>

            <a href="kelas/index.php">
                🏫 Data Kelas
            </a>

            <a href="mapel/index.php">
                📚 Mata Pelajaran
            </a>

            <a href="tahun_ajaran/index.php">
                📅 Tahun Ajaran
            </a>

            <a href="mengajar/index.php">
                📝 Data Mengajar
            </a>

            <a href="wali_kelas/index.php">
                👤 Wali Kelas
            </a>

            <a href="sekretaris/index.php">
                📋 Sekretaris
            </a>

            <a href="absensi/index.php">
                📝 Absensi
            </a>

            <a href="rekap/index.php">
                📊 Rekap Absensi
            </a>

        </div>

        <div class="col-md-10 content">

            <h3>
                Dashboard Admin
            </h3>

            <p class="text-muted">
                Selamat datang,
                <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>
            </p>

            <div class="row">

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>👨‍🎓 Data Siswa</h5>

                            <p class="text-muted">
                                Mengelola data siswa.
                            </p>

                            <a href="siswa/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>👨‍🏫 Data Guru</h5>

                            <p class="text-muted">
                                Mengelola data guru.
                            </p>

                            <a href="guru/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>🏫 Data Kelas</h5>

                            <p class="text-muted">
                                Mengelola data kelas.
                            </p>

                            <a href="kelas/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📚 Mata Pelajaran</h5>

                            <p class="text-muted">
                                Mengelola mata pelajaran.
                            </p>

                            <a href="mapel/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📅 Tahun Ajaran</h5>

                            <p class="text-muted">
                                Mengatur tahun ajaran dan semester.
                            </p>

                            <a href="tahun_ajaran/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📝 Data Mengajar</h5>

                            <p class="text-muted">
                                Mengatur guru, kelas dan mata pelajaran.
                            </p>

                            <a href="mengajar/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>👤 Wali Kelas</h5>

                            <p class="text-muted">
                                Mengatur wali kelas setiap periode.
                            </p>

                            <a href="wali_kelas/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📋 Sekretaris</h5>

                            <p class="text-muted">
                                Mengatur akun sekretaris kelas.
                            </p>

                            <a href="sekretaris/index.php" class="btn btn-primary">
                                Kelola
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📝 Absensi</h5>

                            <p class="text-muted">
                                Melihat dan mengelola absensi.
                            </p>

                            <a href="absensi/index.php" class="btn btn-primary">
                                Buka
                            </a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3 col-sm-6 mb-4">

                    <div class="card menu-card shadow-sm">

                        <div class="card-body">

                            <h5>📊 Rekap Absensi</h5>

                            <p class="text-muted">
                                Melihat rekapitulasi absensi siswa.
                            </p>

                            <a href="rekap/index.php" class="btn btn-primary">
                                Buka
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="../bootstrap/js/jquery.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>

<script>
function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>

</html>