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
        mengajar.id,
        guru.nama AS nama_guru,
        kelas.nama_kelas,
        mata_pelajaran.nama_mapel,
        tahun_ajaran.tahun,
        tahun_ajaran.semester
    FROM mengajar
    INNER JOIN guru ON mengajar.guru_id = guru.id
    INNER JOIN kelas ON mengajar.kelas_id = kelas.id
    INNER JOIN mata_pelajaran ON mengajar.mapel_id = mata_pelajaran.id
    INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
    ORDER BY tahun_ajaran.id DESC, kelas.nama_kelas, mata_pelajaran.nama_mapel, guru.nama
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mengajar - Sistem Absensi TKJ</title>

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
            padding: 30px;
        }

        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
        }

        #mobileMenu {
            display: none;
        }

        @media (max-width: 767px) {
            .logout-link {
                display: none;
            }

            .menu-toggle {
                display: inline-block;
                margin-left: auto;
            }

            #mobileMenu {
                display: none;
                background: #ffffff;
                border-bottom: 1px solid #ddd;
            }

            #mobileMenu.active {
                display: block;
            }

            #mobileMenu a {
                display: block;
                padding: 12px 20px;
                color: #333;
                text-decoration: none;
                border-bottom: 1px solid #eee;
            }

            #mobileMenu a:hover {
                background: #f1f1f1;
            }

            #mobileMenu .mobile-logout {
                color: #dc3545;
            }

            .sidebar {
                display: none;
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

            .content .btn-primary {
                white-space: nowrap;
            }

            .table-container {
                padding: 12px;
                border-radius: 8px;
                text-align: left;
                overflow-x: auto;
            }

            .table {
                min-width: 800px;
            }
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
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Sistem Absensi TKJ</span>
        <span class="admin-name"><?= htmlspecialchars($_SESSION['nama']); ?></span>
        <a href="../../auth/logout.php" class="logout-link">Logout</a>
        <button type="button" class="menu-toggle" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<div id="mobileMenu">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
    <a href="../guru/index.php">👨‍🏫 Data Guru</a>
    <a href="../kelas/index.php">🏫 Data Kelas</a>
    <a href="../mapel/index.php">📚 Mata Pelajaran</a>
    <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
    <a href="index.php">📝 Data Mengajar</a>
    <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
    <a href="../sekretaris/index.php">📋 Sekretaris</a>
    <a href="../absensi/index.php">📝 Absensi</a>
    <a href="../rekap/index.php">📊 Rekap Absensi</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
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
            <a href="index.php">📝 Data Mengajar</a>
            <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
            <a href="../sekretaris/index.php">📋 Sekretaris</a>
            <a href="../absensi/index.php">📝 Absensi</a>
            <a href="../rekap/index.php">📊 Rekap Absensi</a>
        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3>Data Mengajar</h3>
                    <p class="text-muted mb-0">Mengelola data guru, kelas, mata pelajaran, dan tahun ajaran</p>
                </div>

                <a href="tambah.php" class="btn btn-primary">+ Tambah Data Mengajar</a>
            </div>

            <div class="table-container shadow-sm">

                <table id="dataTable" class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Tahun Ajaran</th>
                            <th>Semester</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($query)) {
                        ?>

                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nama_guru']); ?></td>
                                <td><?= htmlspecialchars($data['nama_kelas']); ?></td>
                                <td><?= htmlspecialchars($data['nama_mapel']); ?></td>
                                <td><?= htmlspecialchars($data['tahun']); ?></td>
                                <td><?= htmlspecialchars($data['semester']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $data['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="hapus.php?id=<?= $data['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data mengajar ini?')">Hapus</a>
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

function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>
</html>