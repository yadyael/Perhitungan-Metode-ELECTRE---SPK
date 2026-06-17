<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';

/* =========================
   TAMBAH KRITERIA
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'tambah'
) {

    $kode  = trim($_POST['kode']);
    $nama  = trim($_POST['nama_kriteria']);
    $bobot = $_POST['bobot'];
    $tipe  = $_POST['tipe'];

    $stmt = $conn->prepare("
        INSERT INTO tbl_kriteria
        (
            kode,
            nama_kriteria,
            bobot,
            tipe
        )
        VALUES
        (
            ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "ssds",
        $kode,
        $nama,
        $bobot,
        $tipe
    );

    if ($stmt->execute()) {
        $_SESSION['success'] =
            'Kriteria berhasil ditambahkan.';
    } else {
        $_SESSION['error'] =
            'Gagal menambahkan kriteria.';
    }
    header("Location: index.php");
    exit();
}

/* =========================
   EDIT KRITERIA
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'edit'
) {
    $id    = $_POST['id'];
    $kode  = trim($_POST['kode']);
    $nama  = trim($_POST['nama_kriteria']);
    $bobot = $_POST['bobot'];
    $tipe  = $_POST['tipe'];

    $stmt = $conn->prepare("
        UPDATE tbl_kriteria
        SET
            kode = ?,
            nama_kriteria = ?,
            bobot = ?,
            tipe = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssdsi",
        $kode,
        $nama,
        $bobot,
        $tipe,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] =
            'Kriteria berhasil diperbarui.';
    } else {
        $_SESSION['error'] =
            'Gagal memperbarui kriteria.';
    }
    header("Location: index.php");
    exit();
}

/* =========================
   HAPUS KRITERIA
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $conn->prepare("
        DELETE FROM tbl_kriteria
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] =
            'Kriteria berhasil dihapus.';
    } else {
        $_SESSION['error'] =
            'Gagal menghapus kriteria.';
    }
    header("Location: index.php");
    exit();
}

/* =========================
   AMBIL DATA
========================= */

$data = $conn->query("
    SELECT *
    FROM tbl_kriteria
    ORDER BY kode ASC
");
$totalBobot = $conn->query("
    SELECT
        COALESCE(SUM(bobot),0) total
    FROM tbl_kriteria
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kriteria</title>
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
    border-collapse:
    collapse;
}

th{
    background:
    rgba(145, 198, 188,.15);
    color: var(--text-dark);
    padding:15px;
    text-align:left;
}

td{
    padding:15px;
    border-top:
    1px solid rgba(75, 157, 169,.12);
}

.badge{
    padding: 6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}

.benefit{
    background: rgba(145, 198, 188,.18);
    color: #2c6f67;
}

.cost{
    background:rgba(227, 116, 52, .15);
    color: #b85a23;
}

.btn-edit{
    border:none;
    background: #4B9DA9;
    color:white;
    padding: 8px 14px;
    border-radius: 10px;
    cursor:pointer;
}

.btn-delete{
    border:none;
    background: #E37434;
    color:white;
    padding: 8px 14px;
    border-radius: 10px;
    cursor:pointer;
}

.success{
    color: #2c6f67;
}

.warning{
    color: #b88d1e;
}

.danger{
    color: #b9531c;
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
                            MASTER DATA
                        </p>
                        <h1>
                            Data
                            <span>Kriteria</span>
                        </h1>
                        <p class="hero-desc">
                            Kelola kriteria, bobot,
                            dan tipe yang digunakan
                            dalam proses ELECTRE.
                        </p>
                    </div>
                </div>
            </div>

            <?php
            $statusClass = 'warning';
            $statusText  = 'Belum Valid';
            if(abs($totalBobot - 1) < 0.0001){
                $statusClass = 'success';
                $statusText  = 'Valid';
            }
            elseif($totalBobot > 1){
                $statusClass = 'danger';
                $statusText  = 'Melebihi Batas';
            }
            ?>
            <div class="action-bar">
                <button class="btn-add" onclick="openTambahModal()">
                    + Tambah Kriteria
                </button>
                <div class="total-bobot <?= $statusClass ?>">
                    Total Bobot:
                    <?= number_format(
                        $totalBobot,
                        2
                    ) ?>
                    (<?= $statusText ?>)
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot</th>
                                <th>Tipe</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while(
                                $row = $data->fetch_assoc()
                            ): ?>
                            <td>
                                <?= htmlspecialchars(
                                    $row['kode']
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_kriteria']
                                ) ?>
                            </td>
                            <td>
                                <span class="
                                badge
                                <?= strtolower(
                                    $row['tipe']
                                ) ?>
                                ">
                                <?= $row['tipe'] ?>
                                </span>
                            </td>
                            <td>
                                <button
                                class="btn-edit"
                                onclick="openEditModal(
                                '<?= $row['id'] ?>',
                                '<?= htmlspecialchars(
                                    $row['kode']
                                ) ?>',
                                '<?= htmlspecialchars(
                                    $row['nama_kriteria']
                                ) ?>',
                                '<?= $row['bobot'] ?>',
                                '<?= $row['tipe'] ?>'
                                )"
                                >
                                Edit
                                </button>
                                <button
                                class="btn-delete"
                                onclick="hapusData(
                                <?= $row['id'] ?>
                                )"
                                >
                                Hapus
                                </button>
                            </td>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>