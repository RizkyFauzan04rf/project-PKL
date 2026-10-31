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

$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $kelas_id = $_POST['kelas_id'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $status = $_POST['status'];

    if ($nis === "" || $nama === "" || $kelas_id === "" || $jenis_kelamin === "" || $status === "") {
        $error = "Semua data wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM siswa WHERE nis = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $nis, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "NIS sudah digunakan oleh siswa lain.";
        } else {
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($koneksi, "UPDATE siswa SET nis = ?, nama = ?, kelas_id = ?, jenis_kelamin = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssissi", $nis, $nama, $kelas_id, $jenis_kelamin, $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php");
                exit;
            } else {
                $error = "Data siswa gagal diperbarui.";
            }
        }

        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
    }

    $data['nis'] = $nis;
    $data['nama'] = $nama;
    $data['kelas_id'] = $kelas_id;
    $data['jenis_kelamin'] = $jenis_kelamin;
    $data['status'] = $status;
}

$kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY tingkat, nama_kelas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Sistem Absensi TKJ</title>

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
            margin-left: auto;
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

        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .mobile-menu {
            display: none;
        }

        @media (max-width: 767px) {
            .navbar-brand {
                margin-right: 10px;
                font-size: 17px;
            }

            .admin-name {
                font-size: 14px;
            }

            .logout-link {
                display: none;
            }

            .menu-toggle {
                display: inline-block;
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
                padding: 13px 20px;
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
            }

            .btn {
                width: 100%;
                margin-bottom: 8px;
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
    <a href="index.php">← Kembali ke Siswa</a>
    <a href="../../auth/logout.php" class="logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <a href="index.php">← Kembali ke Siswa</a>
            </div>
        </div>

        <div class="col-md-10 content">
            <div class="form-container">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Siswa</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>NIS</label>
                                <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($data['nis']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Nama Siswa</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-control" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                        <option value="<?= $row['id']; ?>" <?= ($data['kelas_id'] == $row['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($row['nama_kelas']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control" required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" <?= ($data['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?= ($data['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="aktif" <?= ($data['status'] === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($data['status'] === 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin menyimpan perubahan data siswa ini?')">
                                Simpan Perubahan
                            </button>

                            <a href="index.php" class="btn btn-secondary">
                                Kembali
                            </a>

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