<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

// PROTEKSI RBAC (Role-Based Access Control) ABSOLUT
$user_role = $_SESSION['role'] ?? 'Staff';
if ($user_role !== 'Administrator' && $user_role !== 'Project Manager') {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Akses Ditolak. Anda tidak memiliki otoritas manajerial.'];
    header("Location: index.php");
    exit;
}

// LOGIKA: Tambah Pengguna Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Staff';

    if ($nama && $username && $password) {
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->rowCount() > 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal: Username sudah terdaftar di sistem.'];
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nama, $username, $hashed_password, $role]);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Akun karyawan baru berhasil diorbitkan.'];
                header("Location: tim.php");
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal menyimpan data sistem.'];
            }
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Parameter form tidak lengkap.'];
    }
}

// LOGIKA BARU: Broadcast Notifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_notif'])) {
    $judul = trim($_POST['judul'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    $tipe = $_POST['tipe'] ?? 'info';
    $sender = $_SESSION['nama']; // Otomatis mencatat nama eksekutif yang menyiarkan

    if ($judul && $pesan) {
        try {
            $stmt_notif = $pdo->prepare("INSERT INTO notifications (sender_name, title, message, type) VALUES (?, ?, ?, ?)");
            $stmt_notif->execute([$sender, $judul, $pesan, $tipe]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Pengumuman berhasil disiarkan ke seluruh tim.'];
            header("Location: tim.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Kesalahan fatal pada database saat menyiarkan pengumuman.'];
        }
    }
}

// LOGIKA: Hapus Pengguna
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $hapus_id = $_GET['id'];
    if ($hapus_id != $_SESSION['user_id']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$hapus_id]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Akun berhasil dicabut dari sistem.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal menghapus akun.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tindakan Ilegal: Anda tidak dapat menghapus akun Anda sendiri.'];
    }
    header("Location: tim.php");
    exit;
}

// Tarik data seluruh pengguna
try {
    $stmt_users = $pdo->query("SELECT id, nama, username, role FROM users ORDER BY id DESC");
    $users = $stmt_users->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

require_once 'includes/layout.php';
renderHeader('Manajemen Tim');
?>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 sm:mb-8 min-w-0">
  <div class="flex-1 min-w-0">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight truncate">Manajemen Tim</h1>
    <p class="text-sm text-slate-500 mt-2 truncate">Pusat kendali otorisasi, pendaftaran, dan siaran pengumuman operasional.</p>
  </div>
  <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 w-full md:w-auto">
    <!-- FITUR BARU: Tombol Siaran Notifikasi -->
    <button onclick="toggleNotifModal()" class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 px-6 py-2.5 bg-amber-100 text-amber-700 text-sm font-bold rounded-xl hover:bg-amber-200 transition-colors shadow-sm active:scale-95 border border-amber-200">
      <span class="material-symbols-outlined text-[18px]">campaign</span> Siarkan Pengumuman
    </button>
    <button onclick="toggleUserModal()" class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm active:scale-95">
      <span class="material-symbols-outlined text-[18px]">person_add</span> Tambah Anggota
    </button>
  </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-w-0 flex flex-col">
  <div class="overflow-x-auto w-full">
    <table class="w-full text-left whitespace-nowrap min-w-[800px] table-fixed">
      <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
        <tr>
          <th class="w-[30%] px-6 py-4 text-[10px] font-extrabold uppercase tracking-widest text-left">Nama Lengkap</th>
          <th class="w-[25%] px-6 py-4 text-[10px] font-extrabold uppercase tracking-widest text-left">Username Akses</th>
          <th class="w-[25%] px-6 py-4 text-[10px] font-extrabold uppercase tracking-widest text-left">Role / Otoritas</th>
          <th class="w-[20%] px-6 py-4 text-[10px] font-extrabold uppercase tracking-widest text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (count($users) > 0): foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50/50 transition-colors group">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                <span class="material-symbols-outlined text-[16px]">person</span>
              </div>
              <span class="font-bold text-sm text-slate-900 group-hover:text-blue-600 transition-colors truncate"><?= htmlspecialchars($u['nama']) ?></span>
              <?php if($u['id'] == $_SESSION['user_id']): ?>
                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-50 text-blue-600 border border-blue-100 uppercase tracking-widest">Anda</span>
              <?php endif; ?>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="text-xs font-mono font-bold text-slate-500"><?= htmlspecialchars($u['username']) ?></span>
          </td>
          <td class="px-6 py-4">
            <?php
                $r_db = $u['role'];
                if ($r_db == 'Administrator' || $r_db == 'Project Manager') { $rC = 'bg-blue-50 text-blue-700 border-blue-200'; }
                else { $rC = 'bg-slate-100 text-slate-600 border-slate-200'; }
            ?>
            <span class="inline-flex px-2.5 py-1 border rounded-md text-[10px] font-extrabold uppercase tracking-widest <?= $rC ?>"><?= htmlspecialchars($r_db) ?></span>
          </td>
          <td class="px-6 py-4 text-right">
            <?php if($u['id'] != $_SESSION['user_id']): ?>
            <button type="button" onclick="confirmAction('Hapus Akun?', 'Karyawan ini tidak akan bisa login lagi ke dalam sistem.', 'error', 'tim.php?action=delete&id=<?= $u['id'] ?>', 'Cabut Akses', '#ef4444')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all border border-transparent hover:border-red-200 hover:shadow-sm" title="Hapus Akun">
              <span class="material-symbols-outlined text-[20px]">person_remove</span>
            </button>
            <?php else: ?>
            <span class="text-xs font-medium text-slate-400 italic">No Action</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="4" class="px-6 py-20 text-center text-slate-500">
            <span class="material-symbols-outlined text-5xl mb-3 opacity-20 block">group_off</span>
            <p class="text-sm font-medium">Belum ada data anggota tim.</p>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL: Siarkan Pengumuman (Notifikasi) -->
<div id="notifModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleNotifModal()"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="notifModalContent">
    
    <div class="px-6 py-4 border-b border-slate-100 bg-amber-50 flex justify-between items-center">
      <h3 class="font-extrabold text-amber-800 text-lg flex items-center gap-2">
        <span class="material-symbols-outlined">campaign</span> Broadcast Notifikasi
      </h3>
      <button onclick="toggleNotifModal()" class="text-amber-600 hover:text-amber-800 transition-colors"><span class="material-symbols-outlined">close</span></button>
    </div>
    
    <form method="POST" action="tim.php" class="p-6 flex flex-col gap-4">
        <input type="hidden" name="kirim_notif" value="1">
        
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Judul Arahan</label>
            <input type="text" name="judul" required placeholder="Cth: Prioritas Sprint Minggu Ini" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
        </div>
        
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Detail Pesan / Instruksi</label>
            <textarea name="pesan" required rows="3" placeholder="Sampaikan instruksi dengan jelas ke seluruh tim..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
        </div>

        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Tingkat Urgensi</label>
            <select name="tipe" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                <option value="info">Informasi Umum (Info)</option>
                <option value="warning">Peringatan / Arahan PM (Warning)</option>
                <option value="urgent">Tindakan Segera (Urgent)</option>
            </select>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" onclick="toggleNotifModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Batal</button>
            <button type="submit" class="px-6 py-2.5 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-600 transition-colors shadow-sm active:scale-95">Pusht Ke Semua</button>
        </div>
    </form>
  </div>
</div>

<!-- MODAL: Tambah Anggota Baru -->
<div id="userModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleUserModal()"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="userModalContent">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
      <h3 class="font-extrabold text-slate-800 text-lg">Orbitkan Akun Baru</h3>
      <button onclick="toggleUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form method="POST" action="tim.php" class="p-6 flex flex-col gap-4">
        <input type="hidden" name="tambah_user" value="1">
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Nama Lengkap Pekerja</label>
            <input type="text" name="nama" required placeholder="Cth: Muhammad Yusya" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Username Login</label>
            <input type="text" name="username" required placeholder="Cth: yusya.admin" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Password Akses</label>
            <input type="password" name="password" required placeholder="Minimal 6 karakter..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Otoritas (Role)</label>
            <select name="role" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                <option value="Staff">Staff (Eksekutor Operasional)</option>
                <option value="Project Manager">Project Manager (Manajerial)</option>
                <option value="Administrator">Administrator (Hak Penuh)</option>
            </select>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" onclick="toggleUserModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Batal</button>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm active:scale-95">Buat Akun</button>
        </div>
    </form>
  </div>
</div>

<script>
function toggleUserModal() {
  const modal = document.getElementById("userModal");
  const content = document.getElementById("userModalContent");
  if (modal.classList.contains("hidden")) {
    modal.classList.remove("hidden");
    setTimeout(() => {
      content.classList.remove("scale-95", "opacity-0");
      content.classList.add("scale-100", "opacity-100");
    }, 10);
  } else {
    content.classList.remove("scale-100", "opacity-100");
    content.classList.add("scale-95", "opacity-0");
    setTimeout(() => { modal.classList.add("hidden"); }, 300);
  }
}

function toggleNotifModal() {
  const modal = document.getElementById("notifModal");
  const content = document.getElementById("notifModalContent");
  if (modal.classList.contains("hidden")) {
    modal.classList.remove("hidden");
    setTimeout(() => {
      content.classList.remove("scale-95", "opacity-0");
      content.classList.add("scale-100", "opacity-100");
    }, 10);
  } else {
    content.classList.remove("scale-100", "opacity-100");
    content.classList.add("scale-95", "opacity-0");
    setTimeout(() => { modal.classList.add("hidden"); }, 300);
  }
}
</script>

<?php renderFooter(); ?>