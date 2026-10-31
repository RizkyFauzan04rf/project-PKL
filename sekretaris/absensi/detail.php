<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('sekretaris', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, "
    SELECT
        absensi_harian.id,
        absensi_harian.tanggal,
        absensi_harian.waktu_absen,
        absensi_harian.kelas_id,
        kelas.nama_kelas,
        users.nama AS dibuat_oleh
    FROM absensi_harian
    INNER JOIN kelas ON absensi_harian.kelas_id = kelas.id
    INNER JOIN users ON absensi_harian.dibuat_oleh = users.id
    INNER JOIN sekretaris ON sekretaris.kelas_id = absensi_harian.kelas_id
    WHERE absensi_harian.id = ?
    AND sekretaris.user_id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}

$absensi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT
        siswa.nis,
        siswa.nama,
        siswa.jenis_kelamin,
        detail_absensi_harian.status,
        detail_absensi_harian.keterangan
    FROM detail_absensi_harian
    INNER JOIN siswa ON detail_absensi_harian.siswa_id = siswa.id
    WHERE detail_absensi_harian.absensi_id = ?
    ORDER BY siswa.nama
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data_siswa = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Absensi - Sekretaris</title>

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

        .info-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .table-container {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
        }

        .table {
            min-width: 650px;
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

            .table-container {
                padding: 20px;
                border-radius: 10px;
            }

            .table {
                min-width: auto;
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

            .info-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Sistem Absensi TKJ</span>
        <span class="admin-name"><?= htmlspecialchars($_SESSION['nama']); ?></span>

        <a href="../../auth/logout.php" class="logout-link">Logout</a>

        <button class="menu-toggle" type="button" onclick="$('#mobileMenu').toggleClass('active');">
            ☰
        </button>
    </div>
</nav>

<div id="mobileMenu">
    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="index.php">📝 Absensi</a>
    <a href="index.php">← Kembali ke Absensi</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <strong>MENU SEKRETARIS</strong>
            </div>

            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="index.php">📝 Absensi</a>
        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3>Detail Absensi</h3>
                    <p class="text-muted mb-0">
                        Detail absensi siswa
                    </p>
                </div>

                <a href="index.php" class="btn btn-secondary">
                    ← Kembali
                </a>
            </div>

            <div class="info-container shadow-sm">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Tanggal</strong>
                        <br>
                        <?= date('d-m-Y', strtotime($absensi['tanggal'])); ?>
                    </div>

                    <div class="col-md-3">
                        <strong>Kelas</strong>
                        <br>
                        <?= htmlspecialchars($absensi['nama_kelas']); ?>
                    </div>

                    <div class="col-md-3">
                        <strong>Dibuat Oleh</strong>
                        <br>
                        <?= htmlspecialchars($absensi['dibuat_oleh']); ?>
                    </div>

                    <div class="col-md-3">
                        <strong>Waktu Absen</strong>
                        <br>
                        <?= date('d-m-Y H:i', strtotime($absensi['waktu_absen'])); ?>
                    </div>
                </div>
            </div>

            <div class="table-container shadow-sm">

                <table id="dataTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($data_siswa)) {
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nis']); ?></td>
                                <td><?= htmlspecialchars($data['nama']); ?></td>
                                <td><?= htmlspecialchars($data['jenis_kelamin']); ?></td>
                                <td><?= htmlspecialchars($data['status']); ?></td>
                                <td>
                                    <?= !empty($data['keterangan']) ? htmlspecialchars($data['keterangan']) : '-'; ?>
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
$(document).ready(function(){
    $('#dataTable').DataTable();
});
</script>

</body>
</html>