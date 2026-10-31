<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM guru
    WHERE user_id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_guru = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_guru) == 0) {
    die("Data guru belum terdaftar.");
}

$data_guru = mysqli_fetch_assoc($result_guru);
$guru_id = $data_guru['id'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "
    SELECT DISTINCT
        kelas.id,
        kelas.nama_kelas,
        kelas.tingkat
    FROM mengajar
    INNER JOIN kelas ON mengajar.kelas_id = kelas.id
    INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
    WHERE mengajar.guru_id = ?
    AND tahun_ajaran.status = 'Aktif'
    ORDER BY kelas.tingkat, kelas.nama_kelas
");

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$data_kelas = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$kelas_id = isset($_POST['kelas_id']) ? (int) $_POST['kelas_id'] : 0;
$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');

$error = "";
$success = "";
$absensi_sudah_ada = false;
$absensi_id = 0;
$data_siswa = null;

if ($kelas_id > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS jumlah
        FROM mengajar
        INNER JOIN tahun_ajaran ON mengajar.tahun_ajaran_id = tahun_ajaran.id
        WHERE mengajar.guru_id = ?
        AND mengajar.kelas_id = ?
        AND tahun_ajaran.status = 'Aktif'
    ");

    mysqli_stmt_bind_param($stmt, "ii", $guru_id, $kelas_id);
    mysqli_stmt_execute($stmt);
    $result_validasi_kelas = mysqli_stmt_get_result($stmt);
    $validasi_kelas = mysqli_fetch_assoc($result_validasi_kelas);
    mysqli_stmt_close($stmt);

    if ($validasi_kelas['jumlah'] == 0) {
        $error = "Kelas yang dipilih tidak termasuk kelas yang Anda ajar.";
        $kelas_id = 0;
    }
}

if ($kelas_id > 0 && $tanggal != '') {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id
        FROM absensi_harian
        WHERE kelas_id = ?
        AND tanggal = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt, "is", $kelas_id, $tanggal);
    mysqli_stmt_execute($stmt);
    $result_cek = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_cek) > 0) {
        $data_cek = mysqli_fetch_assoc($result_cek);
        $absensi_sudah_ada = true;
        $absensi_id = $data_cek['id'];
    }

    mysqli_stmt_close($stmt);
}

if (isset($_POST['simpan'])) {
    if ($kelas_id == 0) {
        $error = "Silakan pilih kelas.";
    } elseif ($tanggal == '') {
        $error = "Silakan pilih tanggal.";
    } elseif ($absensi_sudah_ada) {
        $error = "Absensi harian untuk kelas dan tanggal tersebut sudah dibuat.";
    } elseif (!isset($_POST['status']) || !is_array($_POST['status'])) {
        $error = "Data absensi siswa belum diisi.";
    } else {
        $status_siswa = $_POST['status'];

        $valid_status = ['Hadir', 'Sakit', 'Izin', 'Alpa'];
        $valid_data = true;

        foreach ($status_siswa as $siswa_id => $status) {
            if (!in_array($status, $valid_status)) {
                $valid_data = false;
                break;
            }
        }

        if (!$valid_data) {
            $error = "Status absensi tidak valid.";
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                $stmt = mysqli_prepare($koneksi, "
                    INSERT INTO absensi_harian
                    (kelas_id, tanggal, dibuat_oleh)
                    VALUES (?, ?, ?)
                ");

                mysqli_stmt_bind_param(
                    $stmt,
                    "isi",
                    $kelas_id,
                    $tanggal,
                    $user_id
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menyimpan absensi.");
                }

                $absensi_id_baru = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt);

                $stmt_detail = mysqli_prepare($koneksi, "
                    INSERT INTO detail_absensi_harian
                    (absensi_id, siswa_id, status)
                    VALUES (?, ?, ?)
                ");

                foreach ($status_siswa as $siswa_id => $status) {
                    $siswa_id = (int) $siswa_id;

                    mysqli_stmt_bind_param(
                        $stmt_detail,
                        "iis",
                        $absensi_id_baru,
                        $siswa_id,
                        $status
                    );

                    if (!mysqli_stmt_execute($stmt_detail)) {
                        throw new Exception("Gagal menyimpan detail absensi.");
                    }
                }

                mysqli_stmt_close($stmt_detail);

                mysqli_commit($koneksi);

                header("Location: harian.php?success=1");
                exit;
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Absensi harian berhasil disimpan.";
}

if ($kelas_id > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, nis, nama, jenis_kelamin
        FROM siswa
        WHERE kelas_id = ?
        AND status = 'aktif'
        ORDER BY nama
    ");

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $data_siswa = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Harian - Guru</title>

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

        .content h3 {
            font-size: 22px;
        }

        .form-container {
            width: 100%;
            margin: 0 auto;
        }

        .card {
            text-align: left;
            border: none;
            border-radius: 10px;
        }

        .student-table {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
        }

        .student-table .table {
            min-width: 650px;
        }

        .status-select {
            min-width: 120px;
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

        #mobileMenu .mobile-back {
            border-bottom: 1px solid #ddd;
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

            .content h3 {
                font-size: 24px;
            }

            .form-container {
                max-width: 700px;
            }

            .student-table {
                padding: 20px;
                border-radius: 10px;
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

            .content {
                padding: 20px 15px;
                text-align: center;
            }

            .form-container {
                width: 100%;
            }

            .student-table {
                text-align: left;
            }

            .btn {
                width: 100%;
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
    <a href="harian.php">📋 Absensi Harian</a>
    <a href="mapel.php">📚 Absensi Mata Pelajaran</a>
    <a href="index.php" class="mobile-back">← Kembali ke Absensi</a>
    <a href="../../auth/logout.php" class="mobile-logout">Logout</a>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">
            <div class="p-3">
                <strong>MENU GURU</strong>
            </div>

            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="index.php">📝 Absensi</a>
            <a href="harian.php">📋 Absensi Harian</a>
            <a href="mapel.php">📚 Absensi Mata Pelajaran</a>
        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3>Absensi Harian</h3>
                    <p class="text-muted mb-0">
                        Absensi awal siswa berdasarkan kelas
                    </p>
                </div>

                <a href="index.php" class="btn btn-secondary">
                    ← Kembali
                </a>
            </div>

            <?php if ($success != "") { ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success); ?>
                </div>
            <?php } ?>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <div class="form-container">

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pilih Kelas dan Tanggal</h5>
                    </div>

                    <div class="card-body">

                        <form method="post">

                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-control" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($kelas = mysqli_fetch_assoc($data_kelas)) { ?>
                                        <option value="<?= $kelas['id']; ?>"
                                            <?= $kelas_id == $kelas['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($kelas['nama_kelas']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tanggal</label>
                                <input
                                    type="date"
                                    name="tanggal"
                                    class="form-control"
                                    value="<?= htmlspecialchars($tanggal); ?>"
                                    required
                                >
                            </div>

                            <button type="submit" name="tampilkan" class="btn btn-primary">
                                Tampilkan Siswa
                            </button>

                        </form>

                    </div>
                </div>

            </div>

            <?php if ($kelas_id > 0 && $tanggal != '') { ?>

                <?php if ($absensi_sudah_ada) { ?>

                    <div class="alert alert-warning">
                        Absensi harian untuk kelas tersebut pada tanggal
                        <strong><?= date('d-m-Y', strtotime($tanggal)); ?></strong>
                        sudah dibuat.
                    </div>

                    <div class="text-center mb-4">
                        <a href="detail.php?jenis=harian&id=<?= $absensi_id; ?>"
                           class="btn btn-info">
                            Lihat Detail Absensi
                        </a>
                    </div>

                <?php } else { ?>

                    <form method="post" onsubmit="return confirm('Yakin ingin menyimpan absensi harian ini?');">

                        <input type="hidden" name="kelas_id" value="<?= $kelas_id; ?>">
                        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>">

                        <div class="student-table shadow-sm">

                            <div class="mb-3">
                                <h5>Daftar Siswa</h5>
                                <p class="text-muted mb-0">
                                    Tanggal:
                                    <?= date('d-m-Y', strtotime($tanggal)); ?>
                                </p>
                            </div>

                            <?php if ($data_siswa && mysqli_num_rows($data_siswa) > 0) { ?>

                                <div class="table-responsive">

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama Siswa</th>
                                                <th>Jenis Kelamin</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php
                                            $no = 1;

                                            while ($siswa = mysqli_fetch_assoc($data_siswa)) {
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
                                                        <?= htmlspecialchars($siswa['jenis_kelamin']); ?>
                                                    </td>

                                                    <td>
                                                        <select
                                                            name="status[<?= $siswa['id']; ?>]"
                                                            class="form-control status-select"
                                                        >
                                                            <option value="Hadir">Hadir</option>
                                                            <option value="Sakit">Sakit</option>
                                                            <option value="Izin">Izin</option>
                                                            <option value="Alpa">Alpa</option>
                                                        </select>
                                                    </td>
                                                </tr>

                                            <?php } ?>

                                        </tbody>
                                    </table>

                                </div>

                                <div class="mt-3">
                                    <button type="submit" name="simpan" class="btn btn-primary">
                                        Simpan Absensi
                                    </button>
                                </div>

                            <?php } else { ?>

                                <div class="alert alert-warning mb-0">
                                    Belum ada siswa aktif pada kelas ini.
                                </div>

                            <?php } ?>

                        </div>

                    </form>

                <?php } ?>

            <?php } ?>

        </div>
    </div>
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>