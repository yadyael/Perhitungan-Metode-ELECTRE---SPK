<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';
require_once '../includes/electre_engine.php';

/* =========================
   AJAX: JALANKAN PERHITUNGAN
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['ajax_hitung']) &&
    $_POST['ajax_hitung'] === '1'
) {
    header('Content-Type: application/json');
    try {
        $summary = hitungElectre($conn);
        echo json_encode([
            'status'  => 'success',
            'message' => 'Perhitungan ELECTRE I berhasil diselesaikan untuk seluruh ' . $summary['m'] . ' alternatif dan ' . $summary['n'] . ' kriteria.',
            'summary' => $summary,
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

/* =========================
   AMBIL DATA UTAMA
========================= */
$alternatif = [];
$res = $conn->query("SELECT id, nama_daerah, provinsi FROM tbl_alternatif ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $alternatif[] = $row;

$kriteria = [];
$res = $conn->query("SELECT id, kode, nama_kriteria, bobot, tipe FROM tbl_kriteria ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $kriteria[] = $row;

$m = count($alternatif);
$n = count($kriteria);

// Index 1..m untuk header matriks pasangan (C, D, F, G, E)
$altIndex = [];
foreach ($alternatif as $i => $alt) {
    $altIndex[(int) $alt['id']] = $i + 1;
}

$status    = getElectreStatus($conn);
$hasResult = $status['has_result'];

$X = $R = $V = $C = $D = $F = $G = $E = [];
$hasil = [];
$cBar  = $dBar = 0;

if ($hasResult) {
    $X = pivotKriteriaMatrix($conn, 'tbl_nilai', 'nilai');
    $R = pivotKriteriaMatrix($conn, 'tbl_normalisasi', 'nilai_r');
    $V = pivotKriteriaMatrix($conn, 'tbl_terbobot', 'nilai_v');
    $C = pivotPairMatrix($conn, 'tbl_concordance', 'nilai_concordance');
    $D = pivotPairMatrix($conn, 'tbl_discordance', 'nilai_discordance');
    $F = pivotPairMatrix($conn, 'tbl_dominan_concordance', 'nilai_f');
    $G = pivotPairMatrix($conn, 'tbl_dominan_discordance', 'nilai_g');
    $E = pivotPairMatrix($conn, 'tbl_agregat', 'nilai_e');

    $cBar = (float) ($conn->query("SELECT AVG(nilai_concordance) v FROM tbl_concordance")->fetch_assoc()['v'] ?? 0);
    $dBar = (float) ($conn->query("SELECT AVG(nilai_discordance) v FROM tbl_discordance")->fetch_assoc()['v'] ?? 0);

    $res = $conn->query("
        SELECT h.alternatif_id, h.phi, h.ranking, a.nama_daerah, a.provinsi
        FROM tbl_hasil h
        JOIN tbl_alternatif a ON a.id = h.alternatif_id
        ORDER BY h.ranking ASC
    ");
    while ($row = $res->fetch_assoc()) $hasil[] = $row;

    $himpunanCD = getSemuaHimpunanCD($conn);
}

function fmt($val, $dec = 4) {
    return number_format((float) $val, $dec, '.', '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perhitungan ELECTRE — SPK ELECTRE</title>
<link rel="shortcut icon" href="../assets/maple-leaf.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ===========================
   CSS VARIABLES & RESET
=========================== */
:root {
    --teal-light:  #91C6BC;
    --teal-mid:    #4B9DA9;
    --cream:       #F6F3C2;
    --orange:      #E37434;
    --text-dark:   #1f3a3f;
    --text-soft:   #6f8387;
    --glass-bg:    rgba(255,255,255,0.35);
    --glass-border:rgba(255,255,255,0.6);
    --glass-shadow:0 15px 40px rgba(75,157,169,0.12);
    --radius-lg:   28px;
    --radius-md:   16px;
    --radius-sm:   10px;
}

*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Outfit', sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fcfb 0%, #F6F3C2 50%, #eef8f6 100%);
    color: var(--text-dark);
}

/* ===========================
   LAYOUT
=========================== */
.wrapper { display: flex; }

.content {
    margin-left: 280px;
    padding: 40px;
    min-height: 100vh;
    flex: 1;
}

/* ===========================
   HERO
=========================== */
.hero {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 44px 48px;
    margin-bottom: 28px;
    box-shadow: var(--glass-shadow);
}

.hero-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--teal-mid);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.hero-label::before {
    content: '';
    display: inline-block;
    width: 24px;
    height: 2px;
    background: var(--teal-mid);
    border-radius: 2px;
}

.hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 56px;
    font-weight: 700;
    line-height: 1.05;
    color: var(--text-dark);
}

.hero h1 span { color: var(--orange); }

.hero-desc {
    margin-top: 14px;
    font-size: 15px;
    color: var(--text-soft);
    max-width: 680px;
    line-height: 1.6;
}

/* ===========================
   STATUS GRID
=========================== */
.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.status-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-md);
    box-shadow: var(--glass-shadow);
    padding: 20px 22px;
}

.status-card .label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--teal-mid);
    margin-bottom: 8px;
}

.status-card .value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    font-weight: 700;
    color: var(--text-dark);
}

.status-card .sub {
    margin-top: 4px;
    font-size: 12px;
    color: var(--text-soft);
}

/* ===========================
   HITUNG PANEL
=========================== */
.hitung-panel {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--glass-shadow);
    padding: 32px 36px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.hitung-panel-text h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 6px;
}

.hitung-panel-text p {
    font-size: 13px;
    color: var(--text-soft);
    max-width: 560px;
    line-height: 1.6;
}

.btn-hitung {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    border-radius: 14px;
    border: none;
    background: var(--orange);
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(227,116,52,0.35);
    transition: all 0.22s ease;
    white-space: nowrap;
}

.btn-hitung:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(227,116,52,0.45);
}

.btn-hitung:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ===========================
   EMPTY STATE
=========================== */
.empty-state {
    text-align: center;
    padding: 60px 30px;
    color: var(--text-soft);
}

.empty-state i {
    font-size: 44px;
    color: var(--teal-light);
    margin-bottom: 14px;
    display: block;
}

.empty-state p { font-size: 15px; }

.empty-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--glass-shadow);
}

/* ===========================
   ACCORDION
=========================== */
.accordion { display: flex; flex-direction: column; gap: 16px; }

.acc-item {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--glass-shadow);
    overflow: hidden;
}

.acc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 28px;
    cursor: pointer;
    user-select: none;
}

.acc-header-left { display: flex; align-items: center; gap: 14px; }

.acc-step {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(75,157,169,0.12);
    color: var(--teal-mid);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
}

.acc-title h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
}

.acc-title p {
    font-size: 12.5px;
    color: var(--text-soft);
    margin-top: 2px;
}

.acc-chevron {
    color: var(--teal-mid);
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.acc-item.open .acc-chevron { transform: rotate(180deg); }

.acc-body {
    display: none;
    padding: 0 28px 28px;
}

.acc-item.open .acc-body { display: block; }

/* ===========================
   MATRIX TABLE
=========================== */
.matrix-scroll {
    overflow-x: auto;
    border: 1px solid rgba(75,157,169,0.15);
    border-radius: var(--radius-md);
    background: rgba(255,255,255,0.45);
}

.matrix-scroll::-webkit-scrollbar { height: 8px; }
.matrix-scroll::-webkit-scrollbar-thumb {
    background: rgba(75,157,169,0.3);
    border-radius: 10px;
}

table.matrix {
    border-collapse: collapse;
    width: 100%;
    font-size: 12.5px;
    white-space: nowrap;
}

table.matrix th, table.matrix td {
    padding: 8px 12px;
    text-align: center;
    border-bottom: 1px solid rgba(75,157,169,0.08);
    border-right: 1px solid rgba(75,157,169,0.06);
}

table.matrix thead th {
    background: rgba(75,157,169,0.1);
    color: var(--teal-mid);
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    position: sticky;
    top: 0;
    z-index: 1;
}

table.matrix th.row-label,
table.matrix td.row-label {
    text-align: left;
    position: sticky;
    left: 0;
    background: rgba(246,243,194,0.55);
    font-weight: 600;
    color: var(--text-dark);
    z-index: 2;
    min-width: 170px;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
}

table.matrix thead th.row-label {
    background: rgba(75,157,169,0.16);
    z-index: 3;
}

table.matrix tbody tr:hover td:not(.row-label) {
    background: rgba(75,157,169,0.06);
}

table.matrix td.diag {
    color: var(--text-soft);
    background: rgba(75,157,169,0.04);
}

/* small badge for 0/1 in F, G, E matrices */
.bin-1, .bin-0 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}

.bin-1 { background: rgba(75,157,169,0.18); color: #2e7d81; }
.bin-0 { background: rgba(31,58,63,0.05); color: var(--text-soft); }

/* ===========================
   LEGEND
=========================== */
.legend-note {
    font-size: 12.5px;
    color: var(--text-soft);
    margin-bottom: 14px;
    line-height: 1.6;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(75,157,169,0.12);
    border-radius: var(--radius-sm);
    padding: 12px 16px;
}

.legend-note strong { color: var(--text-dark); }

.legend-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 6px 16px;
    margin-bottom: 18px;
    font-size: 12.5px;
}

.legend-grid .legend-item {
    display: flex;
    gap: 8px;
    padding: 4px 0;
    border-bottom: 1px dashed rgba(75,157,169,0.12);
}

.legend-grid .legend-num {
    font-weight: 700;
    color: var(--teal-mid);
    min-width: 28px;
}

.legend-grid .legend-name { color: var(--text-dark); }
.legend-grid .legend-prov { color: var(--text-soft); font-size: 11.5px; }

/* ===========================
   HIMPUNAN C/D — DUA KOLOM TABEL PENUH
=========================== */
.himpunan-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 900px) {
    .himpunan-cols { grid-template-columns: 1fr; }
}

.himpunan-col-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid;
}

.himpunan-col-title.c { color: #2e7d81; border-color: rgba(75,157,169,0.3); }
.himpunan-col-title.d { color: #b8501e; border-color: rgba(227,116,52,0.3); }

.himpunan-scroll {
    max-height: 420px;
    overflow-y: auto;
}

.himpunan-scroll::-webkit-scrollbar { width: 8px; }
.himpunan-scroll::-webkit-scrollbar-thumb {
    background: rgba(75,157,169,0.3);
    border-radius: 10px;
}

table.himpunan-set {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

table.himpunan-set thead th {
    position: sticky;
    top: 0;
    background: rgba(75,157,169,0.12);
    color: var(--teal-mid);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 10px 14px;
    text-align: left;
    z-index: 1;
}

table.himpunan-set tbody td {
    padding: 9px 14px;
    border-bottom: 1px solid rgba(75,157,169,0.08);
}

table.himpunan-set tbody tr:hover { background: rgba(255,255,255,0.4); }

td.pair-cell {
    font-weight: 700;
    color: var(--text-dark);
    white-space: nowrap;
    width: 90px;
}

.set-empty { color: var(--text-soft); font-style: italic; }

/* ===========================
   PAIR SELECTOR (HIMPUNAN C/D)
=========================== */
.pair-form {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.pair-field { display: flex; flex-direction: column; gap: 6px; min-width: 220px; flex: 1; }

.pair-field label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--teal-mid);
}

.pair-field select {
    appearance: none;
    -webkit-appearance: none;
    background: rgba(255,255,255,0.8) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%234B9DA9' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 14px center;
    border: 1px solid rgba(75,157,169,0.3);
    border-radius: 10px;
    padding: 11px 38px 11px 14px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    cursor: pointer;
    width: 100%;
}

.pair-field select:focus {
    outline: none;
    border-color: var(--teal-mid);
    box-shadow: 0 0 0 3px rgba(75,157,169,0.15);
}

.btn-lihat {
    padding: 11px 26px;
    border-radius: 10px;
    border: none;
    background: var(--teal-mid);
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(75,157,169,0.3);
    transition: all 0.22s;
    height: 44px;
}

.btn-lihat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(75,157,169,0.4); }

.himpunan-result { display: none; }
.himpunan-result.show { display: block; }

.himpunan-summary {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    font-size: 13px;
    color: var(--text-soft);
}

.himpunan-summary strong { color: var(--text-dark); }

table.himpunan {
    border-collapse: collapse;
    width: 100%;
    font-size: 13px;
}

table.himpunan th, table.himpunan td {
    padding: 10px 14px;
    text-align: left;
    border-bottom: 1px solid rgba(75,157,169,0.1);
}

table.himpunan thead th {
    background: rgba(75,157,169,0.08);
    color: var(--teal-mid);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

table.himpunan td.center, table.himpunan th.center { text-align: center; }

.badge-himpunan {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}

.badge-himpunan.c { background: rgba(75,157,169,0.14); color: #2e7d81; }
.badge-himpunan.d { background: rgba(227,116,52,0.14); color: #b8501e; }

/* ===========================
   PRIORITY CARDS (TOP 5)
=========================== */
.priority-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.priority-card {
    position: relative;
    background: rgba(255,255,255,0.55);
    border: 1px solid rgba(75,157,169,0.18);
    border-radius: var(--radius-md);
    padding: 22px 20px 20px;
    overflow: hidden;
}

.priority-card.rank-1 {
    border-color: var(--orange);
    background: linear-gradient(160deg, rgba(227,116,52,0.12), rgba(255,255,255,0.55));
}

.priority-rank {
    font-family: 'Cormorant Garamond', serif;
    font-size: 40px;
    font-weight: 700;
    color: var(--teal-mid);
    line-height: 1;
}

.priority-card.rank-1 .priority-rank { color: var(--orange); }

.priority-name {
    margin-top: 6px;
    font-weight: 600;
    font-size: 15px;
    color: var(--text-dark);
    line-height: 1.3;
}

.priority-prov {
    margin-top: 4px;
    font-size: 12px;
    color: var(--text-soft);
}

.priority-phi {
    margin-top: 12px;
    font-size: 12px;
    color: var(--teal-mid);
    font-weight: 700;
    letter-spacing: 0.05em;
}

/* ===========================
   FINAL RANKING TABLE
=========================== */
.table-card {
    background: rgba(255,255,255,0.45);
    border: 1px solid rgba(75,157,169,0.15);
    border-radius: var(--radius-md);
    overflow: hidden;
}

table.final { width: 100%; border-collapse: collapse; }

table.final thead th {
    background: rgba(75,157,169,0.1);
    color: var(--teal-mid);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 14px 18px;
    text-align: left;
}

table.final tbody td {
    padding: 14px 18px;
    font-size: 13.5px;
    border-bottom: 1px solid rgba(75,157,169,0.08);
}

table.final tbody tr:last-child td { border-bottom: none; }
table.final tbody tr:hover { background: rgba(255,255,255,0.4); }

.rank-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: rgba(75,157,169,0.12);
    color: var(--teal-mid);
    font-weight: 700;
    font-size: 13px;
}

.rank-pill.top { background: var(--orange); color: #fff; }

.badge-provinsi {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(145,198,188,0.2);
    color: var(--teal-mid);
    border-radius: 6px;
    padding: 5px 11px;
    font-size: 12px;
    font-weight: 600;
}
</style>
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <main class="content">

        <!-- HERO -->
        <div class="hero">
            <p class="hero-label">SPK ELECTRE I</p>
            <h1>Perhitungan <span>ELECTRE</span></h1>
            <p class="hero-desc">
                Jalankan seluruh tahapan ELECTRE I — normalisasi, pembobotan, concordance,
                discordance, matriks dominan, hingga matriks agregat — dalam satu kali proses.
                Hasilnya disimpan ke database dan dapat ditampilkan kembali kapan saja.
            </p>
        </div>

        <!-- STATUS -->
        <div class="status-grid">
            <div class="status-card">
                <div class="label">Total Alternatif</div>
                <div class="value"><?= $m ?></div>
                <div class="sub">Daerah Jatim &amp; Jateng</div>
            </div>
            <div class="status-card">
                <div class="label">Total Kriteria</div>
                <div class="value"><?= $n ?></div>
                <div class="sub">Kriteria aktif</div>
            </div>
            <div class="status-card">
                <div class="label">Threshold c̄</div>
                <div class="value"><?= $hasResult ? fmt($cBar, 3) : '—' ?></div>
                <div class="sub">Rata-rata concordance</div>
            </div>
            <div class="status-card">
                <div class="label">Threshold d̄</div>
                <div class="value"><?= $hasResult ? fmt($dBar, 3) : '—' ?></div>
                <div class="sub">Rata-rata discordance</div>
            </div>
        </div>

        <!-- PANEL HITUNG -->
        <div class="hitung-panel">
            <div class="hitung-panel-text">
                <h2>Jalankan Perhitungan ELECTRE</h2>
                <p>
                    <?php if ($hasResult): ?>
                        Hasil terakhir dihitung pada
                        <strong><?= htmlspecialchars(date('d M Y, H:i', strtotime($status['updated_at']))) ?></strong>.
                        Menekan tombol ini akan menghitung ulang seluruh tahapan dan menimpa hasil sebelumnya.
                    <?php else: ?>
                        Belum ada hasil perhitungan. Pastikan seluruh daerah sudah memiliki status
                        <strong>"Sudah Dinilai"</strong> pada halaman Data Nilai sebelum menekan tombol ini.
                    <?php endif; ?>
                </p>
            </div>
            <button class="btn-hitung" id="btnHitung" onclick="jalankanHitung()">
                <i class="fa-solid fa-calculator"></i>
                <?= $hasResult ? 'Hitung Ulang ELECTRE' : 'Hitung ELECTRE' ?>
            </button>
        </div>

        <?php if (!$hasResult): ?>

            <!-- EMPTY STATE -->
            <div class="empty-card">
                <div class="empty-state">
                    <i class="fa-regular fa-chart-bar"></i>
                    <p>
                        Belum ada hasil perhitungan ELECTRE.<br>
                        Klik tombol <strong>"Hitung ELECTRE"</strong> di atas untuk memulai.
                    </p>
                </div>
            </div>

        <?php else: ?>

        <!-- ACCORDION SELURUH TAHAPAN -->
        <div class="accordion">

            <!-- 1. MATRIKS KEPUTUSAN -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">1</div>
                        <div class="acc-title">
                            <h3>Matriks Keputusan</h3>
                            <p>Nilai mentah (skala 1–5) setiap alternatif terhadap setiap kriteria.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <div class="matrix-scroll">
                        <table class="matrix">
                            <thead>
                                <tr>
                                    <th class="row-label">Daerah</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th title="<?= htmlspecialchars($k['nama_kriteria']) ?>"><?= htmlspecialchars($k['kode']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatif as $alt): ?>
                                <tr>
                                    <td class="row-label" title="<?= htmlspecialchars($alt['nama_daerah'] . ' — ' . $alt['provinsi']) ?>">
                                        <?= htmlspecialchars($alt['nama_daerah']) ?>
                                    </td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td><?= fmt($X[$alt['id']][$k['id']] ?? 0, 0) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. NORMALISASI -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">2</div>
                        <div class="acc-title">
                            <h3>Matriks Normalisasi (R)</h3>
                            <p>r<sub>ij</sub> = x<sub>ij</sub> / √(Σ x<sub>ij</sub>²) — dihitung per kolom kriteria.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <div class="matrix-scroll">
                        <table class="matrix">
                            <thead>
                                <tr>
                                    <th class="row-label">Daerah</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th title="<?= htmlspecialchars($k['nama_kriteria']) ?>"><?= htmlspecialchars($k['kode']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatif as $alt): ?>
                                <tr>
                                    <td class="row-label" title="<?= htmlspecialchars($alt['nama_daerah'] . ' — ' . $alt['provinsi']) ?>">
                                        <?= htmlspecialchars($alt['nama_daerah']) ?>
                                    </td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td><?= fmt($R[$alt['id']][$k['id']] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. TERBOBOT -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">3</div>
                        <div class="acc-title">
                            <h3>Matriks Ternormalisasi Terbobot (V)</h3>
                            <p>v<sub>ij</sub> = r<sub>ij</sub> × w<sub>j</sub> — bobot setiap kriteria dikalikan ke matriks normalisasi.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <div class="matrix-scroll">
                        <table class="matrix">
                            <thead>
                                <tr>
                                    <th class="row-label">Daerah</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th title="<?= htmlspecialchars($k['nama_kriteria']) ?> (w=<?= fmt($k['bobot'],2) ?>)"><?= htmlspecialchars($k['kode']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatif as $alt): ?>
                                <tr>
                                    <td class="row-label" title="<?= htmlspecialchars($alt['nama_daerah'] . ' — ' . $alt['provinsi']) ?>">
                                        <?= htmlspecialchars($alt['nama_daerah']) ?>
                                    </td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td><?= fmt($V[$alt['id']][$k['id']] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. HIMPUNAN CONCORDANCE & DISCORDANCE (TABEL LENGKAP) -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">4</div>
                        <div class="acc-title">
                            <h3>Himpunan Concordance &amp; Discordance</h3>
                            <p>Seluruh pasangan (i,j) beserta himpunan kriteria yang masuk C dan D.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <div class="legend-note">
                        <strong>C(i,j)</strong>: himpunan kriteria di mana alternatif i "lebih baik atau sama"
                        dibanding alternatif j (benefit: V<sub>i</sub> ≥ V<sub>j</sub>, cost: V<sub>i</sub> ≤ V<sub>j</sub>).
                        <strong>D(i,j)</strong>: sisanya. i, j mengacu pada nomor urut pada legenda di bawah.
                    </div>

                    <?php renderLegend($alternatif, $altIndex); ?>

                    <div class="himpunan-cols">
                        <div class="himpunan-col">
                            <h4 class="himpunan-col-title c">Himpunan Concordance — C(i,j)</h4>
                            <div class="matrix-scroll himpunan-scroll">
                                <table class="himpunan-set">
                                    <thead>
                                        <tr><th>Pasangan</th><th>Himpunan C(i,j)</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($himpunanCD['concordance'] as $row): ?>
                                        <tr>
                                            <td class="pair-cell">C(<?= $row['i'] ?>,<?= $row['j'] ?>)</td>
                                            <td>
                                                <?php if (empty($row['kode_set'])): ?>
                                                    <span class="set-empty">{ }</span>
                                                <?php else: ?>
                                                    { <?= htmlspecialchars(implode(', ', $row['kode_set'])) ?> }
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="himpunan-col">
                            <h4 class="himpunan-col-title d">Himpunan Discordance — D(i,j)</h4>
                            <div class="matrix-scroll himpunan-scroll">
                                <table class="himpunan-set">
                                    <thead>
                                        <tr><th>Pasangan</th><th>Himpunan D(i,j)</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($himpunanCD['discordance'] as $row): ?>
                                        <tr>
                                            <td class="pair-cell">D(<?= $row['i'] ?>,<?= $row['j'] ?>)</td>
                                            <td>
                                                <?php if (empty($row['kode_set'])): ?>
                                                    <span class="set-empty">{ }</span>
                                                <?php else: ?>
                                                    { <?= htmlspecialchars(implode(', ', $row['kode_set'])) ?> }
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. MATRIKS CONCORDANCE -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">5</div>
                        <div class="acc-title">
                            <h3>Matriks Concordance (C)</h3>
                            <p>c(k,l) = Σ bobot kriteria pada himpunan C(k,l). Threshold c̄ = <?= fmt($cBar, 4) ?></p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <?php renderLegend($alternatif, $altIndex); ?>
                    <?php renderPairMatrix($alternatif, $altIndex, $C, 4, false); ?>
                </div>
            </div>

            <!-- 6. MATRIKS DISCORDANCE -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">6</div>
                        <div class="acc-title">
                            <h3>Matriks Discordance (D)</h3>
                            <p>d(k,l) = max selisih V pada himpunan D(k,l) dibagi max selisih V seluruh kriteria. Threshold d̄ = <?= fmt($dBar, 4) ?></p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <?php renderLegend($alternatif, $altIndex); ?>
                    <?php renderPairMatrix($alternatif, $altIndex, $D, 4, false); ?>
                </div>
            </div>

            <!-- 7. MATRIKS DOMINAN CONCORDANCE F -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">7</div>
                        <div class="acc-title">
                            <h3>Matriks Dominan Concordance (F)</h3>
                            <p>f(k,l) = 1 jika c(k,l) ≥ c̄ (<?= fmt($cBar, 4) ?>), selain itu 0.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <?php renderLegend($alternatif, $altIndex); ?>
                    <?php renderPairMatrix($alternatif, $altIndex, $F, 0, true); ?>
                </div>
            </div>

            <!-- 8. MATRIKS DOMINAN DISCORDANCE G -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">8</div>
                        <div class="acc-title">
                            <h3>Matriks Dominan Discordance (G)</h3>
                            <p>g(k,l) = 1 jika d(k,l) ≤ d̄ (<?= fmt($dBar, 4) ?>), selain itu 0.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <?php renderLegend($alternatif, $altIndex); ?>
                    <?php renderPairMatrix($alternatif, $altIndex, $G, 0, true); ?>
                </div>
            </div>

            <!-- 9. MATRIKS AGREGAT E -->
            <div class="acc-item">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">9</div>
                        <div class="acc-title">
                            <h3>Matriks Agregat Dominan (E)</h3>
                            <p>e(k,l) = f(k,l) × g(k,l). Digunakan untuk menghitung Nilai Phi.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">
                    <?php renderLegend($alternatif, $altIndex); ?>
                    <?php renderPairMatrix($alternatif, $altIndex, $E, 0, true); ?>
                </div>
            </div>

            <!-- 10. NILAI PHI & RANKING -->
            <div class="acc-item open">
                <div class="acc-header" onclick="toggleAcc(this)">
                    <div class="acc-header-left">
                        <div class="acc-step">10</div>
                        <div class="acc-title">
                            <h3>Nilai Phi (Φ) &amp; Ranking Prioritas</h3>
                            <p>Φ(k) = Σ e(k,l) − Σ e(l,k). Diurutkan dari nilai Φ terbesar ke terkecil.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down acc-chevron"></i>
                </div>
                <div class="acc-body">

                    <div class="priority-grid">
                        <?php foreach (array_slice($hasil, 0, 5) as $h): ?>
                            <div class="priority-card <?= $h['ranking'] == 1 ? 'rank-1' : '' ?>">
                                <div class="priority-rank">#<?= $h['ranking'] ?></div>
                                <div class="priority-name"><?= htmlspecialchars($h['nama_daerah']) ?></div>
                                <div class="priority-prov"><?= htmlspecialchars($h['provinsi']) ?></div>
                                <div class="priority-phi">Φ = <?= fmt($h['phi'], 4) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="table-card">
                        <table class="final">
                            <thead>
                                <tr>
                                    <th style="width:70px;">Rank</th>
                                    <th>Nama Daerah</th>
                                    <th>Provinsi</th>
                                    <th style="text-align:right;">Nilai Phi (Φ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hasil as $h): ?>
                                <tr>
                                    <td>
                                        <span class="rank-pill <?= $h['ranking'] <= 5 ? 'top' : '' ?>"><?= $h['ranking'] ?></span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($h['nama_daerah']) ?></strong></td>
                                    <td>
                                        <span class="badge-provinsi">
                                            <i class="fa-solid fa-map-pin" style="font-size:10px;"></i>
                                            <?= htmlspecialchars($h['provinsi']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;font-weight:600;"><?= fmt($h['phi'], 4) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
        <?php endif; ?>

    </main>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ===========================
   ACCORDION TOGGLE
=========================== */
function toggleAcc(headerEl) {
    headerEl.parentElement.classList.toggle('open');
}

/* ===========================
   JALANKAN PERHITUNGAN ELECTRE
=========================== */
function jalankanHitung() {
    Swal.fire({
        icon: 'warning',
        title: 'Hitung ELECTRE?',
        html: 'Proses ini akan menghitung ulang seluruh tahapan ELECTRE<br>dan <strong>menimpa hasil sebelumnya</strong>. Lanjutkan?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hitung',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#E37434',
        cancelButtonColor: '#91C6BC',
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('btnHitung');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menghitung…';

        const body = new FormData();
        body.append('ajax_hitung', '1');

        fetch('index.php', { method: 'POST', body: body })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        confirmButtonColor: '#E37434',
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message,
                        confirmButtonColor: '#E37434',
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Error',
                    text: 'Permintaan gagal. Periksa koneksi dan coba lagi.',
                    confirmButtonColor: '#E37434',
                });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });
}
</script>
</body>
</html>
<?php
/* =========================================================
   HELPER RENDER (di akhir file agar tidak mengganggu alur HTML)
========================================================= */

/**
 * Tampilkan legenda nomor -> nama daerah untuk matriks pasangan (m x m).
 */
function renderLegend(array $alternatif, array $altIndex): void
{
    echo '<div class="legend-note">';
    echo 'Baris &amp; kolom matriks di bawah menggunakan nomor indeks. Tanda "—" pada diagonal berarti pasangan (k,k) tidak dihitung.';
    echo '</div>';
    echo '<div class="legend-grid">';
    foreach ($alternatif as $alt) {
        $idx = $altIndex[(int) $alt['id']];
        echo '<div class="legend-item">';
        echo '<span class="legend-num">' . $idx . '.</span>';
        echo '<span><span class="legend-name">' . htmlspecialchars($alt['nama_daerah']) . '</span><br>';
        echo '<span class="legend-prov">' . htmlspecialchars($alt['provinsi']) . '</span></span>';
        echo '</div>';
    }
    echo '</div>';
}

/**
 * Tampilkan matriks pasangan m x m (Concordance, Discordance, F, G, E).
 *
 * @param array $matrix    [alternatif_i][alternatif_j] => value
 * @param int   $decimals  jumlah desimal untuk nilai non-biner
 * @param bool  $isBinary  true untuk matriks F/G/E (tampilkan sebagai badge 0/1)
 */
function renderPairMatrix(array $alternatif, array $altIndex, array $matrix, int $decimals, bool $isBinary): void
{
    echo '<div class="matrix-scroll">';
    echo '<table class="matrix">';

    // Header
    echo '<thead><tr><th class="row-label">Daerah</th>';
    foreach ($alternatif as $altJ) {
        echo '<th>' . $altIndex[(int) $altJ['id']] . '</th>';
    }
    echo '</tr></thead>';

    // Body
    echo '<tbody>';
    foreach ($alternatif as $altI) {
        $i = (int) $altI['id'];
        echo '<tr>';
        echo '<td class="row-label" title="' . htmlspecialchars($altI['nama_daerah'] . ' — ' . $altI['provinsi']) . '">';
        echo $altIndex[$i] . '. ' . htmlspecialchars($altI['nama_daerah']);
        echo '</td>';

        foreach ($alternatif as $altJ) {
            $j = (int) $altJ['id'];
            if ($i === $j) {
                echo '<td class="diag">—</td>';
                continue;
            }

            $val = $matrix[$i][$j] ?? 0;

            if ($isBinary) {
                $cls = ((int) $val === 1) ? 'bin-1' : 'bin-0';
                echo '<td><span class="' . $cls . '">' . (int) $val . '</span></td>';
            } else {
                echo '<td>' . number_format((float) $val, $decimals, '.', '') . '</td>';
            }
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
