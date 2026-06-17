<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare(
        "SELECT * FROM tbl_users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (
        $user &&
        password_verify($password, $user['password'])
    ) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../dashboard.php');
        exit;
    } else {
        $error = 'Username atau password anda salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="../assets/maple-leaf.png">
<title>Login — SPK ELECTRE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after{
  box-sizing:border-box;
  margin:0;
  padding:0;
}

:root{
  --cream:#F6F3C2;
  --orange:#E37434;

  --bg-main:#f8fcfb;
  --text-dark:#1f3a3f;
  --text-soft:#6f8387;

  --ff-serif:'Cormorant Garamond', serif;
  --ff-sans:'Outfit', sans-serif;
}
html,
body{
  height:100%;
  font-family:var(--ff-sans);
}

body{
  background:
  linear-gradient(
    135deg,
    #f8fcfb 0%,
    #f6f3c2 50%,
    #eef8f6 100%
  );
  position:relative;
  overflow:hidden;
}

/* BACKGROUND BLOBS */

.bg-blob{
  position:fixed;
  border-radius:50%;
  filter:blur(100px);
  pointer-events:none;
  z-index:0;
}

.bg-blob-1{
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

.bg-blob-2{
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

.bg-blob-3{
  width:450px;
  height:450px;
  top:35%;
  left:40%;
  background:
  radial-gradient(
    circle,
    rgba(227,116,52,.12),
    transparent 70%
  );
}

/* LAYOUT */

.page{
  display:grid;
  grid-template-columns: 58% 42%;
  height:100vh;
  position:relative;
  z-index:1;
}

/* GLASS */

.glass{
  backdrop-filter:blur(30px);
  -webkit-backdrop-filter:blur(30px);
}

/* LEFT */

.left{
  display:flex;
  justify-content:center;
  align-items:center;
  padding:60px;
  position:relative;
}

.left::before{
  content:'';
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:4px;
  background:
  linear-gradient(
    90deg,
    var(--teal-light),
    var(--teal-mid),
    var(--orange)
  );
}

.brand{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:55px;
}

.brand-dot{
  width:10px;
  height:10px;
  border-radius:50%;
  background:var(--orange);
}

.brand-name{
  font-size:13px;
  font-weight:600;
  color:var(--teal-mid);
  letter-spacing:.12em;
  text-transform:uppercase;
}

.form-heading{
  font-family:var(--ff-serif);
  font-size:52px;
  color:var(--text-dark);
  line-height:1.05;
  margin-bottom:10px;
}

.form-heading em{
  color:var(--orange);
}

.form-sub{
  color:var(--text-soft);
  font-size:14px;
  line-height:1.8;
  margin-bottom:40px;
}

.field{
  margin-bottom:18px;
}

.field label{
  display:block;
  margin-bottom:8px;
  font-size:11px;
  font-weight:600;
  color:var(--teal-mid);
  letter-spacing:.1em;
  text-transform:uppercase;
}

.field input{
  width:100%;
  padding:14px 16px;
  border-radius:12px;
  border:1px solid rgba(75,157,169,.15);
  background:rgba(255,255,255,.65);
  color:var(--text-dark);
  font-size:14px;
  outline:none;
  transition:.25s;
}

.field input::placeholder{
  color:#94a7ab;
}

.field input:focus{
  border-color:var(--teal-mid);
  box-shadow:
  0 0 0 4px rgba(75,157,169,.15);
}

.btn-login{
  width:100%;
  border:none;
  border-radius:12px;
  padding:14px;
  margin-top:5px;
  background:var(--orange);
  color:white;
  font-weight:600;
  font-size:14px;
  cursor:pointer;
  transition:.25s;
}

.btn-login:hover{
  transform:translateY(-2px);
  box-shadow:
  0 12px 28px rgba(227,116,52,.35);
}

.error-box{
  background:rgba(227,116,52,.1);
  border:1px solid rgba(227,116,52,.2);
  color:#b9531c;
  border-radius:10px;
  padding:12px;
  margin-bottom:18px;
}

.register-link{
  text-align:center;
  margin-top:20px;
  font-size:13px;
  color:var(--text-soft);
}

.register-link a{
  color:var(--orange);
  text-decoration:none;
  font-weight:600;
}

/* RIGHT */

.right{
  display:flex;
  flex-direction:column;
  justify-content:center;

  padding:80px 90px 80px 20px;

  position:relative;
}

.info-label{
  color:var(--teal-mid);
  font-size:11px;
  font-weight:700;
  letter-spacing:.15em;
  text-transform:uppercase;
  margin-bottom:20px;
}

.info-title{
  font-family:var(--ff-serif);
  font-size:68px;
  line-height:1;
  color:var(--text-dark);
  margin-bottom:28px;
}

.info-title em{
  color:var(--teal-mid);
}

.info-desc{
  color:var(--text-soft);
  line-height:1.9;
  margin-bottom:35px;
  max-width:500px;
}

.info-desc strong{
  color:var(--text-dark);
}

/* CARDS */

.cards{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
}

.login-card{
  width:100%;
  max-width:480px;
  background:rgba(255,255,255,.42);
  border:1px solid rgba(255,255,255,.7);
  border-radius:32px;
  padding:50px;
  box-shadow:
    0 20px 60px rgba(75,157,169,.12),
    inset 0 1px 0 rgba(255,255,255,.6);
  backdrop-filter:blur(30px);
  -webkit-backdrop-filter:blur(30px);
  position:relative;
  overflow:hidden;
}

.login-card::before{
  content:'';
  position:absolute;
  top:-120px;
  right:-120px;
  width:240px;
  height:240px;
  border-radius:50%;
  background:
    radial-gradient(
      circle,
      rgba(145,198,188,.35),
      transparent 70%
    );
}

.card{
  background:rgba(255,255,255,.38);
  border:1px solid rgba(255,255,255,.75);
  border-radius:22px;
  padding:22px;
  backdrop-filter:blur(18px);
  transition:.3s ease;
  box-shadow:
    0 10px 30px rgba(75,157,169,.08);
}

.card:hover{
  transform:
    translateY(-6px)
    scale(1.02);
  box-shadow:
    0 18px 40px rgba(75,157,169,.14);
}

.card-label{
  color:var(--teal-mid);
  font-size:11px;
  font-weight:700;
  letter-spacing:.08em;
  text-transform:uppercase;
  margin-bottom:8px;
}

.card-text{
  color:var(--text-soft);
  font-size:13px;
  line-height:1.7;
}

body::before{
  content:'';
  position:fixed;
  inset:0;
  background:
  linear-gradient(
    rgba(255,255,255,.25) 1px,
    transparent 1px
  ),
  linear-gradient(
    90deg,
    rgba(255,255,255,.25) 1px,
    transparent 1px
  );
  background-size:60px 60px;
  mask-image:
    radial-gradient(circle at center,
    black 30%,
    transparent 90%);

  pointer-events:none;
}

.source{
  display:none;
}

/* ANIMATION */

@keyframes fadeUp{
  from{
    opacity:0;
    transform:translateY(20px);
  }
  to{
    opacity:1;
    transform:translateY(0);
  }
}

.left{
  animation:fadeUp .5s ease;
}

.right{
  animation:fadeUp .5s ease .15s both;
}

/* RESPONSIVE */

@media(max-width:900px){

  .page{
    grid-template-columns:1fr;
  }

  .right{
    display:none;
  }

  .left{
    padding:40px;
  }
}

</style>
</head>
<body>

<div class="bg-blob bg-blob-1"></div>
<div class="bg-blob bg-blob-2"></div>
<div class="bg-blob bg-blob-3"></div>

<div class="page">

  <!-- LEFT: FORM -->
  <div class="left">
    <div class="login-card glass">
      <div class="brand">
        <div class="brand-dot"></div>
        <div class="brand-name">SPK ELECTRE</div>
      </div>

      <h1 class="form-heading">Selamat<br><em>Datang</em></h1>
      <p class="form-sub">Masuk untuk Menghitung Penentuan Prioritas Daerah untuk Peningkatan Pembangunan Manusia di Jawa timur dan Jawa Tengah 2024 Menggunakan Metode ELECTRE</p>

      <?php if ($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="field">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Masukkan username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            required
            autocomplete="username"
          >
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            required
            autocomplete="current-password"
          >
        </div>
        <button type="submit" class="btn-login">Masuk</button>
      </form>

      <!-- <p class="register-link">
        Belum punya akun?
        <a href="register.php">Daftar di sini</a>
      </p> -->
    </div>
  </div>

  <!-- RIGHT: INFO -->
  <div class="right glass">

    <p class="info-label">Metode MCDM</p>

    <h2 class="info-title">Apa itu<br><em>ELECTRE?</em></h2>

    <p class="info-desc">
      <strong>ELECTRE</strong> (<em>Elimination Et Choix Traduisant la Realite</em>)
      adalah metode pengambilan keputusan multikriteria berbasis prinsip
      <strong>outranking</strong> — membandingkan setiap pasang alternatif
      untuk menentukan mana yang lebih unggul berdasarkan kriteria berbobot.
    </p>

    <div class="cards">
      <div class="card">
        <div class="card-label">Outranking</div>
        <div class="card-text">Setiap alternatif dibandingkan berpasangan, bukan diberi skor tunggal.</div>
      </div>
      <div class="card">
        <div class="card-label">Concordance & Discordance</div>
        <div class="card-text">Mengukur dominasi dan kelemahan relatif antar alternatif.</div>
      </div>
      <div class="card">
        <div class="card-label">Multi-Kriteria</div>
        <div class="card-text">Mendukung kriteria benefit dan cost sekaligus dalam satu proses.</div>
      </div>
      <div class="card">
        <div class="card-label">Nilai Phi</div>
        <div class="card-text">Net superior value sebagai dasar perangkingan akhir alternatif.</div>
      </div>
    </div>

  </div>
</div>
</body>
</html>