<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/db.php';

/* =========================
   TAMBAH 
========================= */
if(
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'tambah'
){

    $nama_daerah = trim($_POST['nama_daerah']);
    $provinsi = trim($_POST['provinsi']);

    if(empty($nama_daerah)){
        $_SESSION['error'] = 'Nama daerah wajib diisi.';
        header("Location:index.php");
        exit();
    }

    $cek = $conn->prepare("
        SELECT id
        FROM tbl_alternatif
        WHERE nama_daerah=?
    ");

    $cek->bind_param("s",$nama_daerah);
    $cek->execute();

    if($cek->get_result()->num_rows > 0){
        $_SESSION['error'] = 'Nama daerah sudah ada.';
        header("Location:index.php");
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO tbl_alternatif
        (
            nama_daerah,
            provinsi
        )
        VALUES
        (
            ?,?
        )
    ");

    $stmt->bind_param(
        "ss",
        $nama_daerah,
        $provinsi
    );

    if($stmt->execute()){
        $_SESSION['success'] = 'Alternatif berhasil ditambahkan.';
    }else{
        $_SESSION['error'] = 'Alternatif gagal ditambahkan.';
    }

    header("Location:index.php");
    exit();
}

/* =========================
   EDIT
========================= */
if(
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'edit'
){

    $id = $_POST['id'];
    $nama_daerah = trim($_POST['nama_daerah']);
    $provinsi = trim($_POST['provinsi']);

    $cek = $conn->prepare("
        SELECT id
        FROM tbl_alternatif
        WHERE nama_daerah=? AND id!=?
    ");

    $cek->bind_param(
        "si",
        $nama_daerah,
        $id
    );

    $cek->execute();

    if($cek->get_result()->num_rows > 0){
        $_SESSION['error'] = 'Nama daerah sudah ada.';
        header("Location:index.php");
        exit();
    }

    $stmt = $conn->prepare("
        UPDATE tbl_alternatif
        SET
            nama_daerah=?,
            provinsi=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssi",
        $nama_daerah,
        $provinsi,
        $id
    );

    if($stmt->execute()){
        $_SESSION['success'] = 'Alternatif berhasil diperbarui.';
    }else{
        $_SESSION['error'] = 'Alternatif gagal diperbarui.';
    }

    header("Location:index.php");
    exit();
}

/* =========================
   HAPUS
========================= */
if(isset($_GET['hapus'])){

    $id = (int)$_GET['hapus'];
        $cek = $conn->prepare("
        SELECT id
        FROM tbl_nilai
        WHERE alternatif_id=?
        LIMIT 1
    ");

    $cek->bind_param("i",$id);
    $cek->execute();

    if($cek->get_result()->num_rows > 0){
        $_SESSION['error'] =
            'Alternatif sudah digunakan pada data nilai.';
        header("Location:index.php");
        exit();
    }

    $stmt = $conn->prepare("
        DELETE FROM tbl_alternatif
        WHERE id=?
    ");

    $stmt->bind_param("i",$id);

    if($stmt->execute()){
        $_SESSION['success'] = 'Alternatif berhasil dihapus.';
    }else{
        $_SESSION['error'] = 'Alternatif gagal dihapus.';
    }

    header("Location:index.php");
    exit();
}

/* =========================
   AMBIL DATA
========================= */

$data = $conn->query("
    SELECT *
    FROM tbl_alternatif
    ORDER BY nama_daerah ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alternatif</title>
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
    font-family: 'Outfit', sans-serif;
    min-height: 100vh;
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
    flex: 1;
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

/* SAMPE SINIIIIIIIII */

.btn-edit{
    border:none;
    align-items:center;
    background: #4B9DA9;
    color:white;
    display: inline-flex;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.22s ease;
    box-shadow: 0 8px 20px rgba(75,157,169,0.4);

}

.btn-delete{
    border:none;
    alighn-items:center;
    background: #E37434;
    color:white;
    display: inline-flex;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.22s ease;
    box-shadow: 0 4px 14px rgba(227,116,52,0.3);
}

.btn-edit:hover {
    box-shadow: 0 8px 20px rgba(75,157,169,0.4);
    transform: translateY(-2px);
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(227,116,52,0.4);
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

</style>
</head>
<body>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="content">
            <div class="hero">
                <p class="hero-label">SPK ELECTRE I</p>
                <h1>Data <span>Alternatif</span></h1>
                <p class="hero-desc">
                    Input dan kelola data kabupaten dan kota yang akan menjadi alternatif pada proses ELECTRE.
                </p>
            </div>

            <div class="action-bar">
                <button
                    class="btn-add"
                    onclick="openTambahModal()"
                >
                    + Tambah Alternatif
                </button>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Daerah</th>
                            <th>Provinsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($data->num_rows > 0): ?>
                        <?php while($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_daerah']) ?></td>

                            <td><?= htmlspecialchars($row['provinsi']) ?></td>

                            <td class="actions">
                                <button
                                    class="btn-edit"
                                    onclick="openEditModal(
                                        '<?= $row['id'] ?>',
                                        '<?= htmlspecialchars($row['nama_daerah']) ?>',
                                        '<?= htmlspecialchars($row['provinsi']) ?>'
                                    )"
                                >
                                    Edit
                                </button>

                                <button
                                    class="btn-delete"
                                    onclick="hapusData(<?= $row['id'] ?>)"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="empty-state">
                                Belum ada data alternatif.
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
                <h2>Tambah Alternatif</h2>
                <button class="close-btn" onclick="closeTambahModal()" title="Tutup">&times;</button>
            </div>

            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="form-group">
                    <label>Nama Daerah</label>
                    <input type="text" name="nama_daerah" required>
                </div>

                <div class="form-group">
                    <label>Provinsi</label>
                    <select name="provinsi" required>
                        <option value="Jawa Timur">Jawa Timur</option>
                        <option value="Jawa Tengah">Jawa Tengah</option>
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
                <h2>Edit Alternatif</h2>
                <button class="close-btn" onclick="closeEditModal()" title="Tutup">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label>Nama Daerah</label>
                    <input type="text" name="nama_daerah" id="edit_nama_daerah" required>
                </div>

                <div class="form-group">
                    <label>Provinsi</label>
                    <select name="provinsi" id="edit_provinsi">
                        <option value="Jawa Timur">Jawa Timur</option>
                        <option value="Jawa Tengah">Jawa Tengah</option>
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
        function openEditModal(id,nama_daerah,provinsi){
            document.getElementById('edit_id').value=id;
            document.getElementById('edit_nama_daerah').value=nama_daerah;
            document.getElementById('edit_provinsi').value=provinsi;

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
                title:'Hapus Alternatif?',
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

    <script>
        function closeModal() {
        document.getElementById('modalOverlay').classList.remove('open');
        document.body.style.overflow = '';
        currentAlternatifId = null;
    }
    </script>
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