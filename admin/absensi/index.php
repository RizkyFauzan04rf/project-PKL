<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../dashboard.php");
    exit;
}

require_once "../../config/koneksi.php";

$query = mysqli_query($koneksi, "
    SELECT
        absensi_harian.id,
        absensi_harian.tanggal,
        absensi_harian.waktu_absen,
        kelas.nama_kelas,
        users.nama AS dibuat_oleh
    FROM absensi_harian
    INNER JOIN kelas ON absensi_harian.kelas_id = kelas.id
    INNER JOIN users ON absensi_harian.dibuat_oleh = users.id
    ORDER BY absensi_harian.tanggal DESC, absensi_harian.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Absensi - Sistem Absensi TKJ</title>

    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/dataTables.bootstrap.min.css">

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

        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
        }

        @media (max-width: 767px) {
            .navbar {
                padding: 0 15px;
            }

            .navbar .container-fluid {
                padding-left: 0;
                padding-right: 0;
            }

            .navbar-brand {
                font-size: 15px;
                margin-right: 12px;
            }

            .admin-name {
                font-size: 11px;
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
            }

            .content {
                padding: 25px 15px;
                text-align: center;
            }

            .content > .d-flex {
                display: flex !important;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .content > .d-flex > div {
                width: 100%;
            }

            .content > .d-flex h3 {
                margin-bottom: 8px;
            }

            .content > .d-flex .btn {
                margin-top: 10px;
            }

            .table-container {
                width: 100%;
                padding: 15px;
                overflow-x: auto;
                text-align: left;
            }

            .table {
                min-width: 700px;
            }

            .dataTables_wrapper {
                text-align: left;
            }

            .dataTables_filter {
                float: none !important;
                text-align: center !important;
                margin-bottom: 10px;
            }

            .dataTables_length {
                float: none !important;
                text-align: center !important;
                margin-bottom: 10px;
            }

            .dataTables_info {
                text-align: center;
            }

            .dataTables_paginate {
                float: none !important;
                text-align: center !important;
                margin-top: 10px;
            }

            h3 {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding-left: 12px;
                padding-right: 12px;
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
                padding: 20px 10px;
            }

            .table-container {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">

        <span class="navbar-brand">Sistem Absensi TKJ</span>

        <span class="admin-name">
            <?= htmlspecialchars($_SESSION['nama']); ?>
        </span>

        <a href="../../auth/logout.php" class="logout-link">
            Logout
        </a>

        <button type="button" class="menu-toggle" onclick="toggleMenu()">
            ☰
        </button>

    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">

    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
    <a href="../guru/index.php">👨‍🏫 Data Guru</a>
    <a href="../kelas/index.php">🏫 Data Kelas</a>
    <a href="../mapel/index.php">📚 Mata Pelajaran</a>
    <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
    <a href="../mengajar/index.php">📝 Data Mengajar</a>
    <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
    <a href="../sekretaris/index.php">📋 Sekretaris</a>
    <a href="index.php">📝 Absensi</a>
    <a href="../rekap/index.php">📊 Rekap Absensi</a>
    <a href="../../auth/logout.php" class="logout">Logout</a>

</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">

            <div class="p-3">
                <strong>MENU ADMIN</strong>
            </div>

            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
            <a href="../guru/index.php">👨‍🏫 Data Guru</a>
            <a href="../kelas/index.php">🏫 Data Kelas</a>
            <a href="../mapel/index.php">📚 Mata Pelajaran</a>
            <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
            <a href="../mengajar/index.php">📝 Data Mengajar</a>
            <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
            <a href="../sekretaris/index.php">📋 Sekretaris</a>
            <a href="index.php">📝 Absensi</a>
            <a href="../rekap/index.php">📊 Rekap Absensi</a>

        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h3>Data Absensi</h3>
                    <p class="text-muted mb-0">
                        Melihat data absensi harian siswa
                    </p>
                </div>

            </div>

            <div class="table-container shadow-sm">

                <table id="dataTable" class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kelas</th>
                            <th>Dibuat Oleh</th>
                            <th>Waktu Absen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($query)) {
                        ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tanggal']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['nama_kelas']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['dibuat_oleh']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['waktu_absen']); ?>
                            </td>

                            <td>

                                <a href="detail.php?id=<?= $data['id']; ?>"
                                   class="btn btn-info btn-sm">
                                    Detail
                                </a>

                                <a href="hapus.php?id=<?= $data['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                    Hapus
                                </a>

                            </td>

                        </tr>

                        <?php
                        }
                        ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>
<script src="../../bootstrap/js/jquery.dataTables.js"></script>
<script src="../../bootstrap/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
});

function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>
</html>