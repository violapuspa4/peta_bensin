<?php
require_once 'koneksi.php';

$rute_terpilih = null;
$jalur_dilewati = []; 

$graf_jarak = [
    'USER'   => ['SPBU A' => 2.1, 'SPBU C' => 2.4],
    'SPBU A' => ['USER' => 2.1, 'SPBU B' => 0.8],
    'SPBU B' => ['SPBU A' => 0.8, 'SPBU D' => 0.6],
    'SPBU C' => ['USER' => 2.4, 'SPBU E' => 1.5],
    'SPBU D' => ['SPBU B' => 0.6, 'SPBU E' => 1.0],
    'SPBU E' => ['SPBU C' => 1.5, 'SPBU D' => 1.0]
];

if (isset($_POST['cari_rute'])) {
    $db_spbu = [];
    $spbu_tutup = [];

    $res = $conn->query("SELECT nama_spbu, status FROM spbu");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $db_spbu[$row['nama_spbu']] = $row;
            if ($row['status'] == 'Tutup') {
                $spbu_tutup[] = $row['nama_spbu']; 
            }
        }
    }

    $jarak = []; 
    $sebelumnya = []; 
    $q = []; 

    foreach (array_keys($graf_jarak) as $node) {
        $jarak[$node] = INF;
        $sebelumnya[$node] = null;
        $q[$node] = INF;
    }
    $jarak['USER'] = 0;
    $q['USER'] = 0;

    while (!empty($q)) {
        $min_val = INF;
        $u = null;
        foreach ($q as $node => $val) {
            if ($val < $min_val) {
                $min_val = $val;
                $u = $node;
            }
        }
        if ($u === null) break;
        unset($q[$u]); 

        foreach ($graf_jarak[$u] as $v => $bobot) {
            $alt = $jarak[$u] + $bobot;
            if ($alt < $jarak[$v]) {
                $jarak[$v] = $alt;
                $sebelumnya[$v] = $u;
                if (isset($q[$v])) $q[$v] = $alt;
            }
        }
    }

    $waktu_terkecil = INF;
    $spbu_terbaik = null;
    $jarak_ke_terbaik = 0;

    foreach ($jarak as $nama_spbu => $dist_km) {
        if ($nama_spbu == 'USER' || $dist_km == INF) continue;
       
        if (in_array($nama_spbu, $spbu_tutup)) continue;

        $total_waktu = $dist_km * 3;

        if ($total_waktu < $waktu_terkecil) {
            $waktu_terkecil = $total_waktu;
            $spbu_terbaik = $nama_spbu;
            $jarak_ke_terbaik = $dist_km;
        }
    }

    if ($spbu_terbaik) {
        $rute_terpilih = [
            'nama_spbu' => $spbu_terbaik,
            'jarak' => round($jarak_ke_terbaik, 1),
            'waktu_estimasi' => round($waktu_terkecil, 1)
        ];

        $curr = $spbu_terbaik;
        while (isset($sebelumnya[$curr]) && $sebelumnya[$curr] !== null) {
            $prev = $sebelumnya[$curr];
            $jalur_dilewati[] = [$prev, $curr];
            $curr = $prev;
        }

        $j = $rute_terpilih['jarak'];
        $w = $rute_terpilih['waktu_estimasi'];
        $n = $conn->real_escape_string($rute_terpilih['nama_spbu']);
        $conn->query("INSERT INTO riwayat_pencarian (lokasi_awal, total_jarak, waktu_estimasi, spbu_tujuan) VALUES ('Titik User', $j, $w, '$n')");
    }
}

if (!function_exists('isGarisDilewati')) {
    function isGarisDilewati($n1, $n2, $jalur) {
        foreach ($jalur as $j) {
            if (($j[0] == $n1 && $j[1] == $n2) || ($j[0] == $n2 && $j[1] == $n1)) return true;
        }
        return false;
    }
}
?>