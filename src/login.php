<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';

// Jika sudah login, langsung arahkan ke Beranda
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // REVISI LOGIKA: Ubah tangkapan form dari email menjadi username
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi hash password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Akses Ditolak: Kredensial tidak valid.";
        }
    } catch (PDOException $e) {
        $error = "Kesalahan sistem. Hubungi administrator.";
    }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Angkasa Labs</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
    rel="stylesheet" />

  <script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['Inter', 'sans-serif']
        },
        colors: {
          primary: '#2563eb'
        }
      }
    }
  }
  </script>
  <style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL'0, 'wght'400, 'GRAD'0, 'opsz'24;
    vertical-align: middle;
  }
  </style>
</head>

<body class="bg-slate-50 font-sans antialiased flex items-center justify-center min-h-screen p-4 selection:bg-blue-200">

  <div
    class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-8 sm:p-10 transform transition-all duration-500 hover:shadow-2xl">

    <div class="flex flex-col items-center justify-center mb-8">
      <div
        class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg mb-5 transform -translate-y-2">
        <span class="material-symbols-outlined text-[28px]">rocket_launch</span>
      </div>
      <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Angkasa Labs</h1>
      <p class="text-[10px] font-black tracking-widest text-blue-600 uppercase mt-1.5">Executive Login</p>
    </div>

    <?php if($error): ?>
    <div
      class="bg-red-50 text-red-600 border border-red-200 p-3.5 rounded-xl text-sm font-bold mb-6 flex items-center gap-2 animate-pulse">
      <span class="material-symbols-outlined text-[18px]">error</span> <?= $error ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">

      <div class="space-y-2">
        <!-- REVISI UI: Ubah Input Email menjadi Input Username -->
        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500">Username Akses</label>
        <div class="relative flex items-center">
          <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[20px]">person</span>
          <input type="text" name="username" required
            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
            placeholder="admin">
        </div>
      </div>

      <div class="space-y-2">
        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-500">Password Sistem</label>
        <div class="relative flex items-center">
          <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[20px]">lock</span>
          <input type="password" id="passwordInput" name="password" required
            class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
            placeholder="••••••••">
          <!-- TOMBOL TOGGLE SHOW/HIDE PASSWORD -->
          <button type="button" onclick="togglePassword()"
            class="absolute right-4 text-slate-400 hover:text-blue-600 focus:outline-none transition-colors flex items-center justify-center p-1 rounded-md">
            <span id="eyeIcon" class="material-symbols-outlined text-[20px]">visibility</span>
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit"
          class="w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 hover:bg-blue-700 transition-all text-sm flex justify-center items-center gap-2">
          Akses Dashboard <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
      </div>

    </form>
  </div>

  <script>
  // Logika UI untuk Toggle Password
  function togglePassword() {
    const passInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passInput.type === 'password') {
      passInput.type = 'text';
      eyeIcon.innerText = 'visibility_off'; // Ubah ikon ke mata dicoret
      eyeIcon.classList.add('text-blue-600');
    } else {
      passInput.type = 'password';
      eyeIcon.innerText = 'visibility'; // Kembalikan ikon
      eyeIcon.classList.remove('text-blue-600');
    }
  }
  </script>

</body>

</html>