<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$query = mysqli_query($koneksi, "
    SELECT *
    FROM tahun_ajaran
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tahun Ajaran - Sistem Absensi TKJ</title>

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

        .content .btn-primary {
            white-space: nowrap;
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

            .table-responsive {
                overflow-x: auto;
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
    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
    <a href="../guru/index.php">👨‍🏫 Data Guru</a>
    <a href="../kelas/index.php">🏫 Data Kelas</a>
    <a href="../mapel/index.php">📚 Mata Pelajaran</a>
    <a href="index.php">📅 Tahun Ajaran</a>
    <a href="../mengajar/index.php">📝 Data Mengajar</a>
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
            <a href="index.php">📅 Tahun Ajaran</a>
            <a href="../mengajar/index.php">📝 Data Mengajar</a>
            <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
            <a href="../sekretaris/index.php">📋 Sekretaris</a>
            <a href="../absensi/index.php">📝 Absensi</a>
            <a href="../rekap/index.php">📊 Rekap Absensi</a>
        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3>Tahun Ajaran</h3>
                    <p class="text-muted mb-0">Mengelola data tahun ajaran</p>
                </div>

                <a href="tambah.php" class="btn btn-primary">+ Tambah Tahun Ajaran</a>
            </div>

            <div class="table-container shadow-sm">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun</th>
                                <th>Semester</th>
                                <th>Status</th>
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
                                    <td><?= htmlspecialchars($data['tahun']); ?></td>
                                    <td><?= htmlspecialchars($data['semester']); ?></td>
                                    <td><?= htmlspecialchars($data['status']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="hapus.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">Hapus</a>
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
$(document).ready(function() {
    $('#dataTable').DataTable({
        scrollX: true
    });
});

function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>
</html>