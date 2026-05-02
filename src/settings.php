<?php
require_once 'includes/auth.php'; // Proteksi wajib
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 1. Ambil data user yang sedang login dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $error_msg = "Gagal memuat data sistem.";
}

// 2. Tangani Eksekusi Form (Update Profil & Password)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Skenario A: Update Profil
    if (isset($_POST['update_profile'])) {
        $nama_baru = trim($_POST['nama']);
        $email_baru = trim($_POST['email']);
        
        try {
            $update = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $update->execute([$nama_baru, $email_baru, $user_id]);
            
            // Perbarui session agar nama di header langsung berubah tanpa relogin
            $_SESSION['nama'] = $nama_baru;
            $user['nama'] = $nama_baru;
            $user['email'] = $email_baru;
            
            $success_msg = "Data profil berhasil diperbarui.";
        } catch (PDOException $e) {
            $error_msg = "Gagal memperbarui profil. Email mungkin sudah digunakan.";
        }
    }

    // Skenario B: Update Password
    if (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        // Validasi krusial: Cocokkan password lama dulu
        if (password_verify($old_password, $user['password'])) {
            // Enkripsi password baru
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hashed_password, $user_id]);
            
            // Update variabel lokal agar tidak perlu query ulang
            $user['password'] = $hashed_password;
            $success_msg = "Kredensial password berhasil diubah.";
        } else {
            $error_msg = "Otorisasi Gagal: Password saat ini tidak valid.";
        }
    }
}

require_once 'includes/layout.php';
renderHeader('Pengaturan Sistem');
?>

<div class="mb-6 md:mb-8">
  <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Akun</h1>
  <p class="text-sm text-slate-500 mt-1">Kelola profil, keamanan, dan preferensi sistem Anda.</p>
</div>

<?php if($success_msg): ?>
<div
  class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-4 rounded-xl text-sm font-medium mb-6 flex items-center gap-2">
  <span class="material-symbols-outlined text-[18px]">check_circle</span> <?= $success_msg ?>
</div>
<?php endif; ?>

<?php if($error_msg): ?>
<div
  class="bg-red-50 text-red-600 border border-red-200 p-4 rounded-xl text-sm font-medium mb-6 flex items-center gap-2">
  <span class="material-symbols-outlined text-[18px]">error</span> <?= $error_msg ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

  <div class="lg:col-span-2 space-y-8 w-full min-w-0">
    <!-- PANEL PROFIL -->
    <section class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200">
      <h3 class="text-lg font-bold mb-6 flex items-center gap-2 text-slate-800">
        <span class="material-symbols-outlined text-blue-600">person</span> Informasi Profil
      </h3>

      <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="space-y-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" />
          </div>
          <div class="space-y-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Alamat Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Otoritas Sistem (Role)</label>
            <input type="text" value="<?= htmlspecialchars($user['role']) ?>" disabled
              class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed" />
          </div>
        </div>
        <button type="submit" name="update_profile"
          class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm shadow-sm hover:bg-blue-700 transition-all">Simpan
          Profil</button>
      </form>
    </section>

    <!-- PANEL KEAMANAN -->
    <section class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200">
      <h3 class="text-lg font-bold mb-6 flex items-center gap-2 text-slate-800">
        <span class="material-symbols-outlined text-red-500">lock</span> Keamanan & Kredensial
      </h3>

      <form method="POST" action="">
        <div class="space-y-6 max-w-md mb-6">
          <div class="space-y-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Password Saat Ini</label>
            <input type="password" name="old_password" required
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
              placeholder="Diperlukan untuk verifikasi" />
          </div>
          <div class="space-y-2">
            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Password Baru</label>
            <input type="password" name="new_password" required minlength="6"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
              placeholder="Minimal 6 karakter" />
          </div>
        </div>
        <button type="submit" name="update_password"
          class="px-6 py-3 bg-slate-800 text-white rounded-xl font-bold text-sm shadow-sm hover:bg-slate-900 transition-all">Update
          Kredensial</button>
      </form>
    </section>
  </div>

  <!-- PANEL INFORMASI SAMPING -->
  <div class="space-y-6">
    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 shadow-inner">
      <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">info</span> Protokol Keamanan
      </h4>
      <p class="text-xs text-blue-700 leading-relaxed">
        Direkomendasikan untuk tidak membagikan kredensial login kepada pihak di luar tim operasional. Semua aktivitas
        terekam dalam basis data pusat.
      </p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
      <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-4">Ringkasan Sesi</h4>
      <ul class="space-y-3">
        <li class="flex items-center justify-between text-sm">
          <span class="font-medium text-slate-600">ID Pengguna</span>
          <span
            class="font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">#<?= sprintf('%04d', $user['id']) ?></span>
        </li>
        <li class="flex items-center justify-between text-sm">
          <span class="font-medium text-slate-600">Status Akun</span>
          <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Aktif</span>
        </li>
      </ul>
    </div>
  </div>

</div>

<?php renderFooter(); ?>