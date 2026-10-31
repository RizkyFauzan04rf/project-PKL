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

$error = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM wali_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php");
    exit;
}

$guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama_kelas, tingkat FROM kelas ORDER BY tingkat, nama_kelas");
$tahun_ajaran = mysqli_query($koneksi, "SELECT id, tahun, semester FROM tahun_ajaran ORDER BY id DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    if ($guru_id === "" || $kelas_id === "" || $tahun_ajaran_id === "") {
        $error = "Semua data wajib diisi.";
    } else {
        $cek_guru = mysqli_prepare($koneksi, "SELECT id FROM wali_kelas WHERE guru_id = ? AND tahun_ajaran_id = ? AND id != ?");
        mysqli_stmt_bind_param($cek_guru, "iii", $guru_id, $tahun_ajaran_id, $id);
        mysqli_stmt_execute($cek_guru);
        mysqli_stmt_store_result($cek_guru);

        $cek_kelas = mysqli_prepare($koneksi, "SELECT id FROM wali_kelas WHERE kelas_id = ? AND tahun_ajaran_id = ? AND id != ?");
        mysqli_stmt_bind_param($cek_kelas, "iii", $kelas_id, $tahun_ajaran_id, $id);
        mysqli_stmt_execute($cek_kelas);
        mysqli_stmt_store_result($cek_kelas);

        if (mysqli_stmt_num_rows($cek_guru) > 0) {
            $error = "Guru tersebut sudah menjadi wali kelas pada tahun ajaran dan semester tersebut.";
        } elseif (mysqli_stmt_num_rows($cek_kelas) > 0) {
            $error = "Kelas tersebut sudah memiliki wali kelas pada tahun ajaran dan semester tersebut.";
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE wali_kelas SET guru_id = ?, kelas_id = ?, tahun_ajaran_id = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "iiii", $guru_id, $kelas_id, $tahun_ajaran_id, $id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($cek_guru);
                mysqli_stmt_close($cek_kelas);
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal mengubah data wali kelas.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek_guru);
        mysqli_stmt_close($cek_kelas);
    }

    $data['guru_id'] = $guru_id;
    $data['kelas_id'] = $kelas_id;
    $data['tahun_ajaran_id'] = $tahun_ajaran_id;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wali Kelas - Sistem Absensi TKJ</title>

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
            padding: 30px;
        }

        #mobileMenu {
            display: none;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
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

            .form-container {
                width: 100%;
            }

            .card {
                text-align: left;
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
    <a href="index.php">← Kembali ke Wali Kelas</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <a href="index.php">← Kembali ke Wali Kelas</a>
            </div>
        </div>

        <div class="col-md-10 content">
            <div class="form-container">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Wali Kelas</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>Guru</label>
                                <select name="guru_id" class="form-control" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php while ($item = mysqli_fetch_assoc($guru)) { ?>
                                        <option value="<?= $item['id']; ?>" <?= $data['guru_id'] == $item['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-control" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while ($item = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= $item['id']; ?>" <?= $data['kelas_id'] == $item['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item['nama_kelas']); ?> - <?= htmlspecialchars($item['tingkat']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-control" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php while ($item = mysqli_fetch_assoc($tahun_ajaran)) { ?>
                                        <option value="<?= $item['id']; ?>" <?= $data['tahun_ajaran_id'] == $item['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item['tahun']); ?> - <?= htmlspecialchars($item['semester']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin menyimpan perubahan wali kelas ini?')">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Kembali</a>

                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>

<script>
function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>
</html>