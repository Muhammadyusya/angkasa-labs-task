<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

$filter_date = $_GET['filter_date'] ?? '';

try {
    $stmt_stats = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai, SUM(CASE WHEN status = 'Dalam Proses' THEN 1 ELSE 0 END) as proses, SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending FROM tasks");
    $stats = $stmt_stats->fetch();
    
    $chart_total = $stats['total'] ?? 0;
    $chart_selesai = $stats['selesai'] ?? 0; 
    $chart_proses = $stats['proses'] ?? 0; 
    $chart_pending = $stats['pending'] ?? 0;
    
    $stmt_urgent = $pdo->query("SELECT * FROM tasks WHERE prioritas = 'Tinggi' AND status != 'Selesai' ORDER BY id DESC LIMIT 5");
    $urgent_tasks = $stmt_urgent->fetchAll();

    $stmt_dates = $pdo->query("SELECT due_date, COUNT(*) as count FROM tasks WHERE status != 'Selesai' AND due_date IS NOT NULL GROUP BY due_date");
    $deadline_dates = $stmt_dates->fetchAll(PDO::FETCH_KEY_PAIR);
    $deadline_json = json_encode(array_keys($deadline_dates));

    $agenda_query = "SELECT * FROM tasks WHERE due_date IS NOT NULL AND status != 'Selesai' ";
    $params = [];
    if (!empty($filter_date)) {
        $agenda_query .= "AND due_date = ? ";
        $params[] = $filter_date;
    } else {
        $agenda_query .= "AND due_date >= CURRENT_DATE ";
    }
    $agenda_query .= "ORDER BY due_date ASC LIMIT 5";
    $stmt_agenda = $pdo->prepare($agenda_query);
    $stmt_agenda->execute($params);
    $agendas = $stmt_agenda->fetchAll();

    $stmt_activity = $pdo->query("SELECT id, updated_by, updated_at, status FROM tasks WHERE updated_at IS NOT NULL ORDER BY updated_at DESC LIMIT 6");
    $recent_activities = $stmt_activity->fetchAll();

} catch (PDOException $e) {}

require_once 'includes/layout.php';
renderHeader('Beranda');
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 sm:mb-8 min-w-0">
  <div class="flex-1 min-w-0 flex flex-col justify-center">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['nama'] ?? 'Admin'))[0]) ?>.</h1>
    <p class="text-sm sm:text-base text-slate-500 mt-1">Tinjau perkembangan proyek strategis Anda melalui metrik performa.</p>
  </div>
  
  <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 w-full md:w-auto">
    <button onclick="toggleCalendarModal()" class="relative flex items-center justify-between bg-white border border-slate-300 rounded-xl shadow-sm px-5 py-2.5 w-full sm:w-auto group hover:border-blue-500 hover:shadow-md transition-all focus:outline-none">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-600 text-[20px]">calendar_month</span>
        <span class="text-sm font-bold text-slate-700"><?= $filter_date ? date('d M Y', strtotime($filter_date)) : 'Lihat Kalender' ?></span>
      </div>
    </button>
    <?php if($filter_date): ?>
      <a href="index.php" class="p-2.5 bg-red-50 text-red-600 rounded-xl border border-red-100 hover:bg-red-100 transition-colors flex items-center justify-center" title="Hapus Filter"><span class="material-symbols-outlined text-[20px]">close</span></a>
    <?php endif; ?>
    
    <a href="export_excel.php" target="_blank" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 bg-blue-600 border border-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/20 active:scale-95">
      <span class="material-symbols-outlined text-[18px]">download</span> Laporan
    </a>
  </div>
</div>

<div id="calendarModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleCalendarModal()"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="calendarContent">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
      <h3 class="font-bold text-slate-800" id="calMonthYear">Bulan Tahun</h3>
      <div class="flex items-center gap-2">
        <button onclick="changeMonth(-1)" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 flex items-center justify-center"><span class="material-symbols-outlined">chevron_left</span></button>
        <button onclick="changeMonth(1)" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 flex items-center justify-center"><span class="material-symbols-outlined">chevron_right</span></button>
      </div>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-7 gap-1 mb-2 text-center text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
      </div>
      <div id="calGrid" class="grid grid-cols-7 gap-1 text-center"></div>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-[10px] font-bold text-slate-500 flex justify-center gap-4">
      <span class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-red-500"></div> Ada Deadline</span>
      <span class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-blue-100 border border-blue-500"></div> Hari Ini</span>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 min-w-0">
  <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-b-slate-700 min-w-0 flex flex-col justify-between group hover:border-slate-300 transition-colors">
    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Total Tugas</p>
    <p class="text-4xl font-black text-slate-900 group-hover:text-blue-600 transition-colors"><?= $stats['total'] ?? 0 ?></p>
  </div>
  <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-b-emerald-500 min-w-0 flex flex-col justify-between group hover:border-slate-300 transition-colors">
    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Selesai</p>
    <p class="text-4xl font-black text-emerald-600"><?= $stats['selesai'] ?? 0 ?></p>
  </div>
  <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-b-blue-500 min-w-0 flex flex-col justify-between group hover:border-slate-300 transition-colors">
    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Dalam Proses</p>
    <p class="text-4xl font-black text-blue-600"><?= $stats['proses'] ?? 0 ?></p>
  </div>
  <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-b-amber-500 min-w-0 flex flex-col justify-between group hover:border-slate-300 transition-colors">
    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Pending</p>
    <p class="text-4xl font-black text-amber-500"><?= $stats['pending'] ?? 0 ?></p>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 items-start min-w-0">

  <div class="xl:col-span-2 flex flex-col gap-6 lg:gap-8 min-w-0">
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-w-0 flex flex-col">
      <div class="px-6 py-5 flex justify-between items-center bg-white">
        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
          <span class="material-symbols-outlined text-red-500">warning</span> Atensi Eksekutif
        </h3>
        <a href="daftar_tugas.php" class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors shrink-0 flex items-center">Lihat Semua</a>
      </div>
      
      <div class="w-full flex flex-col min-w-0">
        <div class="hidden sm:grid sm:grid-cols-12 bg-blue-600 border-y border-blue-700">
          <div class="sm:col-span-9 px-6 py-3.5 text-[10px] font-extrabold uppercase tracking-widest text-white flex items-center">Klien & Ringkasan</div>
          <div class="sm:col-span-3 px-6 py-3.5 text-[10px] font-extrabold uppercase tracking-widest text-white flex items-center justify-end">Aksi Cepat</div>
        </div>
        <div class="divide-y divide-slate-100 min-w-0 bg-white">
          <?php if(!empty($urgent_tasks)): foreach($urgent_tasks as $task): ?>
          <div class="flex flex-col sm:grid sm:grid-cols-12 items-center px-6 py-4 hover:bg-blue-50/30 transition-colors group gap-3 sm:gap-0 min-w-0">
            <div class="w-full sm:col-span-9 min-w-0 flex flex-col justify-center">
              <p class="font-bold text-sm text-slate-900 group-hover:text-blue-600 transition-colors break-words"><?= htmlspecialchars($task['nama_klien'] ?? '') ?></p>
              <div class="flex items-center gap-2 mt-1 min-w-0">
                <p class="text-xs font-medium text-slate-500 truncate min-w-0"><?= htmlspecialchars($task['deskripsi'] ?? '') ?></p>
                <span class="text-slate-300 shrink-0">&bull;</span>
                <p class="text-[10px] sm:text-[11px] text-slate-400 font-bold flex items-center gap-1 shrink-0">
                  <span class="material-symbols-outlined text-[12px]">person</span> <?= htmlspecialchars($task['pic'] ?? '') ?>
                </p>
              </div>
            </div>
            <div class="w-full sm:col-span-3 sm:text-right shrink-0 flex sm:justify-end items-center">
              <button type="button" onclick="confirmAction('Selesaikan Tugas?', 'Tugas ini akan dipindah ke status Selesai.', 'question', 'selesai.php?id=<?= $task['id'] ?>', 'Selesaikan', '#10b981')" class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-600 hover:text-emerald-700 hover:border-emerald-400 hover:bg-emerald-50 rounded-lg text-xs font-bold transition-all shadow-sm">
                <span class="material-symbols-outlined text-[16px]">check_circle</span> Selesaikan
              </button>
            </div>
          </div>
          <?php endforeach; else: ?>
          <div class="px-6 py-12 text-center text-sm font-medium text-slate-400">Semua tugas prioritas tinggi telah tuntas.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 min-w-0">
      <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
          <span class="material-symbols-outlined text-amber-500">schedule</span> 
          <?= $filter_date ? 'Jadwal: ' . date('d M Y', strtotime($filter_date)) : 'Tenggat Mendekati' ?>
        </h3>
        <?php if($filter_date): ?>
          <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md text-[10px] font-bold uppercase tracking-widest border border-amber-200 flex items-center">Filtered</span>
        <?php endif; ?>
      </div>
      
      <div class="space-y-4">
        <?php if(!empty($agendas)): foreach($agendas as $agenda): ?>
        <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all min-w-0 group">
          <div class="w-14 h-14 rounded-xl bg-white text-slate-700 flex flex-col items-center justify-center shrink-0 border border-slate-200 shadow-sm group-hover:border-blue-400 group-hover:text-blue-600 transition-colors">
            <span class="text-[9px] font-extrabold uppercase"><?= date('M', strtotime($agenda['due_date'])) ?></span>
            <span class="text-xl font-black leading-none mt-1"><?= date('d', strtotime($agenda['due_date'])) ?></span>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-slate-900 break-words group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($agenda['nama_klien']) ?></h4>
            <p class="text-xs text-slate-500 truncate mt-1"><?= htmlspecialchars($agenda['deskripsi']) ?></p>
            <div class="mt-2 flex gap-2 items-center">
                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-white border border-slate-200 text-slate-600 shadow-sm items-center">PIC: <?= htmlspecialchars($agenda['pic']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200 flex flex-col items-center">
            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">event_available</span>
            <p class="text-sm font-medium text-slate-500">Tidak ada jadwal tenggat ditemukan.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <div class="space-y-6 lg:space-y-8 min-w-0">
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col items-center min-w-0">
      <h3 class="font-bold text-slate-900 text-base w-full text-left mb-6">Metrik Penyelesaian</h3>
      <div class="relative w-full h-[220px] flex justify-center items-center">
        <canvas id="statusChart"></canvas>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 min-w-0 flex flex-col">
      <div class="flex items-center justify-between mb-6">
        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-600">history</span> Riwayat Aktivitas
        </h3>
      </div>
      
      <div class="space-y-5 relative before:absolute before:inset-0 before:ml-2.5 before:-translate-x-px before:h-full before:w-0.5 before:bg-slate-100">
        <?php if(!empty($recent_activities)): foreach($recent_activities as $act): ?>
        <div class="relative flex items-start gap-4 group">
          <div class="w-5 h-5 rounded-full bg-white border-2 border-blue-500 shadow-sm flex items-center justify-center shrink-0 z-10">
            <div class="w-1.5 h-1.5 rounded-full bg-blue-600"></div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-slate-600 leading-relaxed">
              <span class="font-bold text-slate-900"><?= htmlspecialchars($act['updated_by'] ?? 'System') ?></span> memodifikasi status
              <span class="font-bold text-blue-600">AL-<?= sprintf('%04d', $act['id'] ?? 0) ?></span>
            </p>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-1"><?= date('d M Y, H:i', strtotime($act['updated_at'] ?? 'now')) ?></p>
          </div>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center text-xs font-medium text-slate-400 py-4">Belum ada aktivitas.</div>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const totalTasks = <?= $chart_total ?>;
  const selesaiTasks = <?= $chart_selesai ?>;
  const percentage = totalTasks > 0 ? Math.round((selesaiTasks / totalTasks) * 100) : 0;

  const centerTextPlugin = {
    id: 'centerText',
    beforeDraw: function(chart) {
      if (chart.config.options.elements.center) {
        var ctx = chart.ctx;
        var centerConfig = chart.config.options.elements.center;
        var fontStyle = centerConfig.fontStyle || 'Plus Jakarta Sans';
        var txt = centerConfig.text;
        var color = centerConfig.color || '#0f172a';
        
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
        var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
        
        ctx.font = "900 32px '" + fontStyle + "'";
        ctx.fillStyle = color;
        ctx.fillText(txt, centerX, centerY - 8);
        
        ctx.font = "800 10px '" + fontStyle + "'";
        ctx.fillStyle = "#64748b";
        ctx.fillText("SELESAI", centerX, centerY + 18);
      }
    }
  };

  new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Selesai', 'Dalam Proses', 'Pending'],
      datasets: [{
        data: [<?= $chart_selesai ?>, <?= $chart_proses ?>, <?= $chart_pending ?>],
        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
        borderWidth: 0,
        hoverOffset: 6
      }]
    },
    plugins: [centerTextPlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '80%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, padding: 20, usePointStyle: true, font: { size: 11, family: "'Plus Jakarta Sans', sans-serif", weight: '700' } }
        }
      },
      elements: { center: { text: percentage + '%', color: '#0f172a' } }
    }
  });
});

const deadlines = <?= $deadline_json ?>; 
let currentDate = new Date(); 

function renderCalendar() {
  const monthYear = document.getElementById("calMonthYear");
  const calGrid = document.getElementById("calGrid");
  calGrid.innerHTML = "";

  const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
  monthYear.innerText = monthNames[currentDate.getMonth()] + " " + currentDate.getFullYear();

  const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
  const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
  const today = new Date();

  for (let i = 0; i < firstDay; i++) {
    let emptyDiv = document.createElement("div");
    calGrid.appendChild(emptyDiv);
  }

  for (let i = 1; i <= daysInMonth; i++) {
    let dayDiv = document.createElement("a");
    let m = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    let d = i.toString().padStart(2, '0');
    let dateString = `${currentDate.getFullYear()}-${m}-${d}`;
    
    dayDiv.href = `index.php?filter_date=${dateString}`;
    dayDiv.innerText = i;
    dayDiv.className = "py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-blue-50 cursor-pointer relative transition-colors flex items-center justify-center";

    if (i === today.getDate() && currentDate.getMonth() === today.getMonth() && currentDate.getFullYear() === today.getFullYear()) {
      dayDiv.classList.add("bg-blue-50", "text-blue-700", "ring-1", "ring-blue-300");
    }

    if (deadlines.includes(dateString)) {
      let dot = document.createElement("div");
      dot.className = "absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-red-500";
      dayDiv.appendChild(dot);
      dayDiv.classList.add("font-extrabold");
    }
    calGrid.appendChild(dayDiv);
  }
}

function changeMonth(step) {
  currentDate.setMonth(currentDate.getMonth() + step);
  renderCalendar();
}

function toggleCalendarModal() {
  const modal = document.getElementById("calendarModal");
  const content = document.getElementById("calendarContent");
  if (modal.classList.contains("hidden")) {
    modal.classList.remove("hidden");
    setTimeout(() => {
      content.classList.remove("scale-95", "opacity-0");
      content.classList.add("scale-100", "opacity-100");
    }, 10);
    renderCalendar();
  } else {
    content.classList.remove("scale-100", "opacity-100");
    content.classList.add("scale-95", "opacity-0");
    setTimeout(() => { modal.classList.add("hidden"); }, 300);
  }
}
</script>
<?php renderFooter(); ?>