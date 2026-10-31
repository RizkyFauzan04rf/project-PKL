<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['roles']) || !in_array('admin', $_SESSION['roles'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$bulan = isset($_GET['bulan']) && preg_match('/^\d{4}-\d{2}$/', $_GET['bulan'])
    ? $_GET['bulan']
    : date('Y-m');

$kelas_id = isset($_GET['kelas_id']) && is_numeric($_GET['kelas_id'])
    ? (int) $_GET['kelas_id']
    : 0;

$tanggal_awal = $bulan . '-01';
$tanggal_akhir = date('Y-m-t', strtotime($tanggal_awal));

$daftar_tanggal = [];
$tanggal_loop = new DateTime($tanggal_awal);
$tanggal_selesai = new DateTime($tanggal_akhir);

$nama_hari = [
    1 => 'Sen',
    2 => 'Sel',
    3 => 'Rab',
    4 => 'Kam',
    5 => 'Jum'
];

$nama_bulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

while ($tanggal_loop <= $tanggal_selesai) {
    $hari = (int) $tanggal_loop->format('N');

    if ($hari <= 5) {
        $daftar_tanggal[] = clone $tanggal_loop;
    }

    $tanggal_loop->modify('+1 day');
}

$kelas_result = mysqli_query($koneksi, "
    SELECT *
    FROM kelas
    ORDER BY tingkat, nama_kelas
");

if ($kelas_id > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT
            siswa.id,
            siswa.nama,
            kelas.nama_kelas
        FROM siswa
        INNER JOIN kelas ON siswa.kelas_id = kelas.id
        WHERE siswa.kelas_id = ?
        AND siswa.status = 'aktif'
        ORDER BY siswa.nama
    ");

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
} else {
    $stmt = mysqli_prepare($koneksi, "
        SELECT
            siswa.id,
            siswa.nama,
            kelas.nama_kelas
        FROM siswa
        INNER JOIN kelas ON siswa.kelas_id = kelas.id
        WHERE siswa.status = 'aktif'
        ORDER BY kelas.tingkat, kelas.nama_kelas, siswa.nama
    ");
}

mysqli_stmt_execute($stmt);
$siswa_result = mysqli_stmt_get_result($stmt);

$siswa_data = [];

while ($row = mysqli_fetch_assoc($siswa_result)) {
    $siswa_data[$row['id']] = [
        'nama' => $row['nama'],
        'nama_kelas' => $row['nama_kelas']
    ];
}

$rekap = [];

foreach ($siswa_data as $siswa_id => $siswa) {
    $rekap[$siswa_id] = [
        'nama' => $siswa['nama'],
        'nama_kelas' => $siswa['nama_kelas'],
        'status' => []
    ];

    foreach ($daftar_tanggal as $tanggal) {
        $rekap[$siswa_id]['status'][$tanggal->format('Y-m-d')] = '-';
    }
}

if (!empty($siswa_data)) {
    if ($kelas_id > 0) {
        $stmt = mysqli_prepare($koneksi, "
            SELECT
                ah.tanggal,
                dah.siswa_id,
                dah.status
            FROM absensi_harian ah
            INNER JOIN detail_absensi_harian dah
                ON ah.id = dah.absensi_id
            WHERE ah.tanggal BETWEEN ? AND ?
            AND ah.kelas_id = ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $tanggal_awal,
            $tanggal_akhir,
            $kelas_id
        );
    } else {
        $stmt = mysqli_prepare($koneksi, "
            SELECT
                ah.tanggal,
                dah.siswa_id,
                dah.status
            FROM absensi_harian ah
            INNER JOIN detail_absensi_harian dah
                ON ah.id = dah.absensi_id
            WHERE ah.tanggal BETWEEN ? AND ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $tanggal_awal,
            $tanggal_akhir
        );
    }

    mysqli_stmt_execute($stmt);
    $hasil_harian = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($hasil_harian)) {
        if (!isset($rekap[$row['siswa_id']])) {
            continue;
        }

        $status = '-';

        if ($row['status'] == 'Hadir') {
            $status = 'H';
        } elseif ($row['status'] == 'Sakit') {
            $status = 'S';
        } elseif ($row['status'] == 'Izin') {
            $status = 'I';
        } elseif ($row['status'] == 'Alpa') {
            $status = 'A';
        }

        if (isset($rekap[$row['siswa_id']]['status'][$row['tanggal']])) {
            $rekap[$row['siswa_id']]['status'][$row['tanggal']] = $status;
        }
    }

    if ($kelas_id > 0) {
        $stmt = mysqli_prepare($koneksi, "
            SELECT
                am.tanggal,
                dam.siswa_id
            FROM absensi_mapel am
            INNER JOIN mengajar m
                ON am.mengajar_id = m.id
            INNER JOIN detail_absensi_mapel dam
                ON am.id = dam.absensi_mapel_id
            WHERE am.tanggal BETWEEN ? AND ?
            AND m.kelas_id = ?
            AND dam.status = 'Cabut'
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $tanggal_awal,
            $tanggal_akhir,
            $kelas_id
        );
    } else {
        $stmt = mysqli_prepare($koneksi, "
            SELECT
                am.tanggal,
                dam.siswa_id
            FROM absensi_mapel am
            INNER JOIN mengajar m
                ON am.mengajar_id = m.id
            INNER JOIN detail_absensi_mapel dam
                ON am.id = dam.absensi_mapel_id
            WHERE am.tanggal BETWEEN ? AND ?
            AND dam.status = 'Cabut'
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $tanggal_awal,
            $tanggal_akhir
        );
    }

    mysqli_stmt_execute($stmt);
    $hasil_cabut = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($hasil_cabut)) {
        if (!isset($rekap[$row['siswa_id']])) {
            continue;
        }

        if (isset($rekap[$row['siswa_id']]['status'][$row['tanggal']])) {
            $rekap[$row['siswa_id']]['status'][$row['tanggal']] = 'C';
        }
    }
}

$bulan_tampil = $nama_bulan[date('m', strtotime($tanggal_awal))] . ' ' . date('Y', strtotime($tanggal_awal));

$kelas_tampil = 'Semua Kelas';

if ($kelas_id > 0) {
    $kelas_nama_query = mysqli_prepare(
        $koneksi,
        "SELECT nama_kelas FROM kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($kelas_nama_query, "i", $kelas_id);
    mysqli_stmt_execute($kelas_nama_query);

    $kelas_nama_result = mysqli_stmt_get_result($kelas_nama_query);
    $kelas_nama_data = mysqli_fetch_assoc($kelas_nama_result);

    if ($kelas_nama_data) {
        $kelas_tampil = $kelas_nama_data['nama_kelas'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - Sistem Absensi TKJ</title>

    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/dataTables.bootstrap.min.css">

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

        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
        }

        .mobile-menu {
            display: none;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .status-cell {
            text-align: center;
            font-weight: bold;
        }

        .print-table {
            display: none;
        }

        @media (max-width: 767px) {
            .navbar-brand {
                font-size: 17px;
                margin-right: 10px;
            }

            .admin-name {
                font-size: 14px;
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

            .content {
                padding: 20px 15px;
                text-align: center;
            }

            .content h3 {
                font-size: 22px;
            }

            .content .d-flex {
                flex-direction: column;
                align-items: center !important;
                gap: 10px;
            }

            .table-container {
                padding: 12px;
                border-radius: 8px;
                text-align: left;
            }

            .table {
                min-width: 650px;
            }

            .filter-row {
                display: flex;
                flex-direction: column;
            }

            .filter-row .form-group {
                width: 100%;
            }

            .filter-buttons {
                margin-top: 0 !important;
            }

            .filter-buttons .btn {
                width: 100%;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                background: #ffffff !important;
            }

            .navbar,
            .sidebar,
            .mobile-menu,
            .filter-card,
            .print-button,
            .screen-table,
            .dataTables_wrapper {
                display: none !important;
            }

            .content {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                text-align: left !important;
            }

            .content-header {
                display: block !important;
                margin-bottom: 10px !important;
            }

            .table-container {
                background: #ffffff !important;
                padding: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .print-table {
                display: table !important;
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 8px !important;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000 !important;
                padding: 3px !important;
                white-space: nowrap !important;
                text-align: center !important;
                vertical-align: middle !important;
            }

            .print-table th:nth-child(3) {
                text-align: left !important;
            }

            .print-table thead {
                display: table-header-group !important;
            }

            .print-table tr {
                page-break-inside: avoid !important;
            }

            .print-table .nama-siswa {
                text-align: left !important;
            }

            .print-title {
                display: block !important;
                text-align: center !important;
                margin-bottom: 8px !important;
            }

            .print-info {
                display: block !important;
                margin-bottom: 10px !important;
                font-size: 10px !important;
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

    <a href="../dashboard.php">🏠 Dashboard</a>
    <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
    <a href="../guru/index.php">👨‍🏫 Data Guru</a>
    <a href="../kelas/index.php">🏫 Data Kelas</a>
    <a href="../mapel/index.php">📚 Mata Pelajaran</a>
    <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
    <a href="../mengajar/index.php">📝 Data Mengajar</a>
    <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
    <a href="../sekretaris/index.php">📋 Sekretaris</a>
    <a href="../absensi/index.php">📝 Absensi</a>
    <a href="index.php">📊 Rekap Absensi</a>
    <a href="../../auth/logout.php" class="logout">Logout</a>

</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar p-0">

            <div class="p-3">
                <strong>MENU ADMIN</strong>
            </div>

            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="../siswa/index.php">👨‍🎓 Data Siswa</a>
            <a href="../guru/index.php">👨‍🏫 Data Guru</a>
            <a href="../kelas/index.php">🏫 Data Kelas</a>
            <a href="../mapel/index.php">📚 Mata Pelajaran</a>
            <a href="../tahun_ajaran/index.php">📅 Tahun Ajaran</a>
            <a href="../mengajar/index.php">📝 Data Mengajar</a>
            <a href="../wali_kelas/index.php">👤 Wali Kelas</a>
            <a href="../sekretaris/index.php">📋 Sekretaris</a>
            <a href="../absensi/index.php">📝 Absensi</a>
            <a href="index.php">📊 Rekap Absensi</a>

        </div>

        <div class="col-md-10 content">

            <div class="d-flex justify-content-between align-items-center mb-3 content-header">

                <div>
                    <h3>Rekap Absensi Bulanan</h3>
                    <p class="text-muted mb-0">Mengelola rekap absensi siswa</p>
                </div>

                <button
                    type="button"
                    class="btn btn-default print-button"
                    onclick="window.print()"
                >
                    🖨 Cetak
                </button>

            </div>

            <div class="table-container shadow-sm">

                <form method="GET" class="filter-card">

                    <div class="row filter-row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bulan</label>

                                <input
                                    type="month"
                                    name="bulan"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($bulan); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Kelas</label>

                                <select name="kelas_id" class="form-control">

                                    <option value="0">Semua Kelas</option>

                                    <?php while ($kelas = mysqli_fetch_assoc($kelas_result)) { ?>

                                        <option
                                            value="<?php echo $kelas['id']; ?>"
                                            <?php echo ($kelas_id == $kelas['id']) ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                        </option>

                                    <?php } ?>

                                </select>

                            </div>
                        </div>

                        <div class="col-md-4 filter-buttons" style="margin-top: 25px;">

                            <button type="submit" class="btn btn-primary">
                                Tampilkan
                            </button>

                        </div>

                    </div>

                </form>

                <div class="alert alert-info">

                    Periode:
                    <strong><?php echo $bulan_tampil; ?></strong>

                    <br>

                    Kelas:
                    <strong><?php echo htmlspecialchars($kelas_tampil); ?></strong>

                    <br>

                    H = Hadir, S = Sakit, I = Izin, A = Alpa, C = Cabut, - = Belum Absen

                </div>

                <div class="table-responsive screen-table">

                    <table id="dataTable" class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>No</th>

                                <?php if ($kelas_id == 0) { ?>

                                    <th>Kelas</th>

                                <?php } ?>

                                <th>Nama</th>

                                <?php foreach ($daftar_tanggal as $tanggal) { ?>

                                    <?php
                                    $hari = (int) $tanggal->format('N');
                                    ?>

                                    <th>
                                        <?php echo $nama_hari[$hari]; ?><br>
                                        <?php echo $tanggal->format('d'); ?>
                                    </th>

                                <?php } ?>

                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            foreach ($rekap as $siswa):
                            ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <?php if ($kelas_id == 0) { ?>

                                    <td>
                                        <?= htmlspecialchars($siswa['nama_kelas']); ?>
                                    </td>

                                <?php } ?>

                                <td>
                                    <?= htmlspecialchars($siswa['nama']); ?>
                                </td>

                                <?php foreach ($daftar_tanggal as $tanggal) { ?>

                                    <?php
                                    $tgl = $tanggal->format('Y-m-d');
                                    $status = $siswa['status'][$tgl];
                                    ?>

                                    <td class="status-cell">
                                        <?= htmlspecialchars($status); ?>
                                    </td>

                                <?php } ?>

                            </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="print-title">

                    <h3>REKAP ABSENSI SISWA</h3>

                </div>

                <div class="print-info">

                    <strong>Bulan:</strong> <?php echo $bulan_tampil; ?>

                    <br>

                    <strong>Kelas:</strong> <?php echo htmlspecialchars($kelas_tampil); ?>

                    <br>

                    H = Hadir, S = Sakit, I = Izin, A = Alpa, C = Cabut, - = Belum Absen

                </div>

                <table class="print-table">

                    <thead>

                        <tr>

                            <th>No</th>

                            <?php if ($kelas_id == 0) { ?>

                                <th>Kelas</th>

                            <?php } ?>

                            <th>Nama</th>

                            <?php foreach ($daftar_tanggal as $tanggal) { ?>

                                <?php
                                $hari = (int) $tanggal->format('N');
                                ?>

                                <th>
                                    <?php echo $nama_hari[$hari]; ?><br>
                                    <?php echo $tanggal->format('d'); ?>
                                </th>

                            <?php } ?>

                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $no_print = 1;

                        foreach ($rekap as $siswa):
                        ?>

                        <tr>

                            <td>
                                <?php echo $no_print++; ?>
                            </td>

                            <?php if ($kelas_id == 0) { ?>

                                <td>
                                    <?php echo htmlspecialchars($siswa['nama_kelas']); ?>
                                </td>

                            <?php } ?>

                            <td class="nama-siswa">
                                <?php echo htmlspecialchars($siswa['nama']); ?>
                            </td>

                            <?php foreach ($daftar_tanggal as $tanggal) { ?>

                                <?php
                                $tgl = $tanggal->format('Y-m-d');
                                $status = $siswa['status'][$tgl];
                                ?>

                                <td>
                                    <?php echo htmlspecialchars($status); ?>
                                </td>

                            <?php } ?>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<script src="../../bootstrap/js/jquery.min.js"></script>
<script src="../../bootstrap/js/bootstrap.min.js"></script>
<script src="../../bootstrap/js/jquery.dataTables.js"></script>
<script src="../../bootstrap/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        pageLength: 10,
        scrollX: true
    });
});

function toggleMenu() {
    $('#mobileMenu').toggleClass('active');
}
</script>

</body>
</html>