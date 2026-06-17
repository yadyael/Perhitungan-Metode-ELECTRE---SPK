<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';

/* =========================
   PROSES SIMPAN NILAI (AJAX)
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['ajax_save']) &&
    $_POST['ajax_save'] === '1'
) {
    header('Content-Type: application/json');
    $alternatif_id = intval($_POST['alternatif_id'] ?? 0);
    $nilai_list    = $_POST['nilai'] ?? [];

    if (!$alternatif_id || empty($nilai_list)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
        exit();
    }

    $errors = 0;
    foreach ($nilai_list as $kriteria_id => $nilai) {
        $kriteria_id = intval($kriteria_id);
        $nilai       = floatval($nilai);

        if ($nilai < 1 || $nilai > 5) {
            $errors++;
            continue;
        }

        $cek = $conn->prepare("SELECT id FROM tbl_nilai WHERE alternatif_id=? AND kriteria_id=?");
        $cek->bind_param("ii", $alternatif_id, $kriteria_id);
        $cek->execute();
        $result = $cek->get_result();

        if ($result->num_rows > 0) {
            $upd = $conn->prepare("UPDATE tbl_nilai SET nilai=? WHERE alternatif_id=? AND kriteria_id=?");
            $upd->bind_param("dii", $nilai, $alternatif_id, $kriteria_id);
            if (!$upd->execute()) $errors++;
        } else {
            $ins = $conn->prepare("INSERT INTO tbl_nilai (alternatif_id, kriteria_id, nilai) VALUES (?,?,?)");
            $ins->bind_param("iid", $alternatif_id, $kriteria_id, $nilai);
            if (!$ins->execute()) $errors++;
        }
    }

    if ($errors > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan sebagian data.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Nilai berhasil disimpan.']);
    }
    exit();
}

/* =========================
   AMBIL DATA UNTUK MODAL (AJAX)
========================= */
if (isset($_GET['get_nilai']) && $_GET['get_nilai'] === '1') {
    header('Content-Type: application/json');
    $alternatif_id = intval($_GET['alternatif_id'] ?? 0);

    $kriteria_list = [];
    $krit = $conn->query("SELECT id, kode, nama_kriteria FROM tbl_kriteria ORDER BY kode");
    while ($k = $krit->fetch_assoc()) {
        $st = $conn->prepare("SELECT nilai FROM tbl_nilai WHERE alternatif_id=? AND kriteria_id=?");
        $st->bind_param("ii", $alternatif_id, $k['id']);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $k['nilai'] = $row ? intval($row['nilai']) : 0;
        $kriteria_list[] = $k;
    }

    echo json_encode($kriteria_list);
    exit();
}

/* =========================
   AMBIL DATA UTAMA
========================= */
$provinsi   = $_GET['provinsi'] ?? 'Jawa Timur';
$alternatifs = $conn->query("
    SELECT * FROM tbl_alternatif
    WHERE provinsi='$provinsi'
    ORDER BY nama_daerah
");

$total_kriteria = $conn->query("SELECT COUNT(*) as total FROM tbl_kriteria")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Nilai — SPK ELECTRE</title>
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
.wrapper {
    display: flex;
}

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
    border-radius:28px;
    padding:40px;
    margin-bottom:30px;
    box-shadow: var(--glass-shadow);
}

.hero-label {
    display: inline-flex;
    align-items: center;
    color: var(--teal-mid);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .15em;
    text-transform: uppercase;
    margin-bottom: 12px;
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
    font-size: 52px;
    line-height: 1.05;
    max-width:850px;
}

.hero h1 span {
    color: var(--orange);
}

.hero-desc {
    margin-top:20px;
    max-width:750px;
}

/* ===========================
   TOOLBAR
=========================== */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-soft);
}

.provinsi-tabs {
    display: flex;
    background: rgba(255,255,255,0.5);
    border: 1px solid var(--glass-border);
    border-radius: 50px;
    padding: 4px;
    gap: 4px;
}

.provinsi-tabs a {
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-soft);
    transition: all 0.25s ease;
}

.provinsi-tabs a.active {
    background: var(--teal-mid);
    color: #fff;
    box-shadow: 0 4px 12px rgba(75,157,169,0.35);
}

.provinsi-tabs a:not(.active):hover {
    background: rgba(75,157,169,0.1);
    color: var(--teal-mid);
}

.stats-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.5);
    border: 1px solid var(--glass-border);
    border-radius: 50px;
    padding: 8px 18px;
    font-size: 13px;
    color: var(--text-soft);
}

.stats-pill strong {
    color: var(--text-dark);
}

/* ===========================
   TABLE CARD
=========================== */
.table-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 0;
    box-shadow: var(--glass-shadow);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: rgba(75,157,169,0.08);
}

thead th {
    padding: 18px 22px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--teal-mid);
    border-bottom: 1px solid rgba(75,157,169,0.15);
}

thead th:last-child {
    text-align: center;
}

tbody tr {
    border-bottom: 1px solid rgba(75,157,169,0.08);
    transition: background 0.2s ease;
}

tbody tr:last-child {
    border-bottom: none;
}

tbody tr:hover {
    background: rgba(255,255,255,0.4);
}

tbody td {
    padding: 18px 22px;
    font-size: 14px;
    vertical-align: middle;
}

.daerah-name {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 15px;
}

.daerah-id {
    font-size: 12px;
    color: var(--text-soft);
    margin-top: 2px;
}

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

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
}

.status-done {
    background: rgba(75,157,169,0.12);
    color: #2e7d81;
}

.status-done::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4B9DA9;
    flex-shrink: 0;
}

.status-pending {
    background: rgba(227,116,52,0.12);
    color: #b8501e;
}

.status-pending::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #E37434;
    flex-shrink: 0;
}

td.actions {
    text-align: center;
}

.btn-input {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    border: none;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.22s ease;
}

.btn-input-primary {
    background: var(--orange);
    color: #fff;
    box-shadow: 0 4px 14px rgba(227,116,52,0.3);
}

.btn-input-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(227,116,52,0.4);
}

.btn-input-secondary {
    background: rgba(75,157,169,0.12);
    color: var(--teal-mid);
    border: 1px solid rgba(75,157,169,0.3);
}

.btn-input-secondary:hover {
    background: rgba(75,157,169,0.2);
    transform: translateY(-2px);
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
    font-size: 40px;
    color: var(--teal-light);
    margin-bottom: 14px;
    display: block;
}

.empty-state p {
    font-size: 15px;
}

/* ===========================
   MODAL OVERLAY
=========================== */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(31, 58, 63, 0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-overlay.open {
    display: flex;
}

/* ===========================
   MODAL BOX
=========================== */
.modal-box {
    background: rgba(248, 252, 251, 0.82);
    backdrop-filter: blur(32px);
    -webkit-backdrop-filter: blur(32px);
    border: 1px solid rgba(255,255,255,0.75);
    border-radius: var(--radius-lg);
    box-shadow: 0 30px 80px rgba(31,58,63,0.25), 0 0 0 1px rgba(75,157,169,0.1);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
}

@keyframes modalIn {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1); }
}

.modal-header {
    padding: 28px 32px 20px;
    border-bottom: 1px solid rgba(75,157,169,0.12);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    position: sticky;
    top: 0;
    background: rgba(248,252,251,0.9);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    z-index: 2;
}

.modal-header-left .modal-eyebrow {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: var(--teal-mid);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.1;
}

.modal-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: var(--text-soft);
}

.modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(75,157,169,0.25);
    background: rgba(255,255,255,0.6);
    color: var(--text-soft);
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.modal-close:hover {
    background: var(--orange);
    color: #fff;
    border-color: var(--orange);
    transform: rotate(90deg);
}

/* ===========================
   MODAL BODY
=========================== */
.modal-body {
    padding: 24px 32px;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: var(--text-soft);
}

.loading-spinner i {
    font-size: 28px;
    color: var(--teal-mid);
    animation: spin 0.8s linear infinite;
    display: block;
    margin-bottom: 12px;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

/* ===========================
   KRITERIA ROW
=========================== */
.kriteria-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.kriteria-row {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(75,157,169,0.15);
    border-radius: var(--radius-md);
    padding: 14px 18px;
    transition: border-color 0.2s;
}

.kriteria-row:hover {
    border-color: rgba(75,157,169,0.35);
}

.kriteria-kode {
    min-width: 80px;
    font-weight: 700;
    font-size: 13px;
    color: var(--teal-mid);
    background: rgba(75,157,169,0.1);
    padding: 5px 10px;
    border-radius: 7px;
    text-align: center;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

.kriteria-nama {
    flex: 1;
    font-size: 14px;
    color: var(--text-dark);
    font-weight: 500;
}

.nilai-select {
    appearance: none;
    -webkit-appearance: none;
    background: rgba(255,255,255,0.8) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%234B9DA9' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 12px center;
    border: 1px solid rgba(75,157,169,0.3);
    border-radius: 10px;
    padding: 9px 36px 9px 14px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    cursor: pointer;
    min-width: 150px;
    transition: all 0.2s;
}

.nilai-select:focus {
    outline: none;
    border-color: var(--teal-mid);
    box-shadow: 0 0 0 3px rgba(75,157,169,0.15);
}

.nilai-select option[value="0"] {
    color: var(--text-soft);
}

/* ===========================
   MODAL FOOTER
=========================== */
.modal-footer {
    padding: 20px 32px 28px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    border-top: 1px solid rgba(75,157,169,0.1);
}

.btn-cancel {
    padding: 11px 22px;
    border-radius: 10px;
    border: 1px solid rgba(75,157,169,0.25);
    background: transparent;
    color: var(--text-soft);
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: rgba(75,157,169,0.08);
    color: var(--text-dark);
}

.btn-submit {
    padding: 11px 26px;
    border-radius: 10px;
    border: none;
    background: var(--orange);
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(227,116,52,0.3);
    transition: all 0.22s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(227,116,52,0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ===========================
   SCROLLBAR
=========================== */
.modal-box::-webkit-scrollbar {
    width: 5px;
}
.modal-box::-webkit-scrollbar-track {
    background: transparent;
}
.modal-box::-webkit-scrollbar-thumb {
    background: rgba(75,157,169,0.3);
    border-radius: 10px;
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
            <h1>Data <span>Nilai</span></h1>
            <p class="hero-desc">
                Input dan kelola nilai alternatif terhadap setiap kriteria menggunakan skala 1–5. Status penilaian ditampilkan secara otomatis.
            </p>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="filter-group">
                <span class="filter-label">Provinsi</span>
                <div class="provinsi-tabs">
                    <a href="?provinsi=Jawa Timur"  class="<?= $provinsi === 'Jawa Timur'  ? 'active' : '' ?>">Jawa Timur</a>
                    <a href="?provinsi=Jawa Tengah" class="<?= $provinsi === 'Jawa Tengah' ? 'active' : '' ?>">Jawa Tengah</a>
                </div>
            </div>
            <div class="stats-pill">
                <i class="fa-solid fa-layer-group" style="color:var(--teal-mid);font-size:13px;"></i>
                <strong><?= $total_kriteria ?></strong> kriteria aktif
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nama Daerah</th>
                        <th>Provinsi</th>
                        <th>Status Penilaian</th>
                        <th style="width:150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                if ($alternatifs && $alternatifs->num_rows > 0):
                    while ($alt = $alternatifs->fetch_assoc()):
                        // Hitung jumlah kriteria yang sudah dinilai untuk daerah ini
                        $count_stmt = $conn->prepare("
                            SELECT COUNT(*) as cnt
                            FROM tbl_nilai
                            WHERE alternatif_id = ?
                        ");
                        $count_stmt->bind_param("i", $alt['id']);
                        $count_stmt->execute();
                        $count_row   = $count_stmt->get_result()->fetch_assoc();
                        $is_done     = intval($count_row['cnt']) >= intval($total_kriteria) && intval($total_kriteria) > 0;
                        $btn_class   = $is_done ? 'btn-input-secondary' : 'btn-input-primary';
                        $btn_label   = $is_done ? '<i class="fa-solid fa-pen-to-square"></i> Edit' : '<i class="fa-solid fa-plus"></i> Input';
                ?>
                <tr>
                    <td style="color:var(--text-soft);font-size:13px;font-weight:600;"><?= $no++ ?></td>
                    <td>
                        <div class="daerah-name"><?= htmlspecialchars($alt['nama_daerah']) ?></div>
                    </td>
                    <td>
                        <span class="badge-provinsi">
                            <i class="fa-solid fa-map-pin" style="font-size:10px;"></i>
                            <?= htmlspecialchars($alt['provinsi']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($is_done): ?>
                            <span class="status-badge status-done">Sudah Dinilai</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">Belum Dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <button
                            class="btn-input <?= $btn_class ?>"
                            onclick="openModal(<?= $alt['id'] ?>, '<?= htmlspecialchars($alt['nama_daerah'], ENT_QUOTES) ?>', '<?= htmlspecialchars($alt['provinsi'], ENT_QUOTES) ?>')"
                        >
                            <?= $btn_label ?>
                        </button>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-regular fa-folder-open"></i>
                            <p>Tidak ada daerah ditemukan untuk provinsi <strong><?= htmlspecialchars($provinsi) ?></strong>.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- ===========================
     MODAL POPUP
=========================== -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box" id="modalBox">

        <div class="modal-header">
            <div class="modal-header-left">
                <p class="modal-eyebrow">Input Nilai Kriteria</p>
                <h2 class="modal-title" id="modalTitle">—</h2>
                <p class="modal-subtitle" id="modalSubtitle">—</p>
            </div>
            <button class="modal-close" onclick="closeModal()" title="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="loading-spinner" id="modalLoading">
                <i class="fa-solid fa-circle-notch"></i>
                Memuat data kriteria…
            </div>
            <div class="kriteria-list" id="kriteriaList" style="display:none;"></div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Batal</button>
            <button class="btn-submit" id="btnSimpan" onclick="simpanNilai()">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Nilai
            </button>
        </div>

    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ===========================
   STATE
=========================== */
let currentAlternatifId  = null;
let currentAlternatifName = '';

const SKALA = {
    0: { label: '— Pilih Nilai —', class: '' },
    1: { label: '1 — Sangat Rendah', class: '' },
    2: { label: '2 — Rendah',        class: '' },
    3: { label: '3 — Sedang',        class: '' },
    4: { label: '4 — Tinggi',        class: '' },
    5: { label: '5 — Sangat Tinggi', class: '' },
};

/* ===========================
   OPEN MODAL
=========================== */
function openModal(alternatifId, nama, provinsi) {
    currentAlternatifId   = alternatifId;
    currentAlternatifName = nama;

    document.getElementById('modalTitle').textContent    = nama;
    document.getElementById('modalSubtitle').textContent = provinsi;
    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('kriteriaList').style.display = 'none';
    document.getElementById('kriteriaList').innerHTML     = '';
    document.getElementById('btnSimpan').disabled         = true;

    document.getElementById('modalOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';

    // Fetch kriteria + nilai lama via AJAX
    fetch(`index.php?get_nilai=1&alternatif_id=${alternatifId}`)
        .then(res => res.json())
        .then(data => {
            renderKriteria(data);
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('kriteriaList').style.display = 'flex';
            document.getElementById('btnSimpan').disabled = false;
        })
        .catch(() => {
            document.getElementById('modalLoading').innerHTML =
                '<i class="fa-solid fa-circle-exclamation" style="color:#E37434;"></i><br>Gagal memuat data. Coba lagi.';
        });
}

/* ===========================
   RENDER KRITERIA
=========================== */
function renderKriteria(list) {
    const container = document.getElementById('kriteriaList');
    container.innerHTML = '';

    list.forEach(k => {
        const row = document.createElement('div');
        row.className = 'kriteria-row';

        const kode = document.createElement('span');
        kode.className   = 'kriteria-kode';
        kode.textContent = k.kode;

        const nama = document.createElement('span');
        nama.className   = 'kriteria-nama';
        nama.textContent = k.nama_kriteria;

        const select = document.createElement('select');
        select.className    = 'nilai-select';
        select.name         = `nilai[${k.id}]`;
        select.dataset.krit = k.id;

        Object.entries(SKALA).forEach(([val, meta]) => {
            const opt   = document.createElement('option');
            opt.value   = val;
            opt.textContent = meta.label;
            if (parseInt(val) === parseInt(k.nilai)) opt.selected = true;
            select.appendChild(opt);
        });

        row.appendChild(kode);
        row.appendChild(nama);
        row.appendChild(select);
        container.appendChild(row);
    });
}

/* ===========================
   SIMPAN NILAI
=========================== */
function simpanNilai() {
    const selects = document.querySelectorAll('#kriteriaList .nilai-select');
    const body    = new FormData();

    let hasEmpty = false;
    selects.forEach(sel => {
        const val = parseInt(sel.value);
        if (val === 0) { hasEmpty = true; return; }
        body.append(`nilai[${sel.dataset.krit}]`, val);
    });

    if (hasEmpty) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Harap isi semua nilai kriteria sebelum menyimpan.',
            confirmButtonColor: '#E37434',
        });
        return;
    }

    body.append('ajax_save', '1');
    body.append('alternatif_id', currentAlternatifId);

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan…';

    fetch('index.php', {
        method: 'POST',
        body: body,
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                confirmButtonColor: '#E37434',
                timer: 2000,
                timerProgressBar: true,
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message,
                confirmButtonColor: '#E37434',
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Nilai';
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
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Nilai';
    });
}

/* ===========================
   CLOSE MODAL
=========================== */
function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.body.style.overflow = '';
    currentAlternatifId = null;
}

// Klik di luar modal → tutup
document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ESC → tutup
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>
