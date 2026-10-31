<?php
require_once "config/koneksi.php";

$password = password_hash("123456", PASSWORD_DEFAULT);

$username = [
    "siti.rahmah",
    "siti.rohani",
    "azeng.pratiwi"
];

foreach ($username as $user) {
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $password, $user);
    mysqli_stmt_execute($stmt);
}

echo "Password Siti Rahmah, Siti Rohani, dan Azeng Pratiwi berhasil diubah menjadi 123456";
?>