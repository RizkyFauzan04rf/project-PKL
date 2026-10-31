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

$stmt = mysqli_prepare($koneksi, "
    SELECT guru.*, users.username
    FROM guru
    INNER JOIN users ON guru.user_id = users.id
    WHERE guru.id = ?
");
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
    $nama = trim($_POST['nama']);
    $nip = trim($_POST['nip']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_id = $data['user_id'];

    if ($nama === "" || $username === "") {
        $error = "Nama Guru dan Username wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "
            SELECT id 
            FROM users 
            WHERE username = ? AND id != ?
        ");
        mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = "Username sudah digunakan.";
        }

        mysqli_stmt_close($stmt);

        if ($error === "" && $nip !== "") {
            $stmt = mysqli_prepare($koneksi, "
                SELECT id 
                FROM guru 
                WHERE nip = ? AND id != ?
            ");
            mysqli_stmt_bind_param($stmt, "si", $nip, $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $error = "NIP sudah digunakan.";
            }

            mysqli_stmt_close($stmt);
        }

        if ($error === "") {
            mysqli_begin_transaction($koneksi);

            try {
                if ($password !== "") {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = mysqli_prepare($koneksi, "
                        UPDATE users 
                        SET nama = ?, username = ?, password = ?
                        WHERE id = ?
                    ");
                    mysqli_stmt_bind_param($stmt, "sssi", $nama, $username, $password_hash, $user_id);
                } else {
                    $stmt = mysqli_prepare($koneksi, "
                        UPDATE users 
                        SET nama = ?, username = ?
                        WHERE id = ?
                    ");
                    mysqli_stmt_bind_param($stmt, "ssi", $nama, $username, $user_id);
                }

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal mengubah akun guru.");
                }

                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($koneksi, "
                    UPDATE guru 
                    SET nip = NULLIF(?, ''), nama = ?
                    WHERE id = ?
                ");
                mysqli_stmt_bind_param($stmt, "ssi", $nip, $nama, $id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal mengubah data guru.");
                }

                mysqli_stmt_close($stmt);

                mysqli_commit($koneksi);

                header("Location: index.php");
                exit;
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }

    $data['nama'] = $nama;
    $data['nip'] = $nip;
    $data['username'] = $username;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guru - Sistem Absensi TKJ</title>

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
                        <h4 class="mb-0">Edit Guru</h4>
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
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($data['nip'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password" class="form-control">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                            </div>

                            <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin menyimpan perubahan data guru ini?')">Simpan</button>
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