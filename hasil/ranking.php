<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';
require_once '../includes/electre_engine.php';

/* =========================
   AJAX: BREAKDOWN BUKTI PER KRITERIA
========================= */
if (isset($_GET['ajax_breakdown']) && $_GET['ajax_breakdown'] === '1') {
    header('Content-Type: application/json');
    $altId = intval($_GET['id'] ?? 0);
    try {
        $data = getKriteriaBreakdown($conn, $altId);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

/* =========================
   AMBIL DATA UTAMA
========================= */
$status    = getElectreStatus($conn);
$hasResult = $status['has_result'];
$ranking   = $hasResult ? getRankingWithKategori($conn) : [];

// Ringkasan jumlah per kategori (dihitung dari seluruh data, sebelum difilter provinsi -- filter dilakukan di JS)
$jumlahKategori = ['utama' => 0, 'tinggi' => 0, 'sedang' => 0, 'cukup' => 0];
foreach ($ranking as $r) {
    $jumlahKategori[$r['kategori']]++;
}

$pageTitle    = 'Ranking Prioritas';
$pageSubtitle = 'Nilai Phi (Φ) per Provinsi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ranking Prioritas — SPK ELECTRE</title>
<link rel="shortcut icon" href="../assets/maple-leaf.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --teal-light:#91C6BC; --teal-mid:#4B9DA9; --cream:#F6F3C2; --orange:#E37434;
    --merah:#D9342B; --kuning:#D4A017;
    --text-dark:#1f3a3f; --text-soft:#6f8387;
    --glass-bg: rgba(255,255,255,0.35); --glass-border: rgba(255,255,255,0.6);
    --glass-shadow: 0 15px 40px rgba(75,157,169,0.12);
}
*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'Outfit',sans-serif; min-height:100vh; color:var(--text-dark);
    background: linear-gradient(135deg, #f8fcfb 0%, #F6F3C2 50%, #eef8f6 100%);
}
.wrapper { display:flex; }
.content { margin-left:280px; padding:40px; min-height:100vh; flex:1; }

.hero {
    background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border:1px solid var(--glass-border); border-radius:28px; padding:44px 48px;
    margin-bottom:28px; box-shadow: var(--glass-shadow);
}
.hero-label {
    display:inline-flex; align-items:center; gap:8px; color:var(--teal-mid);
    font-size:11px; font-weight:700; letter-spacing:.18em; text-transform:uppercase; margin-bottom:14px;
}
.hero-label::before { content:''; width:24px; height:2px; background:var(--teal-mid); border-radius:2px; }
.hero h1 { font-family:'Cormorant Garamond',serif; font-size:52px; font-weight:700; line-height:1.05; }
.hero h1 span { color:var(--orange); }
.hero-desc { margin-top:14px; font-size:15px; color:var(--text-soft); max-width:680px; line-height:1.6; }

/* ===== KATEGORI STAT CARDS ===== */
.kategori-grid {
    display:grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap:16px; margin-bottom:24px;
}
.kategori-card {
    background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border:1px solid var(--glass-border); border-radius:18px; box-shadow: var(--glass-shadow);
    padding:20px 22px; position:relative; overflow:hidden; cursor:pointer; transition:.2s;
}
.kategori-card:hover { transform:translateY(-3px); }
.kategori-card.active { outline:2px solid var(--text-dark); outline-offset:-2px; }
.kategori-card::before {
    content:''; position:absolute; top:0; left:0; width:6px; height:100%;
}
.kategori-card.utama::before  { background:var(--merah); }
.kategori-card.tinggi::before { background:var(--orange); }
.kategori-card.sedang::before { background:var(--kuning); }
.kategori-card.cukup::before  { background:var(--teal-mid); }

.kategori-card .label { font-size:11.5px; font-weight:700; color:var(--text-soft); margin-bottom:6px; }
.kategori-card .value { font-family:'Cormorant Garamond',serif; font-size:36px; font-weight:700; }
.kategori-card.utama .value  { color:var(--merah); }
.kategori-card.tinggi .value { color:var(--orange); }
.kategori-card.sedang .value { color:var(--kuning); }
.kategori-card.cukup .value  { color:var(--teal-mid); }
.kategori-card .sub { font-size:11px; color:var(--text-soft); margin-top:4px; }

/* ===== FILTER BAR ===== */
.filter-bar {
    display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;
}
.filter-chip {
    padding:9px 20px; border-radius:30px; border:1px solid rgba(75,157,169,.25);
    background: rgba(255,255,255,.5); font-size:13px; font-weight:600; color:var(--text-soft);
    cursor:pointer; transition:.2s; user-select:none;
}
.filter-chip:hover { background: rgba(255,255,255,.8); }
.filter-chip.active { background: var(--teal-mid); color:#fff; border-color: var(--teal-mid); }
.filter-spacer { flex:1; }
.search-box {
    display:flex; align-items:center; gap:8px; background:rgba(255,255,255,.6);
    border:1px solid rgba(75,157,169,.2); border-radius:30px; padding:9px 18px; min-width:220px;
}
.search-box i { color:var(--teal-mid); font-size:13px; }
.search-box input {
    border:none; background:transparent; outline:none; font-family:'Outfit',sans-serif;
    font-size:13px; color:var(--text-dark); width:100%;
}

/* ===== DIAGRAM RINGKASAN ===== */
.diagram-card {
    background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border:1px solid var(--glass-border); border-radius:28px; box-shadow: var(--glass-shadow);
    padding:32px 36px; margin-bottom:28px;
}
.diagram-card h2 {
    font-family:'Cormorant Garamond',serif; font-size:24px; font-weight:700; margin-bottom:4px;
}
.diagram-card .diagram-desc { font-size:12.5px; color:var(--text-soft); margin-bottom:20px; }
.diagram-empty { text-align:center; padding:30px; color:var(--text-soft); font-size:13.5px; }
#diagramWrap { position:relative; }

/* ===== TABLE CARD ===== */
.table-card {
    background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border:1px solid var(--glass-border); border-radius:28px; box-shadow: var(--glass-shadow);
    overflow:hidden;
}
.table-scroll { overflow-x:auto; }
table.ranking { width:100%; border-collapse:collapse; }
table.ranking thead th {
    background: rgba(75,157,169,.12); color:var(--teal-mid); font-size:11px; font-weight:700;
    letter-spacing:.08em; text-transform:uppercase; padding:16px 18px; text-align:left; white-space:nowrap;
}
table.ranking tbody td { padding:14px 18px; font-size:13.5px; border-bottom:1px solid rgba(75,157,169,.08); }
table.ranking tbody tr:hover { background: rgba(255,255,255,.4); }
table.ranking tbody tr.hidden-row { display:none; }

.rank-pill {
    display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px;
    border-radius:9px; background: rgba(75,157,169,.12); color:var(--teal-mid); font-weight:700; font-size:13px;
}
.badge-provinsi {
    display:inline-flex; align-items:center; gap:5px; background: rgba(145,198,188,.2);
    color:var(--teal-mid); border-radius:6px; padding:5px 11px; font-size:12px; font-weight:600;
}
.badge-kategori {
    display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px;
    font-size:11.5px; font-weight:700; white-space:nowrap;
}
.badge-kategori .dot { width:7px; height:7px; border-radius:50%; }

.btn-detail {
    display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:9px;
    border:1px solid rgba(75,157,169,.3); background:rgba(255,255,255,.6); color:var(--teal-mid);
    font-family:'Outfit',sans-serif; font-size:12px; font-weight:700; cursor:pointer; transition:.2s;
}
.btn-detail:hover { background:var(--teal-mid); color:#fff; }

.no-result-row td { text-align:center; padding:40px; color:var(--text-soft); }

/* ===== MODAL DETAIL ===== */
.modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(31,58,63,.45);
    backdrop-filter: blur(4px); z-index:1000; align-items:center; justify-content:center; padding:20px;
}
.modal-overlay.show { display:flex; }
.modal-box {
    background: rgba(255,255,255,.92); backdrop-filter: blur(24px);
    border:1px solid rgba(255,255,255,.8); border-radius:28px; box-shadow:0 30px 80px rgba(31,58,63,.25);
    max-width:640px; width:100%; max-height:88vh; overflow-y:auto; padding:36px;
}
.modal-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:6px; }
.modal-head h3 { font-family:'Cormorant Garamond',serif; font-size:26px; font-weight:700; }
.modal-close {
    width:34px; height:34px; border-radius:10px; border:none; background:rgba(75,157,169,.1);
    color:var(--teal-mid); font-size:15px; cursor:pointer; flex-shrink:0;
}
.modal-sub { font-size:12.5px; color:var(--text-soft); margin-bottom:22px; }
.modal-chart-wrap { max-width:380px; margin:0 auto 24px; }

table.breakdown { width:100%; border-collapse:collapse; font-size:13px; }
table.breakdown th {
    background: rgba(75,157,169,.1); color:var(--teal-mid); font-size:10.5px; text-transform:uppercase;
    letter-spacing:.05em; padding:9px 12px; text-align:left;
}
table.breakdown td { padding:10px 12px; border-bottom:1px solid rgba(75,157,169,.08); }
table.breakdown td.center { text-align:center; }
.status-pill {
    display:inline-flex; align-items:center; gap:5px; padding:4px 11px; border-radius:16px;
    font-size:11px; font-weight:700;
}
.status-pill.unggul { background: rgba(75,157,169,.15); color:#2e7d81; }
.status-pill.kurang { background: rgba(227,52,52,.12); color:#b8341e; }
</style>
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <main class="content">

        <div class="hero">
            <p class="hero-label">SPK ELECTRE I</p>
            <h1>Ranking <span>Prioritas</span></h1>
            <p class="hero-desc">
                Daftar prioritas daerah berdasarkan Nilai Phi (Φ), dikelompokkan ke dalam 4 tingkat
                kepentingan. Top 5 selalu menjadi Prioritas Utama; sisanya dikelompokkan berdasarkan
                rentang nilai Phi.
            </p>
        </div>

        <?php if (!$hasResult): ?>
            <div class="table-card">
                <div class="no-result-row" style="padding:60px;">
                    <i class="fa-regular fa-chart-bar" style="font-size:40px;color:var(--teal-light);display:block;margin-bottom:14px;"></i>
                    Belum ada hasil perhitungan. Jalankan perhitungan ELECTRE terlebih dahulu.
                </div>
            </div>
        <?php else: ?>

        <!-- KATEGORI STAT CARDS (sekaligus filter kategori) -->
        <div class="kategori-grid">
            <div class="kategori-card utama active" data-kategori="all">
                <div class="label">SEMUA KATEGORI</div>
                <div class="value"><?= count($ranking) ?></div>
                <div class="sub">Total daerah dinilai</div>
            </div>
            <div class="kategori-card utama" data-kategori="utama">
                <div class="label">PRIORITAS UTAMA</div>
                <div class="value"><?= $jumlahKategori['utama'] ?></div>
                <div class="sub">Top 5 nilai Φ tertinggi</div>
            </div>
            <div class="kategori-card tinggi" data-kategori="tinggi">
                <div class="label">PRIORITAS TINGGI</div>
                <div class="value"><?= $jumlahKategori['tinggi'] ?></div>
                <div class="sub">1/3 teratas interval Φ</div>
            </div>
            <div class="kategori-card sedang" data-kategori="sedang">
                <div class="label">PRIORITAS SEDANG</div>
                <div class="value"><?= $jumlahKategori['sedang'] ?></div>
                <div class="sub">1/3 tengah interval Φ</div>
            </div>
            <div class="kategori-card cukup" data-kategori="cukup">
                <div class="label">CUKUP BAIK</div>
                <div class="value"><?= $jumlahKategori['cukup'] ?></div>
                <div class="sub">1/3 bawah interval Φ</div>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-bar">
            <div class="filter-chip active" data-provinsi="all">Semua Provinsi</div>
            <div class="filter-chip" data-provinsi="Jawa Timur">Jawa Timur</div>
            <div class="filter-chip" data-provinsi="Jawa Tengah">Jawa Tengah</div>
            <div class="filter-spacer"></div>
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Cari nama daerah...">
            </div>
        </div>

        <!-- DIAGRAM RINGKASAN: Prioritas Utama + Tinggi saja -->
        <div class="diagram-card">
            <h2>Diagram Sorotan Prioritas</h2>
            <p class="diagram-desc">
                Menampilkan daerah kategori <strong style="color:var(--merah)">Prioritas Utama</strong> dan
                <strong style="color:var(--orange)">Prioritas Tinggi</strong> saja — kategori Sedang &amp; Cukup Baik
                tetap lengkap di tabel di bawah, tidak dihilangkan dari data, hanya tidak disorot di sini.
            </p>
            <div id="diagramWrap">
                <canvas id="diagramChart"></canvas>
            </div>
            <div class="diagram-empty" id="diagramEmpty" style="display:none;">
                Tidak ada daerah Prioritas Utama/Tinggi pada filter provinsi saat ini.
            </div>
        </div>

        <!-- TABEL RANKING LENGKAP -->
        <div class="table-card">
            <div class="table-scroll">
                <table class="ranking" id="rankingTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Nama Daerah</th>
                            <th>Provinsi</th>
                            <th>Nilai Φ</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $r): ?>
                        <tr
                            class="data-row"
                            data-provinsi="<?= htmlspecialchars($r['provinsi']) ?>"
                            data-kategori="<?= $r['kategori'] ?>"
                            data-nama="<?= htmlspecialchars(strtolower($r['nama_daerah'])) ?>"
                        >
                            <td><span class="rank-pill">#<?= $r['ranking'] ?></span></td>
                            <td><strong><?= htmlspecialchars($r['nama_daerah']) ?></strong></td>
                            <td>
                                <span class="badge-provinsi">
                                    <i class="fa-solid fa-map-pin" style="font-size:10px;"></i>
                                    <?= htmlspecialchars($r['provinsi']) ?>
                                </span>
                            </td>
                            <td style="font-weight:600;"><?= number_format($r['phi'], 4) ?></td>
                            <td>
                                <span class="badge-kategori" style="background:<?= $r['kategori_bg'] ?>;color:<?= $r['kategori_color'] ?>;">
                                    <span class="dot" style="background:<?= $r['kategori_color'] ?>;"></span>
                                    <?= htmlspecialchars($r['kategori_label']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-detail" onclick="bukaDetail(<?= $r['alternatif_id'] ?>, '<?= htmlspecialchars(addslashes($r['nama_daerah'])) ?>', '<?= htmlspecialchars(addslashes($r['kategori_label'])) ?>', <?= $r['ranking'] ?>, <?= json_encode($r['phi']) ?>)">
                                    <i class="fa-solid fa-chart-simple"></i> Detail
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noResultMsg" style="display:none;">
                            <td colspan="6" style="text-align:center;padding:40px;color:var(--text-soft);">
                                <i class="fa-solid fa-filter" style="display:block;font-size:28px;color:var(--teal-light);margin-bottom:10px;"></i>
                                Tidak ada daerah yang cocok dengan filter/pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>

    </main>
</div>

<!-- MODAL DETAIL BREAKDOWN -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h3 id="modalNamaDaerah">—</h3>
                <p class="modal-sub" id="modalSubInfo">—</p>
            </div>
            <button class="modal-close" onclick="tutupDetail()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-chart-wrap">
            <canvas id="breakdownChart"></canvas>
        </div>

        <table class="breakdown">
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <th class="center">Tipe</th>
                    <th class="center">Nilai Daerah</th>
                    <th class="center">Rata-rata</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody id="breakdownBody"></tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const SKALA_LABEL = {1:'Sangat Rendah', 2:'Rendah', 3:'Sedang', 4:'Tinggi', 5:'Sangat Tinggi'};

/* ===========================
   DATA RANKING (dari PHP)
=========================== */
const rankingData = <?= json_encode($ranking) ?>;

let diagramChart = null;
let breakdownChartInstance = null;

/* ===========================
   STATE FILTER
=========================== */
let currentProvinsi = 'all';
let currentKategori = 'all';
let currentSearch   = '';

function applyFilters() {
    let visibleCount = 0;

    document.querySelectorAll('#rankingTable tbody tr.data-row').forEach(row => {
        const matchProvinsi = currentProvinsi === 'all' || row.dataset.provinsi === currentProvinsi;
        const matchKategori = currentKategori === 'all' || row.dataset.kategori === currentKategori;
        const matchSearch   = currentSearch === '' || row.dataset.nama.includes(currentSearch);

        const visible = matchProvinsi && matchKategori && matchSearch;
        row.classList.toggle('hidden-row', !visible);
        if (visible) visibleCount++;
    });

    document.getElementById('noResultMsg').style.display = visibleCount === 0 ? '' : 'none';

    renderDiagram();
}

document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        currentProvinsi = chip.dataset.provinsi;
        applyFilters();
    });
});

document.querySelectorAll('.kategori-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.kategori-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        currentKategori = card.dataset.kategori;
        applyFilters();
    });
});

const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        currentSearch = e.target.value.trim().toLowerCase();
        applyFilters();
    });
}

/* ===========================
   DIAGRAM RINGKASAN (Utama + Tinggi saja, ikut filter provinsi & search)
=========================== */
function renderDiagram() {
    const canvas = document.getElementById('diagramChart');
    const wrap   = document.getElementById('diagramWrap');
    const empty  = document.getElementById('diagramEmpty');
    if (!canvas || !wrap) return;

    const filtered = rankingData.filter(r => {
        const matchProvinsi = currentProvinsi === 'all' || r.provinsi === currentProvinsi;
        const matchSearch   = currentSearch === '' || r.nama_daerah.toLowerCase().includes(currentSearch);
        const matchKategoriDiagram = r.kategori === 'utama' || r.kategori === 'tinggi';
        return matchProvinsi && matchSearch && matchKategoriDiagram;
    }).sort((a, b) => b.phi - a.phi);

    if (filtered.length === 0) {
        wrap.style.display = 'none';
        empty.style.display = 'block';
        if (diagramChart) { diagramChart.destroy(); diagramChart = null; }
        return;
    }

    wrap.style.display = 'block';
    empty.style.display = 'none';

    // Destroy dulu SEBELUM ubah ukuran container
    if (diagramChart) { diagramChart.destroy(); diagramChart = null; }

    // Set tinggi container setelah destroy, baru buat chart baru
    wrap.style.height = Math.max(160, filtered.length * 36) + 'px';

    diagramChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: filtered.map(r => r.nama_daerah),
            datasets: [{
                data: filtered.map(r => r.phi),
                backgroundColor: filtered.map(r => r.kategori_color),
                borderRadius: 6,
                barThickness: 18,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `Φ = ${ctx.parsed.x.toFixed(4)}`
                    }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(75,157,169,0.08)' }, ticks: { font: { family: 'Outfit', size: 11 } } },
                y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11.5 } } }
            }
        }
    });
}

/* ===========================
   MODAL DETAIL: BREAKDOWN PER KRITERIA
=========================== */
function bukaDetail(altId, namaDaerah, kategoriLabel, ranking, phi) {
    document.getElementById('modalNamaDaerah').textContent = namaDaerah;
    document.getElementById('modalSubInfo').textContent =
        `Rank #${ranking} — ${kategoriLabel} — Φ = ${Number(phi).toFixed(4)}`;
    document.getElementById('breakdownBody').innerHTML =
        '<tr><td colspan="5" style="text-align:center;color:var(--text-soft);padding:24px;">Memuat...</td></tr>';
    document.getElementById('modalOverlay').classList.add('show');

    fetch(`ranking.php?ajax_breakdown=1&id=${altId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#E37434' });
                tutupDetail();
                return;
            }
            renderBreakdownTable(res.data);
            renderBreakdownChart(res.data);
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Koneksi Error', text: 'Gagal memuat data breakdown.', confirmButtonColor: '#E37434' });
            tutupDetail();
        });
}

function tutupDetail() {
    document.getElementById('modalOverlay').classList.remove('show');
}

function renderBreakdownTable(data) {
    const tbody = document.getElementById('breakdownBody');
    tbody.innerHTML = '';
    data.forEach(row => {
        const isUnggul = row.status === 'unggul';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${row.kode}</strong> — ${row.nama_kriteria}</td>
            <td class="center" style="text-transform:capitalize;">${row.tipe}</td>
            <td class="center">${SKALA_LABEL[row.nilai] || row.nilai}</td>
            <td class="center">${Number(row.rata_rata).toFixed(2)}</td>
            <td class="center">
                <span class="status-pill ${isUnggul ? 'unggul' : 'kurang'}">
                    <i class="fa-solid ${isUnggul ? 'fa-arrow-up' : 'fa-arrow-down'}"></i>
                    ${isUnggul ? 'Unggul' : 'Kurang'}
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderBreakdownChart(data) {
    const canvas = document.getElementById('breakdownChart');
    if (breakdownChartInstance) breakdownChartInstance.destroy();

    breakdownChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'radar',
        data: {
            labels: data.map(d => d.kode),
            datasets: [
                {
                    label: 'Daerah Ini',
                    data: data.map(d => d.nilai),
                    borderColor: '#E37434',
                    backgroundColor: 'rgba(227,116,52,0.18)',
                    borderWidth: 2,
                    pointBackgroundColor: '#E37434',
                },
                {
                    label: 'Rata-rata Seluruh Daerah',
                    data: data.map(d => d.rata_rata),
                    borderColor: '#4B9DA9',
                    backgroundColor: 'rgba(75,157,169,0.12)',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: '#4B9DA9',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0, max: 5, ticks: { stepSize: 1, font: { size: 9 } },
                    pointLabels: { font: { family: 'Outfit', size: 12, weight: '600' } },
                    grid: { color: 'rgba(75,157,169,0.15)' },
                }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Outfit', size: 11 }, boxWidth: 12 } }
            }
        }
    });
}

document.getElementById('modalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'modalOverlay') tutupDetail();
});

/* ===========================
   INIT
=========================== */
renderDiagram();
</script>
</body>
</html>
