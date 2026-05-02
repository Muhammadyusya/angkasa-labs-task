<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];

// Menarik data pengguna yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Logika Pemrosesan Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Skenario 1: Update Profil
    if (isset($_POST['update_profil'])) {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if ($nama && $username) {
            // Validasi duplikasi username
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt_check->execute([$username, $user_id]);
            
            if ($stmt_check->rowCount() > 0) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Username tersebut sudah terdaftar pada akun lain.'];
            } else {
                try {
                    $stmt_update = $pdo->prepare("UPDATE users SET nama = ?, username = ? WHERE id = ?");
                    $stmt_update->execute([$nama, $username, $user_id]);
                    
                    // Sinkronisasi Sesi
                    $_SESSION['nama'] = $nama;
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Identitas profil berhasil diperbarui.'];
                    
                    header("Location: profil.php");
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal melakukan pembaruan data sistem.'];
                }
            }
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Parameter Nama dan Username tidak boleh kosong.'];
        }
    }

    // Skenario 2: Update Kriptografi (Password)
    if (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($old_password && $new_password && $confirm_password) {
            if ($new_password === $confirm_password) {
                if (password_verify($old_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    try {
                        $stmt_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt_pass->execute([$hashed_password, $user_id]);
                        
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Kunci sandi keamanan berhasil diperbarui.'];
                        header("Location: profil.php");
                        exit;
                    } catch (PDOException $e) {
                        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Kesalahan fatal pada enkripsi database.'];
                    }
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Otorisasi Gagal: Kata sandi lama tidak sesuai.'];
                }
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Konfirmasi kata sandi baru tidak sinkron.'];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Seluruh parameter keamanan wajib diisi.'];
        }
    }
}

require_once 'includes/layout.php';
renderHeader('Profil Saya');
?>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 sm:mb-8 min-w-0">
  <div class="flex-1 min-w-0">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight truncate">Profil Saya</h1>
    <p class="text-sm text-slate-500 mt-2 truncate">Pusat kendali identitas dan keamanan sesi personal Anda.</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start min-w-0 pb-10">
    
    <!-- KOLOM KIRI: Informasi Profil -->
    <div class="lg:col-span-7 flex flex-col gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <span class="material-symbols-outlined text-[20px]">badge</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Identitas Pengguna</h2>
                    <p class="text-xs font-medium text-slate-500">Perbarui nama tampilan dan username login.</p>
                </div>
            </div>

            <form method="POST" action="profil.php" class="flex flex-col gap-5">
                <input type="hidden" name="update_profil" value="1">
                
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex flex-col gap-2 mt-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Otoritas Sesi (Read-Only)</label>
                    <input type="text" value="<?= htmlspecialchars($user['role']) ?>" disabled class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-bold text-slate-500 cursor-not-allowed">
                </div>

                <div class="mt-2 pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm active:scale-95 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: Keamanan Sandi -->
    <div class="lg:col-span-5 flex flex-col gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <span class="material-symbols-outlined text-[20px]">lock_reset</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Keamanan Sandi</h2>
                    <p class="text-xs font-medium text-slate-500">Gunakan kata sandi yang kuat.</p>
                </div>
            </div>

            <form method="POST" action="profil.php" class="flex flex-col gap-5">
                <input type="hidden" name="update_password" value="1">
                
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Kata Sandi Lama</label>
                    <input type="password" name="old_password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Kata Sandi Baru</label>
                    <input type="password" name="new_password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Konfirmasi Sandi Baru</label>
                    <input type="password" name="confirm_password" required placeholder="Ulangi sandi baru" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="mt-2 pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-slate-800 text-white text-sm font-bold rounded-xl hover:bg-slate-900 transition-colors shadow-sm active:scale-95 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">key</span> Update Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php renderFooter(); ?>