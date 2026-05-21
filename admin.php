<?php
session_start();
require_once 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// --- PROSES UPDATE STATUS SPBU SAJA ---
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id_spbu'];
    $status = $conn->real_escape_string($_POST['status']);
    
    // Hanya kolom status yang di-update
    $conn->query("UPDATE spbu SET status='$status' WHERE id_spbu=$id");
    header("Location: admin.php");
    exit();
}

// Ambil semua data SPBU
$res_spbu = $conn->query("SELECT * FROM spbu ORDER BY id_spbu ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Peta Bensin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased">

    <header class="bg-slate-800 border-b border-slate-700 px-6 py-4 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-blue-400 tracking-wide flex items-center gap-2">
                <span class="text-2xl">⚙️</span> Panel Admin SPBU
            </h1>
            <div class="flex gap-4 items-center">
                <a href="index.php" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-4 py-2 rounded-md font-semibold transition-all">Lihat Peta User</a>
                <a href="auth.php?logout=1" class="bg-rose-600 hover:bg-rose-500 text-white text-xs px-4 py-2 rounded-md font-semibold transition-all shadow-lg shadow-rose-500/20">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-6 mt-4">
        
        <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl overflow-x-auto">
            <h2 class="text-sm font-bold mb-4 text-blue-400 uppercase tracking-wider border-b border-slate-700 pb-3">📋 Kelola Status Operasional SPBU</h2>
            <table class="w-full text-left border-collapse mt-2">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="p-4 rounded-tl-lg border-b border-slate-700">Nama & Lokasi SPBU</th>
                        <th class="p-4 border-b border-slate-700 text-center w-32">Status Aktif</th>
                        <th class="p-4 rounded-tr-lg border-b border-slate-700 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php 
                    if ($res_spbu && $res_spbu->num_rows > 0) {
                        while ($spbu = $res_spbu->fetch_assoc()):
                            $is_buka = $spbu['status'] == 'Buka';
                            // Membuat ID unik untuk setiap baris agar form tidak tertukar
                            $form_id = "form_update_" . $spbu['id_spbu']; 
                    ?>
                        <tr class="border-b border-slate-700/50 hover:bg-slate-750/50 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-slate-200 text-base mb-1">
                                    <?= htmlspecialchars($spbu['nama_spbu']) ?>
                                </div>
                                <div class="text-slate-400 text-xs flex items-center">
                                    <span class="mr-1 opacity-70">📍</span>
                                    <?= !empty($spbu['alamat']) ? htmlspecialchars($spbu['alamat']) : 'Alamat tidak tersedia' ?>
                                </div>
                            </td>
                            
                            <td class="p-4 text-center">
                                <select form="<?= $form_id ?>" name="status" class="w-full bg-slate-900 text-slate-200 text-xs font-bold p-2.5 rounded-lg border border-slate-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                    <option value="Buka" <?= $is_buka ? 'selected' : '' ?> class="text-emerald-400 bg-slate-800">BUKA</option>
                                    <option value="Tutup" <?= !$is_buka ? 'selected' : '' ?> class="text-rose-400 bg-slate-800">TUTUP</option>
                                </select>
                            </td>

                            <td class="p-4 text-center">
                                <form id="<?= $form_id ?>" method="POST" action="admin.php">
                                    <input type="hidden" name="id_spbu" value="<?= $spbu['id_spbu'] ?>">
                                    <button type="submit" name="update_status" class="bg-blue-600/20 hover:bg-blue-500 text-blue-400 hover:text-white border border-blue-500/30 hover:border-blue-500 px-4 py-2.5 rounded-lg text-xs font-bold transition-all w-full shadow-lg">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    } else {
                        echo "<tr><td colspan='3' class='p-8 text-center text-slate-500 text-sm italic bg-slate-900/30'>Belum ada data SPBU</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>