<?php

declare(strict_types=1);

require_once 'includes/auth.php';
require_once 'koneksi.php';

// Pagination setup
$itemsPerPage = 10;
$currentPage  = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$currentPage  = max(1, $currentPage);
$dataOffset   = ($currentPage - 1) * $itemsPerPage;

// Task filtering logic
$statusFilter = $_GET['status'] ?? 'Semua';
$validStatuses = ['Pending', 'Dalam Proses', 'Selesai'];
$whereClause  = "";
$queryParams  = [];

if ($statusFilter !== 'Semua' && in_array($statusFilter, $validStatuses)) {
    $whereClause = "WHERE status = ?";
    $queryParams[] = $statusFilter;
}

try {
    // Get total count for pagination metadata
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks $whereClause");
    $countStmt->execute($queryParams);
    $totalRecords = (int) $countStmt->fetchColumn();
    $totalPages   = (int) ceil($totalRecords / $itemsPerPage);

    // Fetch paginated task list
    $sql = "SELECT * FROM tasks $whereClause ORDER BY id DESC LIMIT $itemsPerPage OFFSET $dataOffset";
    $taskStmt = $pdo->prepare($sql);
    $taskStmt->execute($queryParams);
    $taskList = $taskStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Task List Fetch Error: " . $e->getMessage());
    $taskList = [];
    $totalPages = 1;
    $totalRecords = 0;
}

require_once 'includes/layout.php';
renderHeader('Daftar Tugas');
?>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 min-w-0">
  <div class="flex-1 min-w-0">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight truncate">Daftar Penugasan</h1>
    <p class="text-sm text-slate-500 mt-2 truncate">Pusat kendali operasional tim. (Total: <?= $totalRecords ?> Data)</p>
  </div>
  <div class="relative w-full md:w-80 shrink-0">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
    <input type="text" id="searchInput" placeholder="Pencarian cepat..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all">
  </div>
</div>

<div class="flex flex-wrap gap-2 mb-6 min-w-0">
  <?php 
    $filterOptions = ['Semua', 'Pending', 'Dalam Proses', 'Selesai'];
    foreach($filterOptions as $option): 
        $isActive = ($statusFilter === $option);
        $buttonStyle = $isActive 
            ? 'bg-slate-900 text-white shadow-md border-slate-900' 
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
    ?>
  <a href="?status=<?= urlencode($option) ?>" class="px-5 py-2.5 text-xs font-bold rounded-xl border transition-colors <?= $buttonStyle ?> whitespace-nowrap">
    <?= $option ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-w-0 flex flex-col">
  <div class="overflow-x-auto w-full">
    <table class="w-full text-left whitespace-nowrap min-w-[900px] table-fixed">
      <thead class="bg-blue-600 text-white border-b border-blue-700">
        <tr>
          <th class="w-[40%] px-6 py-4 text-[10px] font-extrabold uppercase tracking-widest text-left">Informasi Klien</th>
          <th class="w-[15%] px-4 py-4 text-[10px] font-extrabold uppercase tracking-widest text-center">Prioritas</th>
          <th class="w-[15%] px-4 py-4 text-[10px] font-extrabold uppercase tracking-widest text-center">Tenggat</th>
          <th class="w-[15%] px-4 py-4 text-[10px] font-extrabold uppercase tracking-widest text-center">Status</th>
          <th class="w-[15%] px-4 py-4 text-[10px] font-extrabold uppercase tracking-widest text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (!empty($taskList)): foreach ($taskList as $task): ?>
        <tr class="task-row hover:bg-slate-50/50 transition-colors group">
          <td class="px-6 py-5 text-left">
            <div class="flex flex-col">
              <div class="flex items-center gap-2 mb-1.5">
                <span class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition-colors truncate max-w-[250px]"><?= htmlspecialchars($task['nama_klien']) ?></span>
                <span class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded-md shadow-sm">#<?= sprintf('%04d', $task['id']) ?></span>
              </div>
              <div class="flex items-center gap-2">
                <p class="text-xs font-medium text-slate-500 truncate max-w-[280px]" title="<?= htmlspecialchars($task['deskripsi']) ?>"><?= htmlspecialchars($task['deskripsi']) ?></p>
                <span class="text-slate-300">&bull;</span>
                <span class="text-[11px] font-bold text-slate-400 flex items-center gap-1 shrink-0">
                  <span class="material-symbols-outlined text-[12px]">person</span> <?= htmlspecialchars($task['pic']) ?>
                </span>
              </div>
            </div>
          </td>
          <td class="px-4 py-5 text-center">
            <?php
                $priorityKey = strtolower($task['prioritas']);
                $priorityConfig = match($priorityKey) {
                    'tinggi' => ['label' => 'High',   'css' => 'bg-red-100 text-red-800'],
                    'sedang' => ['label' => 'Medium', 'css' => 'bg-amber-100 text-amber-800'],
                    default  => ['label' => 'Low',    'css' => 'bg-slate-100 text-slate-700'],
                };
            ?>
            <div class="flex justify-center">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest <?= $priorityConfig['css'] ?> leading-none shadow-sm">
                  <?= $priorityConfig['label'] ?>
                </span>
            </div>
          </td>
          <td class="px-4 py-5 text-center">
            <?php if(!empty($task['due_date'])): ?>
            <span class="inline-flex items-center justify-center text-xs font-bold text-slate-600 gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span> 
                <?= date('d M Y', strtotime($task['due_date'])) ?>
            </span>
            <?php else: ?>
            <span class="text-xs font-medium text-slate-400 italic">Belum diatur</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-5 text-center">
            <?php
                $statusKey = strtolower($task['status']);
                if ($statusKey === 'selesai') { 
                    $statusBadge = 'bg-emerald-100 text-emerald-800'; 
                    $statusIcon  = '<svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
                } elseif (str_contains($statusKey, 'proses')) { 
                    $statusBadge = 'bg-blue-100 text-blue-800'; 
                    $statusIcon  = '<svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 0 0 0-18z" fill="currentColor"/></svg>';
                } else { 
                    $statusBadge = 'bg-slate-100 text-slate-700'; 
                    $statusIcon  = '<svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" stroke-dasharray="4 4"/></svg>';
                }
            ?>
            <div class="flex justify-center">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold <?= $statusBadge ?> leading-none shadow-sm">
                  <?= $statusIcon ?> <?= htmlspecialchars($task['status']) ?>
                </span>
            </div>
          </td>
          <td class="px-4 py-5">
            <div class="flex items-center justify-center gap-2 lg:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <a href="edit.php?id=<?= $task['id'] ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-200 hover:shadow-sm" title="Edit Data">
                <span class="material-symbols-outlined text-[20px]">edit</span>
              </a>
              <?php if($task['status'] !== 'Selesai'): ?>
              <button type="button" onclick="confirmAction('Tandai Selesai?', 'Tugas ini akan dipindah ke status tuntas.', 'question', 'selesai.php?id=<?= $task['id'] ?>', 'Selesaikan', '#10b981')" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all border border-transparent hover:border-emerald-200 hover:shadow-sm" title="Tandai Selesai">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
              </button>
              <?php endif; ?>
              <?php if(isset($_SESSION['role']) && $_SESSION['role'] !== 'Staff'): ?>
              <button type="button" onclick="confirmAction('Hapus Permanen?', 'Data operasional proyek ini akan dimusnahkan. Lanjutkan?', 'error', 'hapus.php?id=<?= $task['id'] ?>', 'Hapus Data', '#ef4444')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all border border-transparent hover:border-red-200 hover:shadow-sm" title="Hapus Data">
                <span class="material-symbols-outlined text-[20px]">delete</span>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="5" class="px-6 py-20 text-center text-slate-500">
            <span class="material-symbols-outlined text-5xl mb-3 opacity-20 block">inbox</span>
            <p class="text-sm font-medium">Tabel Data Kosong</p>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($totalPages > 1): ?>
  <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Halaman <?= $currentPage ?> dari <?= $totalPages ?></span>
    <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
      <?php if($currentPage > 1): ?>
      <a href="?status=<?= urlencode($statusFilter) ?>&page=<?= $currentPage - 1 ?>" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-[16px]">chevron_left</span> Prev
      </a>
      <?php endif; ?>
      <?php if($currentPage < $totalPages): ?>
      <a href="?status=<?= urlencode($statusFilter) ?>&page=<?= $currentPage + 1 ?>" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors flex items-center gap-1">
        Next <span class="material-symbols-outlined text-[16px]">chevron_right</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
/**
 * Simple client-side search filter
 * Digunakan untuk filter cepat record yang sudah ter-load di halaman aktif
 */
document.getElementById('searchInput').addEventListener('input', e => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('.task-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
<?php renderFooter(); ?>