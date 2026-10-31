<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

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
    WHERE absensi_harian.id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$absensi = mysqli_fetch_assoc($result);

if (!$absensi) {
    header("Location: index.php");
    exit;
}

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
$detail = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Absensi - Admin</title>

    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">

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

        .info-box {
            margin-bottom: 20px;
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

            .sidebar.show {
                display: block;
            }

            .content {
                padding: 15px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">Sistem Absensi TKJ</a>
        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>

        <a href="../../auth/logout.php" class="logout-link">Logout</a>

        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 sidebar" id="sidebar">
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
            <a href="../../auth/logout.php" style="color:#dc3545;">Logout</a>
        </div>

        <div class="col-md-9 content">

            <div class="card">
                <div class="card-header">
                    <h4>Detail Absensi Harian</h4>
                </div>

                <div class="card-body">

                    <div class="row info-box">
                        <div class="col-md-3">
                            <strong>Tanggal</strong>
                            <br>
                            <?php echo date('d-m-Y', strtotime($absensi['tanggal'])); ?>
                        </div>

                        <div class="col-md-3">
                            <strong>Kelas</strong>
                            <br>
                            <?php echo htmlspecialchars($absensi['nama_kelas']); ?>
                        </div>

                        <div class="col-md-3">
                            <strong>Dibuat Oleh</strong>
                            <br>
                            <?php echo htmlspecialchars($absensi['dibuat_oleh']); ?>
                        </div>

                        <div class="col-md-3">
                            <strong>Waktu Absen</strong>
                            <br>
                            <?php echo date('d-m-Y H:i', strtotime($absensi['waktu_absen'])); ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>JK</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                if (mysqli_num_rows($detail) > 0) {
                                    while ($row = mysqli_fetch_assoc($detail)) {
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td>
                                            <?php echo $row['keterangan'] != "" ? htmlspecialchars($row['keterangan']) : "-"; ?>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Belum ada detail absensi.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="index.php" class="btn btn-secondary">← Kembali</a>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('sidebar').classList.toggle('show');
}
</script>

</body>
</html>