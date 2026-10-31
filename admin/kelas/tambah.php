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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kelas = trim($_POST['nama_kelas']);
    $tingkat = trim($_POST['tingkat']);

    if ($nama_kelas == "" || $tingkat == "") {
        $error = "Semua data wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kelas (nama_kelas, tingkat) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $nama_kelas, $tingkat);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php");
            exit;
        } else {
            $error = "Data kelas gagal ditambahkan.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas - Sistem Absensi TKJ</title>

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
            line-height: 50px;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            margin-left: auto;
            line-height: 50px;
        }

        .logout-link:hover {
            color: #ddd;
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
            margin-left: 12px;
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
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
        }

        @media (max-width: 767px) {
            .navbar {
                padding: 0 15px;
            }

            .navbar .container-fluid {
                padding-left: 0;
                padding-right: 0;
            }

            .navbar-brand {
                font-size: 15px;
                margin-right: 12px;
            }

            .admin-name {
                font-size: 11px;
                line-height: 50px;
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
            }

            .content {
                padding: 20px 12px;
            }

            .form-container {
                max-width: 100%;
                padding: 15px;
            }

            h3 {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding-left: 12px;
                padding-right: 12px;
            }

            .navbar-brand {
                font-size: 14px;
            }

            .admin-name {
                font-size: 10px;
            }

            .menu-toggle {
                font-size: 18px;
                padding: 5px 9px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">

        <span class="navbar-brand">Sistem Absensi TKJ</span>

        <span class="admin-name">
            <?= htmlspecialchars($_SESSION['nama']); ?>
        </span>

        <a href="../../auth/logout.php" class="logout-link">
            Logout
        </a>

        <button type="button" class="menu-toggle" onclick="toggleMenu()">
            ☰
        </button>

    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <a href="index.php">← Kembali ke Kelas</a>
    <a href="../../auth/logout.php" class="logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <a href="index.php">← Kembali ke Kelas</a>
        </div>

        <div class="col-md-10 content">

            <div class="mb-3">
                <h3>Tambah Data Kelas</h3>
                <p class="text-muted mb-0">Menambahkan data kelas</p>
            </div>

            <div class="form-container shadow-sm">

                <?php if ($error != "") { ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php } ?>

                <form method="POST">

                    <div class="form-group">
                        <label>Nama Kelas</label>
                        <input type="text"
                               name="nama_kelas"
                               class="form-control"
                               value="<?= isset($_POST['nama_kelas']) ? htmlspecialchars($_POST['nama_kelas']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Tingkat</label>
                        <input type="text"
                               name="tingkat"
                               class="form-control"
                               value="<?= isset($_POST['tingkat']) ? htmlspecialchars($_POST['tingkat']) : ''; ?>"
                               required>
                    </div>

                    <button type="submit"
                            class="btn btn-primary"
                            onclick="return confirm('Yakin ingin menyimpan data kelas ini?')">
                        Simpan
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        Kembali
                    </a>

                </form>

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