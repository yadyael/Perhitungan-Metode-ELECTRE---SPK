<?php
/**
 * Fixed Glass Header — include di setiap halaman.
 *
 * Cara pakai:
 *   $pageTitle    = 'Perhitungan ELECTRE';   // wajib
 *   $pageSubtitle = 'ELECTRE I + Nilai Phi'; // opsional
 *   include '../includes/header.php';
 *
 * Session $_SESSION['username'] sudah harus tersedia sebelum include ini.
 */

$pageTitle    = $pageTitle    ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
?>

<!-- ========= FIXED GLASS HEADER ============ -->
<header class="glass-header" id="glassHeader">

    <!-- Kiri: nama halaman -->
    <div class="gh-left">
        <div class="gh-page-label">SPK ELECTRE I</div>
        <div class="gh-page-title">
            <?= htmlspecialchars($pageTitle) ?>
            <?php if ($pageSubtitle): ?>
                <span class="gh-page-sub">— <?= htmlspecialchars($pageSubtitle) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kanan: user pill -->
    <div class="gh-right">
        <div class="gh-user-pill">
            <div class="gh-user-avatar">
                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="gh-user-info">
                <span class="gh-user-role">Hi,</span>
                <span class="gh-user-name">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
                </span>
            </div>
        </div>
    </div>

</header>

<style>
/* ===============================================================
   GLASS HEADER — Apple-style frosted glass
=============================================================== */

.glass-header {
    /* positioning */
    position: fixed;
    top: 16px;
    left: calc(280px + 24px);
    right: 24px;
    z-index: 900;
    /* Apple glass: blur kuat, saturasi tinggi */
    background: rgba(255, 255, 255, 0.52);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-bottom: 1px solid rgba(145, 198, 188, 0.22);

    border-radius: 20px;
    padding: 0 24px;
    height: 64px;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;

    /* shadow lembut, bukan harsh drop-shadow */
    box-shadow:
        0 2px 8px  rgba(75, 157, 169, 0.08),
        0 8px 32px rgba(75, 157, 169, 0.10),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);

    transition: box-shadow 0.3s ease, background 0.3s ease;
}

/* Sedikit lebih solid saat di-scroll */
.glass-header.scrolled {
    background: rgba(255, 255, 255, 0.72);
    box-shadow:
        0 2px 12px rgba(75, 157, 169, 0.12),
        0 12px 40px rgba(75, 157, 169, 0.14),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
}

/* ---- Kiri ---- */
.gh-left {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1px;
    min-width: 0;
}

.gh-page-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--teal-mid, #4B9DA9);
    line-height: 1;
}

.gh-page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark, #1f3a3f);
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.gh-page-sub {
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--text-soft, #6f8387);
}

/* ---- Kanan: user pill ---- */
.gh-right { flex-shrink: 0; }

.gh-user-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.80);
    border-radius: 40px;
    padding: 6px 16px 6px 6px;
    box-shadow: 0 2px 8px rgba(75, 157, 169, 0.08);
    cursor: default;
    transition: box-shadow 0.2s;
}

.gh-user-pill:hover {
    box-shadow: 0 4px 16px rgba(75, 157, 169, 0.16);
}

.gh-user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal-light, #91C6BC), var(--teal-mid, #4B9DA9));
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    /* subtle inner ring */
    box-shadow: 0 0 0 2px rgba(255,255,255,0.7), 0 2px 6px rgba(75,157,169,0.25);
}

.gh-user-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.gh-user-role {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.08em;
    color: var(--text-soft, #6f8387);
    line-height: 1;
}

.gh-user-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--orange, #E37434);
    line-height: 1.1;
}

/* ===============================================================
   KOMPENSASI KONTEN
=============================================================== */
.content {
    padding-top: 100px !important;
}
</style>

<script>
(function () {
    const header = document.getElementById('glassHeader');
    if (!header) return;

    const onScroll = () => {
        header.classList.toggle('scrolled', window.scrollY > 10);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // cek posisi awal
})();
</script>
