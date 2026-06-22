<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
require_once 'config/db.php';

$totalAlternatif = $conn->query(
    "SELECT COUNT(*) total FROM tbl_alternatif"
)->fetch_assoc()['total'] ?? 0;

$totalKriteria = $conn->query(
    "SELECT COUNT(*) total FROM tbl_kriteria"
)->fetch_assoc()['total'] ?? 0;

$totalJatim = $conn->query(
    "SELECT COUNT(*) total
     FROM tbl_alternatif
     WHERE provinsi='Jawa Timur'"
)->fetch_assoc()['total'] ?? 0;

$totalJateng = $conn->query(
    "SELECT COUNT(*) total
     FROM tbl_alternatif
     WHERE provinsi='Jawa Tengah'"
)->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="shortcut icon" href="assets/maple-leaf.png">
<title>Dashboard | SPK ELECTRE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --teal-light:#91C6BC;
    --teal-mid:#4B9DA9;
    --cream:#F6F3C2;
    --orange:#E37434;
    --text-dark:#1f3a3f;
    --text-soft:#6f8387;
}

body{
    min-height:100vh;
    font-family:'Outfit',sans-serif;
    background:
    linear-gradient(
        135deg,
        #f8fcfb 0%,
        #F6F3C2 50%,
        #eef8f6 100%
    );
    overflow-x:hidden;
}

.bg-blob{
    position:fixed;
    border-radius:50%;
    filter:blur(120px);
    pointer-events:none;
}

.blob1{
    width:700px;
    height:700px;
    top:-200px;
    left:-150px;

    background:
    radial-gradient(
        circle,
        rgba(145,198,188,.45),
        transparent 70%
    );
}

.blob2{
    width:500px;
    height:500px;
    bottom:-150px;
    right:-100px;

    background:
    radial-gradient(
        circle,
        rgba(75,157,169,.25),
        transparent 70%
    );
}

.wrapper{
    display:flex;
    min-height:100vh;
    position:relative;
    z-index:1;
}

.content{
    margin-left:280px;
    padding:40px;
    min-height:100vh;
}

.hero{
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.6);
    border-radius:28px;
    padding:40px;
    margin-bottom:30px;
    box-shadow:
    0 15px 40px rgba(75,157,169,.12);
}

.hero-top{
    display:flex;
    justify-content:space-between;
    gap:30px;
    align-items:flex-start;
}

.hero-label{
    color:var(--teal-mid);
    font-size:12px;
    font-weight:700;
    letter-spacing:.15em;
    margin-bottom:12px;
}

.hero h1{
    font-family:'Cormorant Garamond',serif;
    font-size:52px;
    line-height:1.05;
    max-width:850px;
}

.hero h1 span{
    color:var(--orange);
}

.hero-desc{
    margin-top:20px;
    max-width:750px;
}

.user-box{
    backdrop-filter:blur(50px);
    border:1px solid rgba(255, 255, 255, 0.31);
    padding:10px;
    border-radius:10px;
    min-width:180px;
    text-align:right;
}

.user-box span{
    display:block;
    font-size:12px;
    color:var(--text-soft);
}

.user-box strong{
    display:block;
    margin-top:6px;
    font-family:'Cormorant Garamond',serif;
    font-size:24px;
    font-weight:700;
    color:var(--orange);
}

.hero p{
    color:var(--text-soft);
    line-height:1.8;
    max-width:700px;
}

.stats{
    display:grid;
    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card{
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.6);
    border-radius:24px;
    padding:25px;
    box-shadow:
    0 10px 30px rgba(75,157,169,.1);
}

.card span{
    font-size:13px;
    color:var(--text-soft);
}

.card h2{
    margin-top:10px;
    font-size:40px;
    color:var(--teal-mid);
}

.quick{
    margin-top:30px;
    display:grid;
    grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.quick-card{
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(20px);
    border-radius:24px;
    padding:25px;
    border:1px solid rgba(255,255,255,.6);
    text-decoration:none;
    color:inherit;
    transition:.3s;
}

.quick-card:hover{
    transform:translateY(-5px);
    box-shadow:
    0 15px 35px rgba(227,116,52,.15);
}

.quick-card h3{
    color:var(--orange);
    margin-bottom:10px;
}

.quick-card p{
    color:var(--text-soft);
    line-height:1.6;
}


.card-panduan{
    max-width:1200px;
    margin-top:40px;
    background:rgba(255,255,255,.35);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.6);
    border-radius:30px;
    padding:40px;
}

h1{
    color:var(--teal-mid);
    margin-bottom:15px;
}

.desc{
    color:var(--text-soft);
    line-height:1.8;
    margin-bottom:30px;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:rgba(75,157,169,.15);
    color:var(--text-dark);
    padding:14px;
}

.table td{
    padding:14px;
    border-top:1px solid rgba(75,157,169,.15);
}

.note{
    margin-top:25px;
    background:
    rgba(227,116,52,.10);
    border:
    1px solid rgba(227,116,52,.20);
    padding:18px;
    border-radius:14px;
    color:#a44d1e;
}
</style>
</head>
<body>

<div class="bg-blob blob1"></div>
<div class="bg-blob blob2"></div>

<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="hero">
            <div class="hero-top">
                <div>
                    <p class="hero-label">
                        SISTEM PENDUKUNG KEPUTUSAN
                    </p>
                    <h1>
                        Penentuan Prioritas Daerah
                        untuk Peningkatan
                        <span>
                            Pembangunan Manusia
                        </span>
                    </h1>
                    <p class="hero-desc">
                        Menggunakan metode <b> ELECTRE I </b>
                        dan nilai Φ di Provinsi Jawa Timur
                        dan Jawa Tengah tahun 2024.
                    </p>
                </div>
                <div class="user-box">
                    <span>Login sebagai</span>
                    <strong>
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </strong>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="card">
                <span>Total Daerah</span>
                <h2><?= $totalAlternatif ?></h2>
            </div>
            <div class="card">
                <span>Total Kriteria</span>
                <h2><?= $totalKriteria ?></h2>
            </div>
            <div class="card">
                <span>Jawa Timur</span>
                <h2><?= $totalJatim ?></h2>
            </div>
            <div class="card">
                <span>Jawa Tengah</span>
                <h2><?= $totalJateng ?></h2>
            </div>
        </div>

        <div class="quick">
            <a href="alternatif/index.php" class="quick-card">
                <h3>Data Alternatif</h3>
                <p>Kelola data kabupaten dan kota.</p>
            </a>
            <a href="kriteria/index.php" class="quick-card">
                <h3>Data Kriteria</h3>
                <p>Kelola bobot dan tipe kriteria.</p>
            </a>
            <a href="nilai/index.php" class="quick-card">
                <h3>Input Nilai</h3>
                <p>Masukkan nilai setiap alternatif.</p>
            </a>
            <a href="hasil/ranking.php" class="quick-card">
                <h3>Ranking Φ</h3>
                <p>Lihat hasil prioritas daerah.</p>
            </a>
        </div>

        <div class="card-panduan">
            <h1>Panduan Penilaian Kriteria</h1>
            <p class="desc">
                Sistem menggunakan metode ELECTRE I dan nilai Φ (Phi)
                untuk menentukan prioritas daerah peningkatan pembangunan manusia.
                Nilai yang dimasukkan pada menu Data Nilai merupakan skor hasil
                konversi penilaian, bukan data mentah BPS.
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Kriteria</th>
                        <th>Tipe</th>
                        <th>Bobot Awal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>UHH</td>
                        <td>Benefit</td>
                        <td>0.14</td>
                        <td>Umur Harapan Hidup</td>
                    </tr>
                    <tr>
                        <td>HLS</td>
                        <td>Benefit</td>
                        <td>0.20</td>
                        <td>Harapan Lama Sekolah</td>
                    </tr>
                    <tr>
                        <td>RLS</td>
                        <td>Benefit</td>
                        <td>0.26</td>
                        <td>Rata-rata Lama Sekolah</td>
                    </tr>
                    <tr>
                        <td>Pengeluaran</td>
                        <td>Benefit</td>
                        <td>0.10</td>
                        <td>Pengeluaran Per Kapita</td>
                    </tr>
                    <tr>
                        <td>Kemiskinan</td>
                        <td>Cost</td>
                        <td>0.30</td>
                        <td>Persentase Penduduk Miskin</td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin-top:40px;color:#4B9DA9;">
            Skala Penilaian
            </h2>
            <p class="desc">
                Nilai yang diinput pada Data Nilai menggunakan skala 1 – 5.
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Skor</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Sangat Rendah</td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>Rendah</td>
                    </tr>

                    <tr>
                        <td>3</td>
                        <td>Sedang</td>
                    </tr>

                    <tr>
                        <td>4</td>
                        <td>Tinggi</td>
                    </tr>

                    <tr>
                        <td>5</td>
                        <td>Sangat Tinggi</td>
                    </tr>

                </tbody>

            </table>

            <div class="note">
                <strong>Catatan:</strong><br><br>
                • Semakin tinggi skor pada kriteria bertipe Benefit,
                semakin baik kondisi daerah.<br>

                • Semakin tinggi skor pada kriteria bertipe Cost,
                menunjukkan kondisi yang kurang baik.<br>

                • Sistem akan melakukan normalisasi, pembobotan,
                perhitungan Concordance, Discordance,
                Matriks Dominan (F dan G),
                Matriks Agregat (E),
                hingga menghasilkan nilai Φ (Phi).<br>

                • Semakin besar nilai Φ (Phi),
                semakin tinggi prioritas daerah dalam hasil perangkingan.
            </div>
        </div>
    </main>
</div>
</body>
</html>