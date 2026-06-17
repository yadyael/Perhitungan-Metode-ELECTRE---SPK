<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';

if(
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['nilai'])
){
    foreach($_POST['nilai'] as $alternatif_id => $kriteriaList){
        foreach($kriteriaList as $kriteria_id => $nilai){
            $cek = $conn->prepare("
                SELECT id
                FROM tbl_nilai
                WHERE alternatif_id=?
                AND kriteria_id=?
            ");
            $cek->bind_param(
                "ii",
                $alternatif_id,
                $kriteria_id
            );
            $cek->execute();
            if($cek->get_result()->num_rows > 0){
                $update = $conn->prepare("
                    UPDATE tbl_nilai
                    SET nilai=?
                    WHERE alternatif_id=?
                    AND kriteria_id=?
                ");
                $update->bind_param(
                    "dii",
                    $nilai,
                    $alternatif_id,
                    $kriteria_id
                );
                $update->execute();
            }else{
                $insert = $conn->prepare("
                    INSERT INTO tbl_nilai
                    (
                        alternatif_id,
                        kriteria_id,
                        nilai
                    )
                    VALUES
                    (
                        ?,?,?
                    )
                ");
                $insert->bind_param(
                    "iid",
                    $alternatif_id,
                    $kriteria_id,
                    $nilai
                );
                $insert->execute();
            }
        }
    }

    $_SESSION['success'] =
        'Nilai berhasil disimpan.';

    header("Location:index.php?provinsi=".$_GET['provinsi']);
    exit();
}

/* =========================
   AMBIL DATA
========================= */

$provinsi = $_GET['provinsi'] ?? 'Jawa Timur';
$alternatif = $conn->query("
    SELECT *
    FROM tbl_alternatif
    WHERE provinsi='$provinsi'
    ORDER BY nama_daerah
");

$kriteria = $conn->query("
    SELECT *
    FROM tbl_kriteria
    ORDER BY kode
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nilai</title>
<link rel="shortcut icon" href="../assets/maple-leaf.png">
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

.action-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.nilai-input{
    width:70px;
    padding:8px;
    text-align:center;
    border:1px solid rgba(75,157,169,.2);
    border-radius:8px;
}

.btn-save{
    border:none;
    background:#E37434;
    color:#fff;
    padding:12px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
}

.action-bar select{
    padding:10px 14px;
    border:none;
    border-radius:12px;
    background:rgba(255,255,255,.7);
    font-family:'Outfit',sans-serif;
    cursor:pointer;
}

.table-card{
    margin-top:30px;
    background:
    rgba(255,255,255,.35);
    backdrop-filter:blur(20px);
    border:
    1px solid rgba(255,255,255,.6);
    border-radius:28px;
    padding:30px;
    box-shadow:
    0 15px 40px rgba(75, 157, 169,.12);
}

table{
    width:100%;
    border-collapse:collapse;
    border:1px solid rgba(75,157,169,.2);
}

th, td{
    border:2px var(--teal-mid) solid;
    padding:15px;
}

th{
    background:
    rgba(145, 198, 188,.15);
    color: var(--text-dark);
    text-align:center;
}

td{
    border-top: 1px solid rgba(75, 157, 169,.12);
}

.empty-state{
    text-align:center;
    color:var(--text-soft);
    padding:30px;
}

</style>
</head>
<body>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="content">
            <div class="hero">
                <div class="hero-top">
                    <div>
                        <p class="hero-label">
                            NILAI
                        </p>
                        <h1>
                            Data
                            <span>Nilai</span>
                        </h1>
                        <p class="hero-desc">
                            Input nilai alternatif terhadap setiap kriteria.
                        </p>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <label><b>Pilih Provinsi</b></label>
                <form method="GET">
                    <select
                        name="provinsi"
                        onchange="this.form.submit()"
                    >
                        <option value="Jawa Timur"
                        <?= $provinsi=='Jawa Timur' ? 'selected' : '' ?>>
                            Jawa Timur
                        </option>

                        <option value="Jawa Tengah"
                        <?= $provinsi=='Jawa Tengah' ? 'selected' : '' ?>>
                            Jawa Tengah
                        </option>
                    </select>
                </form>
            </div>

            <form method="POST">
                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Daerah</th>
                                <?php while($k = $kriteria->fetch_assoc()): ?>
                                    <th><?= $k['kode'] ?></th>
                                <?php endwhile; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($alt = $alternatif->fetch_assoc()): ?>
                            <tr>
                                <td><?= $alt['nama_daerah'] ?></td>
                                <?php
                                mysqli_data_seek($kriteria,0);
                                while($k = $kriteria->fetch_assoc()):
                                ?>
                                <?php endwhile; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:20px;">
                        <button type="submit" class="btn-save">
                            Simpan Nilai
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if(isset($_SESSION['success'])): ?>
    <script>
    Swal.fire({
        icon:'success',
        title:'Berhasil',
        text:'<?= $_SESSION['success']; ?>',
        confirmButtonColor:'#E37434'
    });
    </script>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <script>
    Swal.fire({
        icon:'error',
        title:'Oops...',
        text:'<?= $_SESSION['error']; ?>',
        confirmButtonColor:'#E37434'
    });

    </script>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
</body>
</html>