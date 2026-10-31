<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$query = mysqli_query($koneksi, "
    SELECT guru.*, users.username
    FROM guru
    INNER JOIN users ON guru.user_id = users.id
    ORDER BY guru.nama
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - Sistem Absensi TKJ</title>

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

        .mobile-menu {
            display: none;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 767px) {
            .navbar-brand {
                font-size: 17px;
                margin-right: 10px;
            }

            .admin-name {
                font-size: 14px;
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
                color: #dc3545;
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
            }

            .table {
                min-width: 650px;
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

<div class="mobile-menu" id="mobileMenu">

    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
    <a href="index.php">👨‍🏫 Data Guru</a>
    <a href="../kelas/index.php">🏫 Data Kelas</a>
    <a href="../mapel/index.php">📚 Mata Pelajaran</a>
    <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
    <a href="../mengajar/index.php">📝 Data Mengajar</a>
    <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
    <a href="../sekretaris/index.php">📋 Sekretaris</a>
    <a href="../absensi/index.php">📝 Absensi</a>
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
            <a href="index.php">👨‍🏫 Data Guru</a>
            <a href="../kelas/index.php">🏫 Data Kelas</a>
            <a href="../mapel/index.php">📚 Mata Pelajaran</a>
            <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
            <a href="../mengajar/index.php">📝 Data Mengajar</a>
            <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
            <a href="../sekretaris/index.php">📋 Sekretaris</a>
            <a href="../absensi/index.php">📝 Absensi</a>
            <a href="../rekap/index.php">📊 Rekap Absensi</a>

        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h3>Data Guru</h3>
                    <p class="text-muted mb-0">Mengelola data guru</p>
                </div>

                <a href="tambah.php" class="btn btn-primary">
                    + Tambah Guru
                </a>

            </div>

            <div class="table-container shadow-sm">

                <div class="table-responsive">

                    <table id="dataTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Guru</th>
                                <th>NIP</th>
                                <th>Username</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($query)):
                            ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nama']); ?>
                                </td>

                                <td>
                                    <?= $row['nip'] !== null && $row['nip'] !== '' ? htmlspecialchars($row['nip']) : '-'; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['username']); ?>
                                </td>

                                <td>
                                    <a
                                        href="edit.php?id=<?= $row['id']; ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="hapus.php?id=<?= $row['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data guru ini? Akun login guru juga akan dihapus.')"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>

                            <?php endwhile; ?>

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