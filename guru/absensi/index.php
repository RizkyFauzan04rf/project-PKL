<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

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
    SELECT
        absensi_harian.id,
        absensi_harian.tanggal,
        absensi_harian.waktu_absen,
        kelas.nama_kelas,
        users.nama AS dibuat_oleh
    FROM absensi_harian
    INNER JOIN kelas ON absensi_harian.kelas_id = kelas.id
    INNER JOIN users ON absensi_harian.dibuat_oleh = users.id
    INNER JOIN mengajar ON mengajar.kelas_id = absensi_harian.kelas_id
    WHERE mengajar.guru_id = ?
    GROUP BY absensi_harian.id
    ORDER BY absensi_harian.tanggal DESC, absensi_harian.id DESC
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$data_harian = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT
        absensi_mapel.id,
        absensi_mapel.tanggal,
        absensi_mapel.jam_ke,
        absensi_mapel.waktu_absen,
        kelas.nama_kelas,
        mata_pelajaran.nama_mapel
    FROM absensi_mapel
    INNER JOIN mengajar ON absensi_mapel.mengajar_id = mengajar.id
    INNER JOIN kelas ON mengajar.kelas_id = kelas.id
    INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
    WHERE mengajar.guru_id = ?
    ORDER BY absensi_mapel.tanggal DESC, absensi_mapel.id DESC
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$data_mapel = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Guru - Sistem Absensi TKJ</title>

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

        .content .d-flex {
            flex-direction: column;
            align-items: center !important;
            gap: 10px;
        }

        .menu-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px 20px;
            text-align: center;
            border: 1px solid #e5e5e5;
            height: 100%;
        }

        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .menu-card h5 {
            margin-bottom: 10px;
        }

        .menu-card p {
            color: #6c757d;
            min-height: 45px;
        }

        .table-container {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
        }

        .table {
            min-width: 700px;
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

            .content .d-flex {
                flex-direction: row;
                align-items: center !important;
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

            .menu-card {
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

        <a href="../../auth/logout.php" class="logout-link">Logout</a>

        <button class="menu-toggle" type="button" onclick="$('#mobileMenu').toggleClass('active');">
            ☰
        </button>
    </div>
</nav>

<div id="mobileMenu">
    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="index.php">📝 Absensi</a>
    <a href="harian.php">📋 Absensi Harian</a>
    <a href="mapel.php">📚 Absensi Mata Pelajaran</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <strong>MENU GURU</strong>
            </div>

            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="index.php">📝 Absensi</a>
            <a href="harian.php">📋 Absensi Harian</a>
            <a href="mapel.php">📚 Absensi Mata Pelajaran</a>
        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3>Absensi Guru</h3>
                    <p class="text-muted mb-0">
                        Mengelola absensi siswa sesuai kelas dan mata pelajaran
                    </p>
                </div>
            </div>

            <div class="row mb-4">

                <div class="col-md-6 mb-4">
                    <div class="menu-card shadow-sm">
                        <div class="menu-icon">📋</div>
                        <h5>Absensi Harian</h5>
                        <p>
                            Melakukan absensi awal siswa berdasarkan kelas.
                        </p>
                        <a href="harian.php" class="btn btn-primary">
                            Buka Absensi Harian
                        </a>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="menu-card shadow-sm">
                        <div class="menu-icon">📚</div>
                        <h5>Absensi Mata Pelajaran</h5>
                        <p>
                            Melakukan absensi siswa berdasarkan mata pelajaran yang diampu.
                        </p>
                        <a href="mapel.php" class="btn btn-primary">
                            Buka Absensi Mapel
                        </a>
                    </div>
                </div>

            </div>

            <div class="table-container shadow-sm mb-4">

                <h5 class="mb-3">Riwayat Absensi Harian</h5>

                <div class="table-responsive">
                    <table id="dataTableHarian" class="table table-bordered table-striped">
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

                            while ($data = mysqli_fetch_assoc($data_harian)) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($data['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($data['dibuat_oleh']); ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($data['waktu_absen'])); ?></td>
                                    <td>
                                        <a href="detail.php?jenis=harian&id=<?= $data['id']; ?>"
                                           class="btn btn-info btn-sm">
                                            Detail
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

            <div class="table-container shadow-sm">

                <h5 class="mb-3">Riwayat Absensi Mata Pelajaran</h5>

                <div class="table-responsive">
                    <table id="dataTableMapel" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam Ke</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Waktu Absen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            while ($data = mysqli_fetch_assoc($data_mapel)) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($data['jam_ke']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_mapel']); ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($data['waktu_absen'])); ?></td>
                                    <td>
                                        <a href="detail.php?jenis=mapel&id=<?= $data['id']; ?>"
                                           class="btn btn-info btn-sm">
                                            Detail
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
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>
<script src="../../bootstrap/js/jquery.dataTables.js"></script>
<script src="../../bootstrap/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function(){
    $('#dataTableHarian').DataTable();
    $('#dataTableMapel').DataTable();
});
</script>

</body>
</html>