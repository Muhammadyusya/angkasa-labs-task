<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function renderHeader($title) {
    global $pdo; 
    $current_page = basename($_SERVER['PHP_SELF']);
    
    $flash_msg = ''; $flash_type = 'success';
    if (isset($_SESSION['flash'])) {
        $flash_msg = $_SESSION['flash']['msg'];
        $flash_type = $_SESSION['flash']['type'];
        unset($_SESSION['flash']);
    }

    $user_role = $_SESSION['role'] ?? 'Staff'; 
    $is_executive = ($user_role === 'Administrator' || $user_role === 'Project Manager');

    // MENARIK 5 NOTIFIKASI TERAKHIR DARI DATABASE
    try {
        $stmt_notif = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
        $global_notifications = $stmt_notif->fetchAll();
    } catch(PDOException $e) {
        $global_notifications = [];
    }
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title><?= htmlspecialchars($title) ?> | Angkasa Labs</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  tailwind.config = {
    theme: {
      extend: { 
        fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }, 
        colors: { primary: '#2563eb' } 
      }
    }
  }
  </script>
  <style>
  .material-symbols-outlined { font-variation-settings: 'FILL'0, 'wght'400, 'GRAD'0, 'opsz'24; vertical-align: middle; }
  ::-webkit-scrollbar { width: 6px; height: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) { overflow-y: hidden !important; }
  .swal2-popup { font-family: '"Plus Jakarta Sans"', sans-serif !important; border-radius: 1rem !important; }
  .swal2-title { font-weight: 800 !important; color: #0f172a !important; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans h-screen w-screen overflow-hidden flex" onload="startClock()">

  <?php if($flash_msg): ?>
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const Toast = Swal.mixin({
      toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3500, timerProgressBar: true,
      didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
    });
    Toast.fire({ icon: '<?= $flash_type == "success" ? "success" : "error" ?>', title: '<?= htmlspecialchars($flash_msg) ?>' });
  });
  </script>
  <?php endif; ?>

  <div id="mobileOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transform -translate-x-full lg:relative lg:translate-x-0 transition-transform duration-300 shrink-0 shadow-2xl lg:shadow-none">
    <div class="h-16 flex items-center px-6 border-b border-slate-100 shrink-0">
      <a href="index.php" class="flex items-center gap-3 group">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform duration-300">
          <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
        </div>
        <div>
          <h1 class="text-base font-bold tracking-tight text-slate-900 leading-none">Angkasa Labs</h1>
          <p class="text-[9px] font-bold tracking-widest text-blue-600 uppercase mt-0.5"><?= htmlspecialchars($user_role) ?></p>
        </div>
      </a>
    </div>

    <nav class="flex-1 overflow-y-auto p-4 space-y-1">
      <p class="px-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-2">Menu Utama</p>
      <?php
        $nav = ['index.php'=>['home','Beranda'], 'daftar_tugas.php'=>['list_alt','Daftar Tugas'], 'proses.php'=>['view_kanban','Board Proses'], 'tambah_tugas.php'=>['add_circle','Tambah Tugas']];
        foreach($nav as $path => $info): $active = ($current_page == $path);
      ?>
      <a href="<?= $path ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $active ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <span class="material-symbols-outlined text-[20px]" <?= $active ? 'style="font-variation-settings:\'FILL\' 1;"' : '' ?>><?= $info[0] ?></span> <span class="text-sm font-semibold"><?= $info[1] ?></span>
      </a>
      <?php endforeach; ?>
    </nav>

    <div class="p-4 mt-auto border-t border-slate-100">
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 hover:border-slate-300 transition-colors">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <div class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </div>
            <span class="text-xs font-bold text-slate-800">Sistem Online</span>
          </div>
          <span class="text-[9px] font-extrabold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">99.9%</span>
        </div>
        <div class="space-y-1.5">
          <div class="flex justify-between text-[10px] font-bold text-slate-500">
            <span>Beban Server</span>
            <span>24%</span>
          </div>
          <div class="w-full bg-slate-200 rounded-full h-1.5">
            <div class="bg-blue-500 h-1.5 rounded-full" style="width: 24%"></div>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden bg-slate-50">

    <header class="h-16 shrink-0 bg-white/90 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30">
      
      <div class="flex items-center">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 mr-2 text-slate-500 hover:text-slate-800 transition-colors focus:outline-none">
          <span class="material-symbols-outlined">menu</span>
        </button>
        
        <div class="hidden md:flex flex-col justify-center lg:border-l-0 lg:pl-0 lg:ml-0 border-l border-slate-200 pl-4 ml-2 h-10">
          <div class="flex items-center gap-1 mb-1">
             <span class="material-symbols-outlined text-[12px] text-slate-400">location_on</span>
             <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest leading-none">Tasikmalaya &bull; <?= date('d M Y') ?></span>
          </div>
          <span id="liveClock" class="text-sm font-black text-slate-800 tracking-tight leading-none"><?= date('H.i.s') ?> WIB</span>
        </div>
      </div>

      <div class="flex items-center gap-1 sm:gap-2">
        
        <!-- MODUL NOTIFIKASI DINAMIS -->
        <div class="relative flex items-center pr-2 sm:pr-4 border-r border-slate-200 h-10">
            <button onclick="toggleDropdown('notifDropdown')" class="relative p-2 text-slate-400 hover:text-blue-600 transition-colors focus:outline-none group rounded-full hover:bg-slate-50">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                <?php if(count($global_notifications) > 0): ?>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-white"></span>
                <?php endif; ?>
            </button>
            
            <div id="notifDropdown" class="dropdown-menu hidden absolute right-0 top-12 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 overflow-hidden z-50">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <span class="text-xs font-extrabold text-slate-800 uppercase tracking-widest">Pengumuman Tim</span>
                    <span class="text-[9px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-md"><?= count($global_notifications) ?> Pesan</span>
                </div>
                <div class="flex flex-col max-h-[350px] overflow-y-auto divide-y divide-slate-50">
                    
                    <?php if(count($global_notifications) > 0): foreach($global_notifications as $notif): 
                        if($notif['type'] == 'urgent') { $bg='bg-red-100'; $txt='text-red-600'; $icon='warning'; }
                        elseif($notif['type'] == 'warning') { $bg='bg-amber-100'; $txt='text-amber-600'; $icon='campaign'; }
                        else { $bg='bg-blue-100'; $txt='text-blue-600'; $icon='info'; }
                    ?>
                    <div class="px-5 py-4 hover:bg-slate-50 transition-colors flex gap-3 group">
                        <div class="w-8 h-8 rounded-full <?= $bg ?> <?= $txt ?> flex items-center justify-center shrink-0 mt-0.5 border border-white group-hover:brightness-95 transition-all">
                            <span class="material-symbols-outlined text-[16px]"><?= $icon ?></span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 mb-1"><?= htmlspecialchars($notif['title']) ?></p>
                            <p class="text-[11px] font-medium text-slate-500 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($notif['message']) ?></p>
                            <p class="text-[9px] font-bold text-slate-400 mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[10px]">person</span> <?= htmlspecialchars($notif['sender_name']) ?> &bull; <?= date('d M, H:i', strtotime($notif['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="px-5 py-8 text-center text-slate-400 flex flex-col items-center">
                        <span class="material-symbols-outlined text-3xl mb-2 opacity-50">notifications_paused</span>
                        <p class="text-xs font-medium">Belum ada pengumuman saat ini.</p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="relative flex items-center pl-2 sm:pl-4 h-10">
          <button onclick="toggleDropdown('profileDropdown')" class="flex items-center gap-2 hover:opacity-80 transition-opacity focus:outline-none group">
            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 shrink-0 group-hover:bg-slate-200 transition-colors">
              <span class="material-symbols-outlined text-[18px]">person</span>
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-xs font-bold text-slate-800 leading-tight"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></p>
            </div>
          </button>
          
          <div id="profileDropdown" class="dropdown-menu hidden absolute right-0 top-12 mt-2 w-56 bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 overflow-hidden z-50">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
              <p class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></p>
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-1"><?= htmlspecialchars($user_role) ?></p>
            </div>
            <div class="py-2 flex flex-col">
              
              <?php if($is_executive): ?>
                  <a href="pengaturan.php" class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">settings</span> Pengaturan Sistem
                  </a>
                  <a href="tim.php" class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">group</span> Manajemen Tim
                  </a>
              <?php else: ?>
                  <a href="profil.php" class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">account_circle</span> Profil Saya
                  </a>
              <?php endif; ?>

              <div class="h-px bg-slate-100 my-1 mx-5"></div>
              <button type="button" onclick="confirmAction('Akhiri Sesi?', 'Anda akan keluar dari portal operasional.', 'warning', 'logout.php', 'Ya, Keluar', '#eab308')" class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-amber-600 hover:bg-amber-50 transition-colors">
                <span class="material-symbols-outlined text-[18px]">logout</span> Keluar Sistem
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
      <div class="w-full">
        <?php
}
function renderFooter() {
?>
      </div>
    </main>

  </div>

  <script>
  function startClock() {
    const clock = document.getElementById('liveClock');
    if(!clock) return;
    
    const updateTime = () => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour12: false }).replace(/:/g, '.') + ' WIB';
        clock.innerText = timeStr;
    };
    
    updateTime(); 
    setInterval(updateTime, 1000); 
  }

  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('mobileOverlay').classList.toggle('hidden');
  }

  function toggleDropdown(id) {
    const el = document.getElementById(id);
    const isHidden = el.classList.contains('hidden');
    
    document.querySelectorAll('.dropdown-menu').forEach(d => {
        if(d.id !== id) d.classList.add('hidden');
    });
    
    if (isHidden) el.classList.remove('hidden');
    else el.classList.add('hidden');
  }

  function confirmAction(title, text, icon, url, btnText, btnColor) {
    Swal.fire({
      title: title, text: text, icon: icon, showCancelButton: true, confirmButtonColor: btnColor, cancelButtonColor: '#64748b', confirmButtonText: btnText, cancelButtonText: 'Batal', reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) { window.location.href = url; }
    })
  }
  
  window.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-menu') && !e.target.closest('[onclick^="toggleDropdown"]')) {
      document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
    }
  });
  </script>
</body>
</html>
<?php
}
?>