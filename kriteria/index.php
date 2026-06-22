<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';

$pageTitle    = 'Data Kriteria';   // wajib
$pageSubtitle = 'Kelola Data Kriteria, Bobot, dan Tipe';

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

    if($bobot <= 0){
        $_SESSION['error'] = 'Bobot harus lebih dari 0.';
        header("Location:index.php");
        exit();
    }

    // ini beda laman/chat
    $cek = $conn->prepare("SELECT id FROM tbl_kriteria WHERE kode=?");
    $cek->bind_param("s",$kode);
    $cek->execute();
    $hasil = $cek->get_result();

    if($hasil->num_rows > 0){
        $_SESSION['error'] = 'Kode kriteria sudah digunakan.';
        header("Location:index.php");
        exit();
    }

    $cekNama = $conn->prepare("
        SELECT id
        FROM tbl_kriteria
        WHERE nama_kriteria=?
    ");

    $cekNama->bind_param("s",$nama);
    $cekNama->execute();

    if($cekNama->get_result()->num_rows > 0){
        $_SESSION['error'] = 'Nama kriteria sudah digunakan.';
        header("Location:index.php");
        exit();
    }

    $total = $conn->query("
        SELECT COALESCE(SUM(bobot),0) total
        FROM tbl_kriteria
    ")->fetch_assoc()['total'];

    if(($total + $bobot) > 1){
        $_SESSION['error'] = 'Total bobot tidak boleh melebihi 1.';
        header("Location:index.php");
        exit();
    }

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

    if($bobot <= 0){
        $_SESSION['error'] = 'Bobot harus lebih dari 0.';
        header("Location:index.php");
        exit();
    }

    // ini beda chat
    $cek = $conn->prepare("
        SELECT id
        FROM tbl_kriteria
        WHERE kode=? AND id!=?
    ");

    $cek->bind_param("si",$kode,$id);
    $cek->execute();

    $hasil = $cek->get_result();

    if($hasil->num_rows > 0){
        $_SESSION['error'] = 'Kode kriteria sudah digunakan.';
        header("Location:index.php");
        exit();
    }

    $cekNama = $conn->prepare("
        SELECT id
        FROM tbl_kriteria
        WHERE nama_kriteria=? AND id!=?
    ");

    $cekNama->bind_param("si",$nama,$id);
    $cekNama->execute();

    if($cekNama->get_result()->num_rows > 0){
        $_SESSION['error'] = 'Nama kriteria sudah digunakan.';
        header("Location:index.php");
        exit();
    }

    $total = $conn->query("
        SELECT COALESCE(SUM(bobot),0) total
        FROM tbl_kriteria
        WHERE id != $id
    ")->fetch_assoc()['total'];

    if(($total + $bobot) > 1){
        $_SESSION['error'] = 'Total bobot tidak boleh melebihi 1.';
        header("Location:index.php");
        exit();
    }

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

    $cek = $conn->prepare("
        SELECT id
        FROM tbl_nilai
        WHERE kriteria_id=?
        LIMIT 1
    ");

    $cek->bind_param("i",$id);
    $cek->execute();

    if($cek->get_result()->num_rows > 0){
        $_SESSION['error'] =
            'Kriteria sudah digunakan pada data nilai.';
        header("Location:index.php");
        exit();
    }
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
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --teal-light:#91C6BC;
    --teal-mid:#4B9DA9;
    --cream:#F6F3C2;
    --orange:#E37434;
    --text-dark:#1f3a3f;
    --text-soft:#6f8387;
    --glass-bg:    rgba(255,255,255,0.35);
    --glass-border:rgba(255,255,255,0.6);
    --glass-shadow:0 15px 40px rgba(75,157,169,0.12);
    --radius-lg:   28px;
    --radius-md:   16px;
    --radius-sm:   10px;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Outfit',sans-serif;
    min-height:100vh;
    background: linear-gradient(135deg, #f8fcfb 0%, #F6F3C2 50%, #eef8f6 100%);
    color: var(--text-dark);
    
}

.wrapper {
    display: flex;
}

.content{
    margin-left:280px;
    padding:40px;
    min-height:100vh;
    flex:1;
}

.hero{
    background: var(--glass-bg);
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border:1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    border-radius:28px;
    padding:40px;
    margin-bottom:30px;
    box-shadow:var(--glass-shadow);
}

.hero-label{
    display: inline-flex;
    align-items: center;
    color:var(--teal-mid);
    font-size:12px;
    font-weight:700;
    letter-spacing:.15em;
    text-transform: uppercase;
    margin-bottom:12px;
}

.hero-label::before {
    content: '';
    display: inline-block;
    width: 24px;
    height: 2px;
    background: var(--teal-mid);
    border-radius: 2px;
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

.btn-add{
    border:none;
    background:#E37434;
    color:white;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
}

.total-bobot{
    background:rgba(255,255,255,.35);
    padding:12px 18px;
    border-radius:12px;
    font-weight:600;
}

.table-card{
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 0;
    box-shadow: var(--glass-shadow);
    overflow: hidden;
}

table{
    width:100%;
    border-collapse:collapse;
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

td.actions {
    text-align: center;
    gap:16px;
}

tbody td {
    padding: 18px 22px;
    font-size: 14px;
    vertical-align: middle;
    align-items: center;
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
    align-items:center;
}

.cost{
    background:rgba(227, 116, 52, .15);
    align-items:center;
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

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.25);
    backdrop-filter:blur(5px);
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    width:500px;
    background:rgba(255, 255, 255, 0.75);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.6);
    border-radius:24px;
    padding:25px;
}

.modal-header{
    display:flex;
    justify-content:space-between;
    font-family:'Cormorant Garamond',serif;
    color: var(--orange);
    align-items:center;
    margin-bottom:20px;
}

.close-btn{
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(75,157,169,0.25);
    background: rgba(255,255,255,0.6);
    color: var(--orange);
    font-size: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.close-btn:hover {
    background: var(--orange);
    color: #fff;
    border-color: var(--orange);
    transform: rotate(90deg);
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:500;
}

.form-group input,
.form-group select{
    width:100%;
    padding:12px;
    font-family:'Outfit',sans-serif;
    border:1px rgba(7255, 255, 255,.2);
    border-radius:10px;
    outline:none;
}

.btn-save{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#E37434;
    color:white;
    cursor:pointer;
    font-weight:600;
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
        <?php include '../includes/header.php'; ?>
        <main class="content">
            <div class="hero">
                <p class="hero-label">SPK ELECTRE I</p>
                <h1>Data <span>Kriteria</span></h1>
                <p class="hero-desc">
                    Input dan kelola kriteria, bobot,
                    dan tipe yang digunakan
                    dalam proses ELECTRE.
                </p>
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
                <button
                    class="btn-add"
                    onclick="openTambahModal()"
                >
                    + Tambah Kriteria
                </button>
                <div class="total-bobot <?= $statusClass ?>">
                    Total Bobot:
                    <?= number_format($totalBobot,2) ?>
                    (<?= $statusText ?>)
                </div>
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
                    <?php if($data->num_rows > 0): ?>
                        <?php while($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row['kode']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nama_kriteria']) ?>
                            </td>
                            <td>
                                <?= number_format($row['bobot'],2) ?>
                            </td>
                            <td>
                                <span
                                class="badge <?= strtolower($row['tipe']) ?>">
                                    <?= $row['tipe'] ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button
                                class="btn-edit"
                                onclick="openEditModal(
                                '<?= $row['id'] ?>',
                                '<?= htmlspecialchars($row['kode']) ?>',
                                '<?= htmlspecialchars($row['nama_kriteria']) ?>',
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
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada data kriteria.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">

            <div class="modal-header">
                <h2>Tambah Kriteria</h2>
                <button class="close-btn" onclick="closeTambahModal()">&times;</button>
            </div>

            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="kode" required>
                </div>
                <div class="form-group">
                    <label>Nama Kriteria</label>
                    <input type="text" name="nama_kriteria" required>
                </div>
                <div class="form-group">
                    <label>Bobot</label>
                    <input type="number" step="0.0001" name="bobot" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="tipe" required>
                        <option value="Benefit">Benefit</option>
                        <option value="Cost">Cost</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Kriteria</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST">

                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="kode" id="edit_kode" required>
                </div>
                <div class="form-group">
                    <label>Nama Kriteria</label>
                    <input type="text" name="nama_kriteria" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Bobot</label>
                    <input type="number" step="0.0001" name="bobot" id="edit_bobot" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="tipe" id="edit_tipe">
                        <option value="Benefit">Benefit</option>
                        <option value="Cost">Cost</option>
                    </select>
                </div>

                <button type="submit" class="btn-save">
                    Update
                </button>
            </form>
        </div>
    </div>

    <script>
        function openTambahModal(){
            document.getElementById('modalTambah').style.display='flex';
        }
        function closeTambahModal(){
            document.getElementById('modalTambah').style.display='none';
        }
        function openEditModal(id,kode,nama,bobot,tipe){

            document.getElementById('edit_id').value=id;
            document.getElementById('edit_kode').value=kode;
            document.getElementById('edit_nama').value=nama;
            document.getElementById('edit_bobot').value=bobot;
            document.getElementById('edit_tipe').value=tipe;

            document.getElementById('modalEdit').style.display='flex';
        }

        function closeEditModal(){
            document.getElementById('modalEdit').style.display='none';
        }

        window.onclick=function(e){
            const tambah=document.getElementById('modalTambah');
            const edit=document.getElementById('modalEdit');

            if(e.target===tambah){
                closeTambahModal();
            }

            if(e.target===edit){
                closeEditModal();
            }
        }
        </script>

        <script>
        function hapusData(id){
            Swal.fire({
                title:'Hapus Kriteria?',
                text:'Data yang dihapus tidak dapat dikembalikan.',
                icon:'warning',
                showCancelButton:true,
                confirmButtonColor:'#E37434',
                cancelButtonColor:'#4B9DA9',
                confirmButtonText:'Ya, Hapus',
                cancelButtonText:'Batal'
            }).then((result)=>{
                if(result.isConfirmed){
                    window.location =
                    'index.php?hapus=' + id;
                }
            });
        }
    </script>

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