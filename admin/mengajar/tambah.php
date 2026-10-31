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

$guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama_kelas, tingkat FROM kelas ORDER BY tingkat, nama_kelas");
$mapel = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel");
$tahun_ajaran = mysqli_query($koneksi, "SELECT id, tahun, semester FROM tahun_ajaran ORDER BY id DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $tahun_ajaran_id = $_POST['tahun_ajaran_id'];

    if ($guru_id === "" || $kelas_id === "" || $mapel_id === "" || $tahun_ajaran_id === "") {
        $error = "Semua data wajib diisi.";
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM mengajar WHERE guru_id = ? AND kelas_id = ? AND mapel_id = ? AND tahun_ajaran_id = ?");
        mysqli_stmt_bind_param($cek, "iiii", $guru_id, $kelas_id, $mapel_id, $tahun_ajaran_id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Data mengajar tersebut sudah ada.";
            mysqli_stmt_close($cek);
        } else {
            mysqli_stmt_close($cek);

            $stmt = mysqli_prepare($koneksi, "INSERT INTO mengajar (guru_id, kelas_id, mapel_id, tahun_ajaran_id) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiii", $guru_id, $kelas_id, $mapel_id, $tahun_ajaran_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal menambahkan data mengajar.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mengajar - Sistem Absensi TKJ</title>

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
    <a href="index.php">← Kembali ke Data Mengajar</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <a href="index.php">← Kembali ke Data Mengajar</a>
            </div>
        </div>

        <div class="col-md-10 content">
            <div class="form-container">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Tambah Data Mengajar</h4>
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
                                    <?php while ($data = mysqli_fetch_assoc($guru)) { ?>
                                        <option value="<?= $data['id']; ?>" <?= isset($_POST['guru_id']) && $_POST['guru_id'] == $data['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($data['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-control" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while ($data = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= $data['id']; ?>" <?= isset($_POST['kelas_id']) && $_POST['kelas_id'] == $data['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($data['nama_kelas']); ?> - <?= htmlspecialchars($data['tingkat']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mata Pelajaran</label>
                                <select name="mapel_id" class="form-control" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php while ($data = mysqli_fetch_assoc($mapel)) { ?>
                                        <option value="<?= $data['id']; ?>" <?= isset($_POST['mapel_id']) && $_POST['mapel_id'] == $data['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($data['nama_mapel']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-control" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php while ($data = mysqli_fetch_assoc($tahun_ajaran)) { ?>
                                        <option value="<?= $data['id']; ?>" <?= isset($_POST['tahun_ajaran_id']) && $_POST['tahun_ajaran_id'] == $data['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($data['tahun']); ?> - <?= htmlspecialchars($data['semester']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin menyimpan data mengajar ini?')">Simpan</button>
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