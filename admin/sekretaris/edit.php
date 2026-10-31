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
    SELECT sekretaris.*, users.nama, users.username
    FROM sekretaris
    INNER JOIN users ON sekretaris.user_id = users.id
    WHERE sekretaris.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $kelas_id = $_POST['kelas_id'];

    if ($nama === "" || $username === "" || $kelas_id === "") {
        $error = "Nama, username, dan kelas wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "
            SELECT id
            FROM users
            WHERE username = ? AND id != ?
        ");

        mysqli_stmt_bind_param($stmt, "si", $username, $data['user_id']);
        mysqli_stmt_execute($stmt);
        $result_username = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result_username) > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $stmt = mysqli_prepare($koneksi, "
                SELECT id
                FROM sekretaris
                WHERE kelas_id = ? AND id != ?
            ");

            mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $id);
            mysqli_stmt_execute($stmt);
            $result_kelas = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result_kelas) > 0) {
                $error = "Kelas tersebut sudah memiliki sekretaris.";
            } else {
                mysqli_begin_transaction($koneksi);

                try {
                    if ($password !== "") {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        $stmt = mysqli_prepare($koneksi, "
                            UPDATE users
                            SET nama = ?, username = ?, password = ?
                            WHERE id = ?
                        ");

                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssi",
                            $nama,
                            $username,
                            $password_hash,
                            $data['user_id']
                        );
                    } else {
                        $stmt = mysqli_prepare($koneksi, "
                            UPDATE users
                            SET nama = ?, username = ?
                            WHERE id = ?
                        ");

                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssi",
                            $nama,
                            $username,
                            $data['user_id']
                        );
                    }

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal memperbarui akun.");
                    }

                    $stmt = mysqli_prepare($koneksi, "
                        UPDATE sekretaris
                        SET kelas_id = ?
                        WHERE id = ?
                    ");

                    mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $id);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal memperbarui data sekretaris.");
                    }

                    mysqli_commit($koneksi);

                    header("Location: index.php");
                    exit;
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $error = $e->getMessage();
                }
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    $data['nama'] = $nama;
    $data['username'] = $username;
    $data['kelas_id'] = $kelas_id;
}

$kelas = mysqli_query($koneksi, "
    SELECT *
    FROM kelas
    ORDER BY tingkat, nama_kelas
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sekretaris - Sistem Absensi TKJ</title>

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
                max-width: 700px;
                margin: 0 auto;
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
    <a href="index.php">← Kembali ke Sekretaris</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <strong>MENU ADMIN</strong>
            </div>

            <a href="index.php">← Kembali ke Sekretaris</a>
        </div>

        <div class="col-md-10 content">

            <div class="form-container">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Sekretaris</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>Nama Sekretaris</label>
                                <input type="text" name="nama" class="form-control" required
                                       value="<?= htmlspecialchars($data['nama']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?= htmlspecialchars($data['username']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control">
                                <small class="form-text text-muted">
                                    Kosongkan jika tidak ingin mengubah password.
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-control" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($kelas_data = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= $kelas_data['id']; ?>"
                                            <?= $data['kelas_id'] == $kelas_data['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($kelas_data['nama_kelas']); ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <button type="submit"
                                    class="btn btn-primary"
                                    onclick="return confirm('Yakin ingin menyimpan perubahan data sekretaris ini?');">
                                Simpan Perubahan
                            </button>

                            <a href="index.php" class="btn btn-secondary">
                                Batal
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

</body>
</html>