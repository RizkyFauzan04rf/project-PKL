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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $nip = trim($_POST['nip']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($nama === "" || $username === "" || $password === "") {
        $error = "Nama Guru, Username, dan Password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = "Username sudah digunakan.";
        } else {
            if ($nip !== "") {
                $stmt = mysqli_prepare($koneksi, "SELECT id FROM guru WHERE nip = ?");
                mysqli_stmt_bind_param($stmt, "s", $nip);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $error = "NIP sudah digunakan.";
                }
            }

            if ($error === "") {
                mysqli_begin_transaction($koneksi);

                try {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, 'guru')");
                    mysqli_stmt_bind_param($stmt, "sss", $nama, $username, $password_hash);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal menambahkan akun.");
                    }

                    $user_id = mysqli_insert_id($koneksi);

                    $stmt = mysqli_prepare($koneksi, "INSERT INTO guru (user_id, nip, nama) VALUES (?, NULLIF(?, ''), ?)");
                    mysqli_stmt_bind_param($stmt, "iss", $user_id, $nip, $nama);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal menambahkan data guru.");
                    }

                    $stmt = mysqli_prepare($koneksi, "INSERT INTO user_roles (user_id, role) VALUES (?, 'guru')");
                    mysqli_stmt_bind_param($stmt, "i", $user_id);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal menambahkan role guru.");
                    }

                    mysqli_commit($koneksi);

                    header("Location: index.php");
                    exit;
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $error = $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - Sistem Absensi TKJ</title>

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
    <a href="index.php">← Kembali ke Guru</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <a href="index.php">← Kembali ke Guru</a>
            </div>
        </div>

        <div class="col-md-10 content">
            <div class="form-container">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Tambah Guru</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>Nama Guru</label>
                                <input type="text" name="nama" class="form-control" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control" value="<?= isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin menyimpan data guru ini?')">Simpan</button>
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