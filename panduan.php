<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panduan Penilaian</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

:root{
    --teal-light:#91C6BC;
    --teal-mid:#4B9DA9;
    --cream:#F6F3C2;
    --orange:#E37434;
    --text-dark:#1f3a3f;
    --text-soft:#6f8387;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Outfit',sans-serif;
    min-height:100vh;
    background:
    linear-gradient(
        135deg,
        #f8fcfb 0%,
        #F6F3C2 50%,
        #eef8f6 100%
    );
    padding:40px;
}

.card-panduan{
    max-width:1200px;
    margin:auto;
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

</body>
</html>