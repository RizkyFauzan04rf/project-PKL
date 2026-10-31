<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || !in_array('guru', $_SESSION['roles'])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/koneksi.php";

$user_id = $_SESSION['user_id'];
$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');

$stmt = mysqli_prepare($koneksi, "
    SELECT wali_kelas.id, wali_kelas.kelas_id, kelas.nama_kelas
    FROM wali_kelas
    INNER JOIN guru ON wali_kelas.guru_id = guru.id
    INNER JOIN kelas ON wali_kelas.kelas_id = kelas.id
    INNER JOIN tahun_ajaran ON wali_kelas.tahun_ajaran_id = tahun_ajaran.id
    WHERE guru.user_id = ?
    AND tahun_ajaran.status = 'Aktif'
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wali = mysqli_fetch_assoc($result);

if (!$wali) {
    die("Data wali kelas tidak ditemukan.");
}

$kelas_id = $wali['kelas_id'];
$nama_kelas = $wali['nama_kelas'];

$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM absensi_harian
    WHERE kelas_id = ? AND tanggal = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "is", $kelas_id, $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$absensi = mysqli_fetch_assoc($result);

$error = "";

if (isset($_POST['simpan'])) {
    if ($absensi) {
        $error = "Absensi untuk kelas ini pada tanggal tersebut sudah dibuat.";
    } else {
        $statuses = $_POST['status'] ?? [];
        $keterangan = $_POST['keterangan'] ?? [];

        $stmt = mysqli_prepare($koneksi, "
            SELECT id
            FROM siswa
            WHERE kelas_id = ? AND status = 'aktif'
            ORDER BY nama
        ");
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $siswa = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $siswa[] = $row;
        }

        mysqli_begin_transaction($koneksi);

        try {
            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO absensi_harian (kelas_id, tanggal, dibuat_oleh)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "isi", $kelas_id, $tanggal, $user_id);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal menyimpan absensi.");
            }

            $absensi_id = mysqli_insert_id($koneksi);

            $stmt_detail = mysqli_prepare($koneksi, "
                INSERT INTO detail_absensi_harian
                (absensi_id, siswa_id, status, keterangan)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($siswa as $data) {
                $siswa_id = $data['id'];
                $status = isset($statuses[$siswa_id]) ? $statuses[$siswa_id] : 'Hadir';
                $ket = isset($keterangan[$siswa_id]) ? trim($keterangan[$siswa_id]) : "";

                if (!in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alpa'])) {
                    throw new Exception("Status absensi tidak valid.");
                }

                mysqli_stmt_bind_param($stmt_detail, "iiss", $absensi_id, $siswa_id, $status, $ket);

                if (!mysqli_stmt_execute($stmt_detail)) {
                    throw new Exception("Gagal menyimpan detail absensi.");
                }
            }

            mysqli_commit($koneksi);

            header("Location: absensi.php?success=1");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}

$stmt = mysqli_prepare($koneksi, "
    SELECT siswa.id, siswa.nis, siswa.nama, siswa.jenis_kelamin
    FROM siswa
    WHERE siswa.kelas_id = ?
    AND siswa.status = 'aktif'
    ORDER BY siswa.nama
");
mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Harian - Wali Kelas</title>

    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
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
            padding: 25px;
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
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }

            .sidebar.show {
                display: block;
            }

            .content {
                padding: 15px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Sistem Absensi TKJ</a>
        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>

        <a href="../auth/logout.php" class="logout-link">Logout</a>

        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 sidebar" id="sidebar">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="rekap.php">📊 Rekap Absensi</a>
            <a href="siswa.php">👨‍🎓 Data Siswa</a>
            <a href="absensi.php">📝 Absensi Harian</a>
            <a href="../auth/logout.php" style="color:#dc3545;">Logout</a>
        </div>

        <div class="col-md-9 content">

            <div class="card">
                <div class="card-header">
                    <h4>Absensi Harian Kelas <?php echo htmlspecialchars($nama_kelas); ?></h4>
                </div>

                <div class="card-body">

                    <?php if (isset($_GET['success'])) { ?>
                        <div class="alert alert-success">
                            Absensi berhasil disimpan.
                        </div>
                    <?php } ?>

                    <?php if ($error != "") { ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php } ?>

                    <?php if ($absensi) { ?>

                        <div class="alert alert-warning">
                            Absensi kelas <strong><?php echo htmlspecialchars($nama_kelas); ?></strong>
                            pada tanggal <strong><?php echo date('d-m-Y', strtotime($tanggal)); ?></strong>
                            sudah dibuat.
                        </div>

                        <a href="rekap.php" class="btn btn-primary">Lihat Rekap</a>

                    <?php } else { ?>

                        <form method="POST">

                            <div class="form-group">
                                <label>Kelas</label>
                                <input type="text" class="form-control"
                                       value="<?php echo htmlspecialchars($nama_kelas); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control"
                                       value="<?php echo htmlspecialchars($tanggal); ?>" required>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>JK</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                                <td>
                                                    <select name="status[<?php echo $row['id']; ?>]" class="form-control">
                                                        <option value="Hadir">Hadir</option>
                                                        <option value="Sakit">Sakit</option>
                                                        <option value="Izin">Izin</option>
                                                        <option value="Alpa">Alpa</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="keterangan[<?php echo $row['id']; ?>]"
                                                           class="form-control">
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" name="simpan" class="btn btn-success"
                                    onclick="return confirm('Yakin ingin menyimpan absensi ini?')">
                                Simpan Absensi
                            </button>

                        </form>

                    <?php } ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('sidebar').classList.toggle('show');
}
</script>

</body>
</html>