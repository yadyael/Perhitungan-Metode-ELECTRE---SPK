<?php
$base_url = '/METODE-ELECTRE/';

/* =========================
   DETEKSI HALAMAN AKTIF
   Bandingkan folder pertama dari REQUEST_URI
   (lebih reliable daripada basename, karena
   banyak halaman sama-sama bernama index.php)
========================= */
$request_path = strtok($_SERVER['REQUEST_URI'], '?');           // buang query string
$base_path    = rtrim($base_url, '/');                           // "/METODE-ELECTRE"
$relative     = trim(str_replace($base_path, '', $request_path), '/'); // "nilai/index.php"
$segments     = explode('/', $relative);
$current_dir  = $segments[0] ?? '';                              // "nilai", "dashboard.php", "hasil"
$current_file = end($segments);                                  // "index.php", "dashboard.php", "ranking.php"

function isActive($target, $current_dir, $current_file) {
    // target contoh: "dashboard.php" atau "nilai/index.php" atau "hasil/ranking.php"
    $target_segments = explode('/', trim($target, '/'));

    if (count($target_segments) === 1) {
        // file langsung di root, misal dashboard.php
        return $current_dir === $target_segments[0];
    }

    // target berupa folder/file, misal "nilai/index.php" atau "hasil/ranking.php"
    [$target_dir, $target_file] = $target_segments;
    return $current_dir === $target_dir && $current_file === $target_file;
}
?>
<style>
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:280px;
    height:100vh;
    z-index:1000;
    padding:30px 20px;
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(25px);
    border-right:1px solid rgba(255,255,255,.6);
    display:flex;
    flex-direction:column;
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:40px;
    flex-shrink:0;
}

.logo img{
    width:48px;
    height:48px;
    object-fit:contain;
}

.logo-text{
    display:flex;
    flex-direction:column;
}

.logo-text span{
    font-size:18px;
    font-weight:600;
    color:var(--teal-mid);
}

.logo-text small{
    color:var(--text-soft);
    font-size:11px;
}

/* area menu bisa di-scroll sendiri jika daftar menu memanjang,
   tanpa mengganggu posisi logout yang fixed di bawah */
.menu-scroll{
    flex:1;
    /* overflow-y:auto; */
    display:flex;
    flex-direction:column;
}

.menu{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.menu a{
    text-decoration:none;
    color:var(--text-dark);
    padding:14px 18px;
    border-radius:14px;
    transition:.3s;
    position:relative;
}

.menu a:hover{
    background:var(--teal-light);
    color:white;
    transform:translateX(5px);
}

/* === STATE AKTIF === */
.menu a.active{
    background:var(--teal-mid);
    color:#fff;
    font-weight:600;
    box-shadow:0 6px 16px rgba(75,157,169,.35);
}

.menu a.active::before{
    content:'';
    position:absolute;
    left:-20px;
    top:50%;
    transform:translateY(-50%);
    width:4px;
    height:60%;
    background:var(--orange);
    border-radius:0 4px 4px 0;
}

.menu a.active:hover{
    transform:none;
    background:var(--teal-mid);
}

.menu-divider{
    height:1px;
    background:rgba(75,157,169,.15);
    margin:10px 0;
}

/* logout terkunci di bagian bawah sidebar */
.logout-wrap{
    flex-shrink:0;
    margin-top:14px;
    padding-top:14px;
    border-top:1px solid rgba(75,157,169,.15);
}

.logout-btn{
    text-decoration:none;
    padding:14px 18px;
    border-radius:14px;
    transition:.3s;
    background:rgba(227,116,52,.10);
    color:var(--orange) !important;
    display:block;
}

.logout-btn:hover{
    background:var(--orange) !important;
    color:white !important;
    transform:none !important;
}
</style>

<div class="sidebar">
    <div class="logo">
        <img src="<?= $base_url ?>assets/maple-leaf.png" alt="Logo SPK">
        <div class="logo-text">
            <span>SPK ELECTRE</span>
            <small>IPM Jatim & Jateng 2024</small>
        </div>
    </div>

    <div class="menu-scroll">
        <div class="menu">
            <a href="<?= $base_url ?>dashboard.php"
               class="<?= isActive('dashboard.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Dashboard
            </a>
            <a href="<?= $base_url ?>kriteria/index.php"
               class="<?= isActive('kriteria/index.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Kriteria
            </a>
            <a href="<?= $base_url ?>alternatif/index.php"
               class="<?= isActive('alternatif/index.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Alternatif
            </a>
            <a href="<?= $base_url ?>nilai/index.php"
               class="<?= isActive('nilai/index.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Penilaian
            </a>
            <a href="<?= $base_url ?>hasil/index.php"
               class="<?= isActive('hasil/index.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Perhitungan
            </a>
            <a href="<?= $base_url ?>hasil/ranking.php"
               class="<?= isActive('hasil/ranking.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Ranking Φ
            </a>
            <a href="<?= $base_url ?>export_excel/index.php"
               class="<?= isActive('export_excel/index.php', $current_dir, $current_file) ? 'active' : '' ?>">
                Export
            </a>
            
        </div>
    </div>

    <div class="logout-wrap">
        <a href="<?= $base_url ?>auth/logout.php" class="logout-btn">
            <b>Logout</b>
        </a>
    </div>
</div>
