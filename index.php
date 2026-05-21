<?php
require_once 'koneksi.php';
require_once 'auth.php';
require_once 'dijkstra.php';

$error_login = "";
if (isset($_SESSION['error_login'])) {
    $error_login = $_SESSION['error_login'];
    unset($_SESSION['error_login']); 
}

$koordinat_dasar = [
    'USER'   => ['x' => 15, 'y' => 50],
    'SPBU A' => ['x' => 35, 'y' => 20],
    'SPBU B' => ['x' => 60, 'y' => 30],
    'SPBU C' => ['x' => 35, 'y' => 80],
    'SPBU D' => ['x' => 85, 'y' => 45],
    'SPBU E' => ['x' => 65, 'y' => 75]
];

$riwayat_res = $conn->query("SELECT * FROM riwayat_pencarian ORDER BY tanggal DESC LIMIT 5");
$res_spbu = $conn->query("SELECT * FROM spbu"); 
$spbu_list = [];
$koordinat = []; 
$koordinat['USER'] = $koordinat_dasar['USER'];

if ($res_spbu) {
    while($row = $res_spbu->fetch_assoc()) {
        $nama = $row['nama_spbu'];
        
        $x = !empty($row['pos_x']) ? (float)$row['pos_x'] : (isset($koordinat_dasar[$nama]) ? $koordinat_dasar[$nama]['x'] : rand(20, 80));
        $y = !empty($row['pos_y']) ? (float)$row['pos_y'] : (isset($koordinat_dasar[$nama]) ? $koordinat_dasar[$nama]['y'] : rand(20, 80));
        
        $row['pos_x'] = $x;
        $row['pos_y'] = $y;
        
        $spbu_list[] = $row;
        $koordinat[$nama] = ['x' => $x, 'y' => $y];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Bensin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .map-grid {
            background-color: #0f172a;
            background-image: radial-gradient(#334155 1px, transparent 1px);
            background-size: 24px 24px;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased selection:bg-blue-500/30">

    <header class="bg-slate-800/80 backdrop-blur-sm border-b border-slate-700/50 px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-blue-400 tracking-wide flex items-center gap-2">
                <span class="text-2xl">⛽</span> Peta Bensin
            </h1>
            <div>
                <?php if(isset($_SESSION['admin'])): ?>
                    <a href="admin.php" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 text-xs px-4 py-2 rounded-md font-semibold transition-all mr-2">Admin</a>
                    <a href="auth.php?logout=1" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs px-4 py-2 rounded-md font-semibold transition-all">Logout</a>
                <?php else: ?>
                    <button type="button" onclick="document.getElementById('login-modal').style.display='flex'" class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-5 py-2 rounded-md font-semibold shadow-lg shadow-blue-500/20 transition-all">
                        Login Admin
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="space-y-6 lg:col-span-1">
            <div class="bg-slate-800/50 p-5 rounded-xl border border-slate-700 shadow-sm backdrop-blur-sm">
                <h2 class="text-sm font-semibold mb-4 text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    📍 Navigasi SPBU
                </h2>
                <form method="POST">
                    <button type="submit" name="cari_rute" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 px-4 rounded-lg text-sm transition-all shadow-lg shadow-blue-500/20 tracking-wide">
                        Cari Rute Terdekat
                    </button>
                </form>
            </div>

            <?php if (isset($rute_terpilih) && $rute_terpilih): ?>
                <div class="bg-blue-900/20 border border-blue-500/50 p-5 rounded-xl shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10 text-6xl">🏆</div>
                    <h3 class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Rute Optimal</h3>
                    <div class="text-2xl font-black mb-1 text-slate-100"><?= htmlspecialchars($rute_terpilih['nama_spbu']) ?></div>
                    <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-blue-500/20 text-left">
                        <div class="bg-slate-900/50 p-2 rounded-md border border-slate-700">
                            <div class="text-[10px] text-slate-400 uppercase">Total Jarak</div>
                            <div class="text-sm font-bold text-blue-300"><?= $rute_terpilih['jarak'] ?> KM</div>
                        </div>
                        <div class="bg-slate-900/50 p-2 rounded-md border border-slate-700">
                            <div class="text-[10px] text-slate-400 uppercase">Estimasi Waktu</div>
                            <div class="text-sm font-bold text-emerald-400"><?= $rute_terpilih['waktu_estimasi'] ?> Mnt</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-slate-800/50 p-5 rounded-xl border border-slate-700 shadow-sm">
                <h2 class="text-xs font-bold mb-3 text-slate-400 uppercase tracking-wider">⏱️ Riwayat Pencarian</h2>
                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2">
                    <?php 
                    if ($riwayat_res && $riwayat_res->num_rows > 0) {
                        while($row = $riwayat_res->fetch_assoc()): 
                    ?>
                            <div class="bg-slate-900/50 p-3 rounded-lg text-xs border border-slate-700/50 hover:border-slate-600 transition-colors">
                                <div class="flex justify-between font-medium mb-1">
                                    <span class="text-slate-200"><?= htmlspecialchars($row['spbu_tujuan']) ?></span>
                                    <span class="text-emerald-400"><?= $row['waktu_estimasi'] ?> mnt</span>
                                </div>
                                <div class="text-[10px] text-slate-500 flex justify-between">
                                    <span><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></span>
                                    <span>Jarak: <?= $row['total_jarak'] ?> KM</span>
                                </div>
                            </div>
                    <?php 
                        endwhile; 
                    } else {
                        echo "<p class='text-xs text-slate-500 italic py-2'>Belum ada riwayat pencarian.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="map-grid h-[32rem] rounded-xl border border-slate-700 shadow-inner relative overflow-hidden ring-1 ring-white/5">
                <svg class="absolute inset-0 w-full h-full z-0 pointer-events-none">
                    <?php 
                    $digambar = [];
                    foreach ($graf_jarak as $node_awal => $tetangga) {
                        foreach ($tetangga as $node_tujuan => $bobot) {
                            $unik1 = $node_awal . "-" . $node_tujuan;
                            $unik2 = $node_tujuan . "-" . $node_awal;

                            if (in_array($unik1, $digambar) || in_array($unik2, $digambar)) continue;
                            $digambar[] = $unik1;

                            $x1 = isset($koordinat[$node_awal]['x']) ? $koordinat[$node_awal]['x'] : 0;
                            $y1 = isset($koordinat[$node_awal]['y']) ? $koordinat[$node_awal]['y'] : 0;
                            $x2 = isset($koordinat[$node_tujuan]['x']) ? $koordinat[$node_tujuan]['x'] : 0;
                            $y2 = isset($koordinat[$node_tujuan]['y']) ? $koordinat[$node_tujuan]['y'] : 0;

                            $is_dilewati = (isset($jalur_dilewati) && is_callable('isGarisDilewati') && isGarisDilewati($node_awal, $node_tujuan, $jalur_dilewati));
                            
                            $warna = $is_dilewati ? '#3b82f6' : '#334155'; 
                            $tebal = $is_dilewati ? '3' : '2';
                            $putus = $is_dilewati ? '' : 'stroke-dasharray="6,6"';
                            
                            echo "<line x1='{$x1}%' y1='{$y1}%' x2='{$x2}%' y2='{$y2}%' stroke='{$warna}' stroke-width='{$tebal}' {$putus} class='transition-all duration-500'/>\n";

                            $mid_x = ($x1 + $x2) / 2;
                            $mid_y = ($y1 + $y2) / 2;
                            $warna_teks = $is_dilewati ? '#93c5fd' : '#64748b'; 

                            echo "<rect x='" . ($mid_x - 1.5) . "%' y='" . ($mid_y - 2.5) . "%' width='3%' height='4%' fill='#0f172a' rx='2'></rect>";
                            echo "<text x='{$mid_x}%' y='" . ($mid_y + 0.5) . "%' fill='{$warna_teks}' font-size='10' font-weight='600' text-anchor='middle'>{$bobot}</text>\n";
                        }
                    }
                    ?>
                </svg>

                <div class="absolute w-10 h-10 bg-blue-600 rounded-full flex flex-col items-center justify-center text-white font-bold text-xs shadow-[0_0_20px_rgba(37,99,235,0.4)] border-2 border-slate-900 z-10 -translate-x-1/2 -translate-y-1/2 transition-all" style="left: <?= $koordinat['USER']['x'] ?>%; top: <?= $koordinat['USER']['y'] ?>%;">
                    👤
                </div>

                <?php foreach ($spbu_list as $spbu): 
                    $is_buka = $spbu['status'] == 'Buka';
                    $bg_color = $is_buka ? 'bg-emerald-500' : 'bg-rose-500';
                    $border_color = $is_buka ? 'border-emerald-700' : 'border-rose-700';
                    
                    $is_selected = (isset($rute_terpilih) && $rute_terpilih['nama_spbu'] == $spbu['nama_spbu']);
                    if ($is_selected) {
                        $bg_color = 'bg-blue-500';
                        $border_color = 'border-blue-400 ring-4 ring-blue-500/30';
                    }
                    
                    $pos_x = isset($spbu['pos_x']) ? $spbu['pos_x'] : 50;
                    $pos_y = isset($spbu['pos_y']) ? $spbu['pos_y'] : 50;
                ?>
                    <div class="absolute flex flex-col items-center z-10 -translate-x-1/2 -translate-y-1/2 group" style="left: <?= $pos_x ?>%; top: <?= $pos_y ?>%;">
                        <div class="w-8 h-8 <?= $bg_color ?> rounded-full border-2 <?= $border_color ?> flex items-center justify-center text-sm shadow-lg transition-transform duration-200 group-hover:scale-110 cursor-pointer">
                            ⛽
                        </div>
                        <div class="mt-2 bg-slate-900/90 px-3 py-1.5 rounded-md text-[10px] text-slate-200 whitespace-nowrap border border-slate-700 text-center shadow-xl backdrop-blur-md opacity-90 group-hover:opacity-100 group-hover:-translate-y-1 transition-all">
                            <span class="font-bold"><?= htmlspecialchars($spbu['nama_spbu']) ?></span><br>
                            <span class="<?= $is_buka ? 'text-emerald-400' : 'text-rose-400' ?>"><?= $spbu['status'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <div id="login-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm items-center justify-center p-4 z-[100]" style="<?= isset($error_login) && $error_login ? 'display: flex;' : 'display: none;' ?>">
        <div class="bg-slate-900 p-8 rounded-2xl border border-slate-700 max-w-sm w-full shadow-2xl relative">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-200 flex items-center gap-2">
                    <span class="text-blue-500">🛡️</span> Otorisasi Admin
                </h3>
                <button type="button" onclick="document.getElementById('login-modal').style.display='none'" class="text-slate-500 hover:text-slate-300 text-2xl transition-colors">&times;</button>
            </div>
            
            <?php if(isset($error_login) && $error_login): ?>
                <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 text-xs rounded-md mb-5 text-center font-medium">
                    <?= htmlspecialchars($error_login) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="auth.php">
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1 block">Username</label>
                        <input type="text" name="username" required class="w-full bg-slate-950 text-sm text-slate-200 p-3 rounded-lg border border-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all placeholder-slate-600" placeholder="Masukkan username">
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1 block">Password</label>
                        <input type="password" name="password" required class="w-full bg-slate-950 text-sm text-slate-200 p-3 rounded-lg border border-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all placeholder-slate-600" placeholder="••••••••">
                    </div>
                    <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg text-sm transition-all shadow-lg shadow-blue-500/20 mt-2">
                        Masuk Sistem
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>