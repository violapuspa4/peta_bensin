<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT nama, password FROM admin WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Catatan: Sesuai data dump SQL Anda, password default adalah '12345'
            if ($password === $row['password']) {
                $_SESSION['admin'] = $row['nama'];
                header("Location: admin.php"); // Jika sukses, langsung ke halaman admin baru
                exit();
            } else {
                $_SESSION['error_login'] = "Password yang Anda masukkan salah!";
            }
        } else {
            $_SESSION['error_login'] = "Username admin tidak ditemukan!";
        }
        $stmt->close();
    }
    
    // JIKA GAGAL: Kembalikan ke index.php agar tidak memunculkan layar putih polos
    header("Location: index.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>