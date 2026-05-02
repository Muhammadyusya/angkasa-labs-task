<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

$p_tasks = []; $w_tasks = []; $d_tasks = [];
try {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
    while ($r = $stmt->fetch()) {
        $s = strtolower(trim($r['status']));
        if ($s == 'pending') $p_tasks[] = $r; elseif ($s == 'selesai') $d_tasks[] = $r; else $w_tasks[] = $r;
    }
} catch (PDOException $e) {}

require_once 'includes/layout.php';
renderHeader('Board Proses');
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 sm:mb-8 min-w-0">
  <div class="flex-1 min-w-0 flex flex-col justify-center">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Board Proses Tim</h1>
    <p class="text-sm sm:text-base text-slate-500 mt-1 leading-normal">Geser kartu untuk update status, atau <span class="font-bold text-blue-600 cursor-pointer">Klik Kartu</span> untuk melihat detail dan riwayat.</p>
  </div>
  
  <div class="flex items-center shrink-0 w-full md:w-auto">
    <a href="tambah_tugas.php" class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm active:scale-95">
      <span class="material-symbols-outlined text-[18px]">add</span> Tugas Baru
    </a>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-start min-w-0 pb-10">

  <!-- KOLOM 1: PENDING (SOLID PASTEL AMBER) -->
  <div class="flex flex-col bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 min-h-[500px] shadow-sm">
    <div class="flex justify-between items-center mb-5 px-1">
      <h3 class="text-[11px] font-extrabold text-amber-700 uppercase tracking-widest flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Pending
      </h3>
      <span id="count-pending" class="px-2.5 py-0.5 rounded-md bg-white border border-amber-200 text-amber-800 text-[10px] font-extrabold shadow-sm"><?= count($p_tasks) ?></span>
    </div>
    
    <div id="board-pending" data-status="Pending" class="space-y-3 flex-1 min-h-[200px]">
      <?php foreach($p_tasks as $t): ?>
      <div onclick="showDetail(this)" data-id="<?= $t['id'] ?>" data-title="<?= htmlspecialchars($t['nama_klien']) ?>" data-desc="<?= htmlspecialchars($t['deskripsi']) ?>" data-pic="<?= htmlspecialchars($t['pic']) ?>" data-prio="<?= htmlspecialchars($t['prioritas']) ?>" data-updated="<?= htmlspecialchars($t['updated_by'] ?? 'System') ?>" data-time="<?= htmlspecialchars($t['updated_at'] ?? 'Belum ada') ?>" class="task-card bg-white border border-slate-200 border-l-4 border-l-amber-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer group flex flex-col gap-2 min-w-0">
        <div class="card-header flex justify-between items-start gap-3">
          <h4 class="task-title text-sm font-bold text-slate-900 leading-snug break-words"><?= htmlspecialchars($t['nama_klien']) ?></h4>
          <div class="indicator-wrapper shrink-0 mt-0.5">
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-50 border border-slate-200 text-slate-400">#<?= sprintf('%04d', $t['id']) ?></span>
          </div>
        </div>
        <p class="task-desc text-[11px] font-medium text-slate-500 line-clamp-2 leading-relaxed mb-3"><?= htmlspecialchars($t['deskripsi']) ?></p>
        <div class="flex justify-between items-center mt-auto pt-2 border-t border-slate-50">
          <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-500">
            <span class="material-symbols-outlined text-[14px]">person</span> <?= htmlspecialchars($t['pic']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- KOLOM 2: DALAM PROSES (SOLID PASTEL BLUE) -->
  <div class="flex flex-col bg-blue-50 border border-blue-200 rounded-2xl p-4 sm:p-5 min-h-[500px] shadow-sm">
    <div class="flex justify-between items-center mb-5 px-1">
      <h3 class="text-[11px] font-extrabold text-blue-700 uppercase tracking-widest flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Dalam Proses
      </h3>
      <span id="count-proses" class="px-2.5 py-0.5 rounded-md bg-white border border-blue-200 text-blue-800 text-[10px] font-extrabold shadow-sm"><?= count($w_tasks) ?></span>
    </div>
    
    <div id="board-proses" data-status="Dalam Proses" class="space-y-3 flex-1 min-h-[200px]">
      <?php foreach($w_tasks as $t): ?>
      <div onclick="showDetail(this)" data-id="<?= $t['id'] ?>" data-title="<?= htmlspecialchars($t['nama_klien']) ?>" data-desc="<?= htmlspecialchars($t['deskripsi']) ?>" data-pic="<?= htmlspecialchars($t['pic']) ?>" data-prio="<?= htmlspecialchars($t['prioritas']) ?>" data-updated="<?= htmlspecialchars($t['updated_by'] ?? 'System') ?>" data-time="<?= htmlspecialchars($t['updated_at'] ?? 'Belum ada') ?>" class="task-card bg-white border border-slate-200 border-l-4 border-l-blue-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer group flex flex-col gap-2 min-w-0">
        <div class="card-header flex justify-between items-start gap-3">
          <h4 class="task-title text-sm font-bold text-slate-900 leading-snug break-words"><?= htmlspecialchars($t['nama_klien']) ?></h4>
          <div class="indicator-wrapper shrink-0 mt-0.5">
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-50 border border-slate-200 text-slate-400">#<?= sprintf('%04d', $t['id']) ?></span>
          </div>
        </div>
        <p class="task-desc text-[11px] font-medium text-slate-500 line-clamp-2 leading-relaxed mb-3"><?= htmlspecialchars($t['deskripsi']) ?></p>
        <div class="flex justify-between items-center mt-auto pt-2 border-t border-slate-50">
          <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-500">
            <span class="material-symbols-outlined text-[14px]">person</span> <?= htmlspecialchars($t['pic']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- KOLOM 3: SELESAI (SOLID PASTEL EMERALD) -->
  <div class="flex flex-col bg-emerald-50 border border-emerald-200 rounded-2xl p-4 sm:p-5 min-h-[500px] shadow-sm">
    <div class="flex justify-between items-center mb-5 px-1">
      <h3 class="text-[11px] font-extrabold text-emerald-700 uppercase tracking-widest flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai
      </h3>
      <span id="count-selesai" class="px-2.5 py-0.5 rounded-md bg-white border border-emerald-200 text-emerald-800 text-[10px] font-extrabold shadow-sm"><?= count($d_tasks) ?></span>
    </div>
    
    <div id="board-selesai" data-status="Selesai" class="space-y-3 flex-1 min-h-[200px]">
      <?php foreach($d_tasks as $t): ?>
      <div onclick="showDetail(this)" data-id="<?= $t['id'] ?>" data-title="<?= htmlspecialchars($t['nama_klien']) ?>" data-desc="<?= htmlspecialchars($t['deskripsi']) ?>" data-pic="<?= htmlspecialchars($t['pic']) ?>" data-prio="<?= htmlspecialchars($t['prioritas']) ?>" data-updated="<?= htmlspecialchars($t['updated_by'] ?? 'System') ?>" data-time="<?= htmlspecialchars($t['updated_at'] ?? 'Belum ada') ?>" class="task-card bg-white border border-slate-200 border-l-4 border-l-emerald-500 rounded-xl p-4 shadow-sm opacity-80 hover:opacity-100 transition-all cursor-pointer group flex flex-col gap-2 min-w-0">
        <div class="card-header flex justify-between items-start gap-3">
          <h4 class="task-title text-sm font-bold text-slate-500 line-through leading-snug break-words"><?= htmlspecialchars($t['nama_klien']) ?></h4>
          <div class="indicator-wrapper shrink-0 mt-0.5">
            <span class="text-emerald-500 flex items-center"><span class="material-symbols-outlined text-[18px]">check_circle</span></span>
          </div>
        </div>
        <p class="task-desc text-[11px] font-medium text-slate-400 line-through line-clamp-2 leading-relaxed mb-3"><?= htmlspecialchars($t['deskripsi']) ?></p>
        <div class="flex justify-between items-center mt-auto pt-2 border-t border-slate-50">
          <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-400">
            <span class="material-symbols-outlined text-[14px]">person</span> <?= htmlspecialchars($t['pic']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<script>
function showDetail(element) {
  const id = element.getAttribute('data-id');
  const title = element.getAttribute('data-title');
  const desc = element.getAttribute('data-desc');
  const pic = element.getAttribute('data-pic');
  let prio = element.getAttribute('data-prio').toLowerCase();
  const updatedBy = element.getAttribute('data-updated');
  const updatedTime = element.getAttribute('data-time');

  let prioLabel = 'LOW'; let prioClass = 'bg-slate-100 text-slate-600 border-slate-200';
  if(prio === 'tinggi') { prioLabel = 'HIGH'; prioClass = 'bg-red-50 text-red-600 border-red-100'; }
  else if(prio === 'sedang') { prioLabel = 'MEDIUM'; prioClass = 'bg-amber-50 text-amber-600 border-amber-100'; }

  Swal.fire({
    html: `
      <div class="text-left font-sans mt-2">
          <div class="flex justify-between items-center mb-6">
              <span class="px-2.5 py-1 bg-slate-50 text-slate-500 text-[11px] font-mono font-bold rounded-md border border-slate-200">#AL-${String(id).padStart(4, '0')}</span>
              <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-widest rounded-md border ${prioClass}">${prioLabel}</span>
          </div>
          <h2 class="text-xl font-extrabold text-slate-900 mb-2 leading-tight">${title}</h2>
          <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-500 mb-6 pb-6 border-b border-slate-100">
              <span class="material-symbols-outlined text-[16px]">person</span> PIC: <span class="text-slate-800">${pic}</span>
          </div>
          <div class="mb-8">
              <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Spesifikasi Pekerjaan</h4>
              <p class="text-sm font-medium text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-xl border border-slate-100 whitespace-pre-wrap">${desc}</p>
          </div>
          <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 p-4 rounded-xl">
              <span class="material-symbols-outlined text-[20px] text-slate-400">history</span>
              <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Audit Trail</p>
                  <p class="text-[11px] font-medium text-slate-600 mt-0.5">Last updated by <b class="text-slate-900">${updatedBy}</b> <br> at ${updatedTime}</p>
              </div>
          </div>
      </div>
    `,
    showCancelButton: true,
    showConfirmButton: true,
    confirmButtonText: '<span class="material-symbols-outlined text-[16px] align-middle mr-1">edit</span> Edit Tugas',
    cancelButtonText: 'Tutup',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    width: '450px'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'edit.php?id=' + id;
    }
  });
}

document.addEventListener("DOMContentLoaded", function() {
  const columns = [
    { id: 'board-pending', countId: 'count-pending' },
    { id: 'board-proses', countId: 'count-proses' },
    { id: 'board-selesai', countId: 'count-selesai' }
  ];

  columns.forEach(col => {
    const el = document.getElementById(col.id);
    if (el) {
      new Sortable(el, {
        group: 'kanban',
        animation: 250,
        ghostClass: 'opacity-30',
        dragClass: 'shadow-2xl',
        onEnd: function(evt) {
          const taskEl = evt.item;
          const taskId = taskEl.getAttribute('data-id');
          const toColumn = evt.to;
          const newStatus = toColumn.getAttribute('data-status');

          if (evt.from === evt.to) return;

          const titleEl = taskEl.querySelector('.task-title');
          const descEl = taskEl.querySelector('.task-desc');
          const indicatorWrapper = taskEl.querySelector('.indicator-wrapper');

          // DOM Cleanup (Border & Teks)
          taskEl.classList.remove('border-l-amber-500', 'border-l-blue-500', 'border-l-emerald-500', 'opacity-80');
          titleEl.classList.remove('line-through', 'text-slate-500');
          titleEl.classList.add('text-slate-900');
          descEl.classList.remove('line-through', 'text-slate-400');
          descEl.classList.add('text-slate-500');

          // Terapkan Warna Kolom Baru
          if (newStatus === 'Pending') {
              taskEl.classList.add('border-l-amber-500');
              indicatorWrapper.innerHTML = `<span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-50 border border-slate-200 text-slate-400">#${String(taskId).padStart(4, '0')}</span>`;
          } 
          else if (newStatus === 'Dalam Proses') {
              taskEl.classList.add('border-l-blue-500');
              indicatorWrapper.innerHTML = `<span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-50 border border-slate-200 text-slate-400">#${String(taskId).padStart(4, '0')}</span>`;
          } 
          else if (newStatus === 'Selesai') {
              taskEl.classList.add('border-l-emerald-500', 'opacity-80');
              titleEl.classList.remove('text-slate-900');
              titleEl.classList.add('line-through', 'text-slate-500');
              descEl.classList.remove('text-slate-500');
              descEl.classList.add('line-through', 'text-slate-400');
              indicatorWrapper.innerHTML = '<span class="text-emerald-500 flex items-center"><span class="material-symbols-outlined text-[18px]">check_circle</span></span>';
          }

          document.getElementById(col.countId).innerText = evt.from.children.length;
          columns.find(c => c.id === evt.to.id).countId &&
            (document.getElementById(columns.find(c => c.id === evt.to.id).countId).innerText = evt.to.children.length);

          fetch('api_update_status.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id: taskId, status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const Toast = Swal.mixin({
                  toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000
                });
                Toast.fire({ icon: 'success', title: data.message });
                taskEl.setAttribute('data-updated', '<?= $_SESSION['nama'] ?? 'Anda' ?>');
                taskEl.setAttribute('data-time', 'Baru saja');
              }
            });
        }
      });
    }
  });
});
</script>

<?php renderFooter(); ?>