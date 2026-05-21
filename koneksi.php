<?php
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli("localhost", "root", "", "peta_bensin_skpl");
if ($conn->connect_error) {
    die("<div style='background:#b91c1c;color:white;padding:20px;text-align:center;'>KONEKSI GAGAL: Pastikan Database 'peta_bensin_skpl' sudah dibuat di phpMyAdmin.</div>");
}
?>