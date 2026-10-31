<?php
session_start();
require_once "../config/koneksi.php";

$error = "";

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username == "" || $password == "") {
        $error = "Username dan password wajib diisi!";
    } else {

        // Cari akun berdasarkan username
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM users WHERE username = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                // Ambil semua role yang dimiliki user
                $stmt_role = mysqli_prepare(
                    $koneksi,
                    "SELECT role FROM user_roles WHERE user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt_role,
                    "i",
                    $user['id']
                );

                mysqli_stmt_execute($stmt_role);

                $result_role = mysqli_stmt_get_result($stmt_role);

                $roles = [];

                while ($row_role = mysqli_fetch_assoc($result_role)) {
                    $roles[] = $row_role['role'];
                }

                mysqli_stmt_close($stmt_role);

                // Simpan data login ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['roles'] = $roles;

                header("Location: ../index.php");
                exit;

            } else {
                $error = "Username atau password salah!";
            }

        } else {
            $error = "Username atau password salah!";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Sistem Absensi TKJ</title>

    <link rel="stylesheet"
          type="text/css"
          href="../bootstrap/css/bootstrap.min.css">

    <style>

        body {
            background: #f5f5f5;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 400px;
            border-radius: 15px;
            border: none;
        }

        .login-title {
            font-weight: bold;
        }

        .login-subtitle {
            color: #777;
            font-size: 14px;
        }

    </style>

</head>

<body>

<div class="container login-container">

    <div class="card shadow login-card">

        <div class="card-body p-4">

            <div class="text-center mb-4">

                <h3 class="login-title">
                    Sistem Absensi TKJ
                </h3>

                <p class="login-subtitle mb-0">
                    SMKS Tarbiyah Islamiyah Hinai Kiri
                </p>

            </div>

            <?php if ($error != "") { ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php } ?>

            <form method="POST">

                <div class="form-group">

                    <label>Username</label>

                    <input type="text"
                           name="username"
                           class="form-control"
                           placeholder="Masukkan username"
                           autocomplete="username"
                           required>

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Masukkan password"
                           autocomplete="current-password"
                           required>

                </div>

                <button type="submit"
                        class="btn btn-primary btn-block">

                    Login

                </button>

            </form>

        </div>

    </div>

</div>

<script src="../bootstrap/js/jquery.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>

</body>

</html>