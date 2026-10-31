<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_SESSION['roles']) || empty($_SESSION['roles'])) {
    session_unset();
    session_destroy();

    header("Location: auth/login.php");
    exit;
}

if (in_array('admin', $_SESSION['roles'])) {
    header("Location: admin/dashboard.php");
    exit;
} elseif (in_array('guru', $_SESSION['roles'])) {
    header("Location: guru/dashboard.php");
    exit;
} elseif (in_array('sekretaris', $_SESSION['roles'])) {
    header("Location: sekretaris/dashboard.php");
    exit;
} else {
    session_unset();
    session_destroy();

    header("Location: auth/login.php");
    exit;
}
?>