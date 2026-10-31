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

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$stmt = mysqli_prepare($koneksi, "
    SELECT sekretaris.id, sekretaris.kelas_id, kelas.nama_kelas
    FROM sekretaris
    INNER JOIN kelas ON sekretaris.kelas_id = kelas.id
    WHERE sekretaris.user_id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Data sekretaris belum terdaftar.");
}

$sekretaris = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$kelas_id = $sekretaris['kelas_id'];
$nama_kelas = $sekretaris['nama_kelas'];

$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');

$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM absensi_harian
    WHERE kelas_id = ? AND tanggal = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "is", $kelas_id, $tanggal);
mysqli_stmt_execute($stmt);
$result_absen = mysqli_stmt_get_result($stmt);

$absensi_sudah_ada = mysqli_num_rows($result_absen) > 0;
$absensi_id = null;

if ($absensi_sudah_ada) {
    $data_absen = mysqli_fetch_assoc($result_absen);
    $absensi_id = $data_absen['id'];
}

mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    if ($absensi_sudah_ada) {
        $error = "Absensi kelas " . $nama_kelas . " pada tanggal " . date('d-m-Y', strtotime($tanggal)) . " sudah dilakukan.";
    } else {

        $siswa = $_POST['status'] ?? [];

        if (empty($siswa)) {
            $error = "Data siswa tidak ditemukan.";
        } else {

            mysqli_begin_transaction($koneksi);

            try {
                $stmt = mysqli_prepare($koneksi, "
                    INSERT INTO absensi_harian
                    (kelas_id, tanggal, dibuat_oleh)
                    VALUES (?, ?, ?)
                ");

                mysqli_stmt_bind_param($stmt, "isi", $kelas_id, $tanggal, $user_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menyimpan absensi harian.");
                }

                $absensi_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt);

                $stmt_detail = mysqli_prepare($koneksi, "
                    INSERT INTO detail_absensi_harian
                    (absensi_id, siswa_id, status)
                    VALUES (?, ?, ?)
                ");

                foreach ($siswa as $siswa_id => $status) {

                    if (!in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alpa'])) {
                        throw new Exception("Status absensi tidak valid.");
                    }

                    $siswa_id = (int) $siswa_id;

                    mysqli_stmt_bind_param(
                        $stmt_detail,
                        "iis",
                        $absensi_id,
                        $siswa_id,
                        $status
                    );

                    if (!mysqli_stmt_execute($stmt_detail)) {
                        throw new Exception("Gagal menyimpan detail absensi.");
                    }
                }

                mysqli_stmt_close($stmt_detail);

                mysqli_commit($koneksi);

                header("Location: absen.php?success=1");
                exit;

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Absensi berhasil disimpan.";
}

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nis, nama
    FROM siswa
    WHERE kelas_id = ? AND status = 'aktif'
    ORDER BY nama
");

mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
$siswa_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Harian - Sekretaris</title>

    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/bootstrap.min.css">

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

        .form-container {
            width: 100%;
        }

        .card {
            text-align: left;
        }

        .table-container {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
            overflow-x: auto;
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

            .form-container {
                max-width: 900px;
                margin: 0 auto;
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

            <div class="form-container">

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Absensi Harian</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <?php if ($success !== "") { ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success); ?>
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>Kelas</label>
                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($nama_kelas); ?>"
                                       readonly>
                            </div>

                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date"
                                       name="tanggal"
                                       class="form-control"
                                       value="<?= htmlspecialchars($tanggal); ?>"
                                       required
                                       <?= $absensi_sudah_ada ? 'readonly' : ''; ?>>
                            </div>

                            <?php if ($absensi_sudah_ada) { ?>

                                <div class="alert alert-warning">
                                    Absensi untuk kelas
                                    <strong><?= htmlspecialchars($nama_kelas); ?></strong>
                                    pada tanggal
                                    <strong><?= date('d-m-Y', strtotime($tanggal)); ?></strong>
                                    sudah dilakukan.
                                </div>

                                <a href="absen.php" class="btn btn-secondary">
                                    Pilih Tanggal Lain
                                </a>

                            <?php } else { ?>

                                <button type="submit"
                                        name="tampilkan"
                                        class="btn btn-primary">
                                    Tampilkan Siswa
                                </button>

                            <?php } ?>

                        </form>

                    </div>
                </div>

                <?php if (!$absensi_sudah_ada && isset($_POST['tampilkan'])) { ?>

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4 class="mb-0">Daftar Siswa</h4>
                        </div>

                        <div class="card-body">

                            <form method="POST">

                                <input type="hidden"
                                       name="tanggal"
                                       value="<?= htmlspecialchars($tanggal); ?>">

                                <div class="table-container">

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama Siswa</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php
                                            $no = 1;

                                            if (mysqli_num_rows($siswa_result) > 0) {

                                                while ($siswa = mysqli_fetch_assoc($siswa_result)) {
                                            ?>

                                                    <tr>
                                                        <td><?= $no++; ?></td>

                                                        <td>
                                                            <?= htmlspecialchars($siswa['nis']); ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($siswa['nama']); ?>
                                                        </td>

                                                        <td>
                                                            <select name="status[<?= $siswa['id']; ?>]"
                                                                    class="form-control"
                                                                    required>
                                                                <option value="Hadir">Hadir</option>
                                                                <option value="Sakit">Sakit</option>
                                                                <option value="Izin">Izin</option>
                                                                <option value="Alpa">Alpa</option>
                                                            </select>
                                                        </td>
                                                    </tr>

                                            <?php
                                                }

                                            } else {
                                            ?>

                                                <tr>
                                                    <td colspan="4" class="text-center">
                                                        Tidak ada siswa aktif di kelas ini.
                                                    </td>
                                                </tr>

                                            <?php
                                            }
                                            ?>

                                        </tbody>
                                    </table>

                                </div>

                                <?php if (mysqli_num_rows($siswa_result) > 0) { ?>

                                    <div class="mt-3">

                                        <button type="submit"
                                                name="simpan"
                                                class="btn btn-primary"
                                                onclick="return confirm('Yakin ingin menyimpan absensi harian kelas ini?');">
                                            Simpan Absensi
                                        </button>

                                        <a href="absen.php" class="btn btn-secondary">
                                            Batal
                                        </a>

                                    </div>

                                <?php } ?>

                            </form>

                        </div>
                    </div>

                <?php } ?>

            </div>

        </div>
    </div>
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>