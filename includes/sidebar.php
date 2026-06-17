<?php
    $base_url = '/METODE-ELECTRE/';
?>
<style>
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:280px;
    height:100vh;
    overflow-y:auto;
    z-index:1000;
    padding:30px 20px;
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(25px);
    border-right:1px solid rgba(255,255,255,.6);
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;

    margin-bottom:40px;
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
}

.menu a:hover{
    background:var(--teal-light);
    color:white;
    transform:translateX(5px);
}

.menu-divider{
    height:1px;
    background:rgba(75,157,169,.15);
    margin:10px 0;
}

.logout-btn{
    background:
    rgba(227,116,52,.10);
    color:
    var(--orange) !important;
}

.logout-btn:hover{
    background:
    var(--orange) !important;
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
        <div class="menu">
            <a href="<?= $base_url ?>dashboard.php">Dashboard</a>
            <a href="<?= $base_url ?>kriteria/index.php">
                Data Kriteria
            </a>
            <a href="<?= $base_url ?>alternatif/index.php">
                Data Alternatif
            </a>
            <a href="<?= $base_url ?>nilai/index.php">
                Data Nilai
            </a>
            <a href="<?= $base_url ?>normalisasi/index.php">
                Matriks R
            </a>
            <a href="<?= $base_url ?>terbobot/index.php">
                Matriks V
            </a>
            <a href="<?= $base_url ?>concordance/index.php">
                Concordance
            </a>
            <a href="<?= $base_url ?>discordance/index.php">
                Discordance
            </a>
            <a href="<?= $base_url ?>hasil/index.php">
                Ranking Φ
            </a>
            <div class="menu-divider"></div>
            <a href="<?= $base_url ?>auth/logout.php" class="logout-btn">
                <b>Logout</b>
            </a>
        </div>

</div>