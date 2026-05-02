<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

// Logic Insert Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_klien = trim($_POST['nama_klien'] ?? '');
    $pic = trim($_POST['pic'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $prioritas = $_POST['prioritas'] ?? 'Sedang';
    $status = $_POST['status'] ?? 'Pending';
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $updated_by = $_SESSION['nama'] ?? 'System';

    if ($nama_klien && $pic && $deskripsi) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (nama_klien, deskripsi, pic, prioritas, status, due_date, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nama_klien, $deskripsi, $pic, $prioritas, $status, $due_date, $updated_by]);
            
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tugas baru berhasil diorbitkan.'];
            header("Location: daftar_tugas.php");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi parameter klien, PIC, dan deskripsi.";
    }
}

// Menarik data deadline untuk context visual di kalender
try {
    $stmt_dates = $pdo->query("SELECT due_date FROM tasks WHERE status != 'Selesai' AND due_date IS NOT NULL GROUP BY due_date");
    $deadline_dates = $stmt_dates->fetchAll(PDO::FETCH_COLUMN);
    $deadline_json = json_encode($deadline_dates);
} catch (PDOException $e) {
    $deadline_json = "[]";
}

require_once 'includes/layout.php';
renderHeader('Tambah Tugas');
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 sm:mb-8 min-w-0">
  <div class="flex-1 min-w-0 flex flex-col justify-center">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Entri Tugas Baru</h1>
    <p class="text-sm sm:text-base text-slate-500 mt-1 leading-normal">Lengkapi parameter operasional di bawah ini untuk memulai eksekusi.</p>
  </div>
</div>

<?php if(!empty($error)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-3">
    <span class="material-symbols-outlined text-red-500">error</span>
    <p class="text-sm font-bold text-red-700"><?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start min-w-0 w-full pb-10">
    
    <!-- KOLOM KIRI (Form Utama) -->
    <form method="POST" action="" class="flex-1 w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 min-w-0 flex flex-col gap-6 relative">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Klien -->
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Nama Klien / UMKM <span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[18px]">domain</span>
                    <input type="text" name="nama_klien" required placeholder="Nama entitas..." class="w-full h-12 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>
            </div>
            
            <!-- PIC -->
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">PIC Internal <span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[18px]">badge</span>
                    <input type="text" name="pic" required placeholder="Nama penanggung jawab..." class="w-full h-12 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Deskripsi Tugas <span class="text-red-500">*</span></label>
            <textarea name="deskripsi" required rows="4" placeholder="Jelaskan spesifikasi dan ruang lingkup pekerjaan secara detail..." class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
        </div>

        <!-- Baris Interaktif (Prioritas, Status, Tenggat) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
            
            <!-- Custom Select: Prioritas -->
            <div class="flex flex-col gap-2 relative" id="wrapper-prioritas">
                <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Prioritas</label>
                <input type="hidden" name="prioritas" id="val-prioritas" value="Sedang">
                <button type="button" onclick="toggleDropdownUI('drop-prioritas')" class="w-full h-12 px-4 bg-white border border-slate-200 rounded-xl flex items-center justify-between hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    <div id="ui-prioritas" class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-amber-50 text-amber-700 leading-none shadow-sm">MEDIUM</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">expand_more</span>
                </button>
                
                <div id="drop-prioritas" class="hidden absolute top-[70px] left-0 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-40 overflow-hidden">
                    <ul class="flex flex-col py-1">
                        <li onclick="setPrioritas('Rendah', 'bg-slate-100 text-slate-700', 'LOW')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 flex items-center">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-slate-100 text-slate-700 leading-none shadow-sm">LOW</span>
                        </li>
                        <li onclick="setPrioritas('Sedang', 'bg-amber-50 text-amber-700', 'MEDIUM')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 flex items-center">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-amber-50 text-amber-700 leading-none shadow-sm">MEDIUM</span>
                        </li>
                        <li onclick="setPrioritas('Tinggi', 'bg-red-50 text-red-600', 'HIGH')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer flex items-center">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-red-50 text-red-600 leading-none shadow-sm">HIGH</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Custom Select: Status -->
            <div class="flex flex-col gap-2 relative" id="wrapper-status">
                <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Status</label>
                <input type="hidden" name="status" id="val-status" value="Pending">
                <button type="button" onclick="toggleDropdownUI('drop-status')" class="w-full h-12 px-4 bg-white border border-slate-200 rounded-xl flex items-center justify-between hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    <div id="ui-status" class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 leading-none shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" stroke-dasharray="4 4"/></svg> Pending
                        </span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">expand_more</span>
                </button>
                
                <div id="drop-status" class="hidden absolute top-[70px] left-0 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-40 overflow-hidden">
                    <ul class="flex flex-col py-1">
                        <li onclick="setStatus('Pending', 'bg-slate-100 text-slate-700', '<svg class=\'w-3.5 h-3.5 mr-1.5\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'12\' cy=\'12\' r=\'9\' stroke-dasharray=\'4 4\'/></svg>')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 flex items-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 leading-none shadow-sm"><svg class="w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" stroke-dasharray="4 4"/></svg> Pending</span>
                        </li>
                        <li onclick="setStatus('Dalam Proses', 'bg-blue-100 text-blue-800', '<svg class=\'w-3.5 h-3.5 mr-1.5\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><circle cx=\'12\' cy=\'12\' r=\'9\'/><path d=\'M12 3v18a9 9 0 0 0 0-18z\' fill=\'currentColor\'/></svg>')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 flex items-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 leading-none shadow-sm"><svg class="w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 0 0 0-18z" fill="currentColor"/></svg> Dalam Proses</span>
                        </li>
                        <li onclick="setStatus('Selesai', 'bg-emerald-100 text-emerald-800', '<svg class=\'w-3.5 h-3.5 mr-1.5\' viewBox=\'0 0 24 24\' fill=\'currentColor\'><path d=\'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\'/></svg>')" class="px-4 py-3 hover:bg-slate-50 cursor-pointer flex items-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 leading-none shadow-sm"><svg class="w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg> Selesai</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Pemanggil Modal Kalender: Tenggat Waktu -->
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Tenggat Waktu</label>
                <input type="hidden" name="due_date" id="val-date" value="">
                <button type="button" onclick="toggleFormCalendar()" class="w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none group">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-400 text-[18px] group-hover:text-blue-500 transition-colors">calendar_month</span>
                        <span id="ui-date" class="text-sm font-bold text-slate-400">Pilih Tanggal</span>
                    </div>
                </button>
            </div>

        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 mt-4 pt-6 border-t border-slate-100">
            <a href="daftar_tugas.php" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">Batalkan</a>
            <button type="submit" class="inline-flex justify-center items-center gap-2 px-8 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/20 active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span> Simpan Tugas
            </button>
        </div>

    </form>

    <!-- KOLOM KANAN (Info Panel) -->
    <div class="w-full lg:w-80 shrink-0 flex flex-col gap-4">
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col gap-5">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Ringkasan Input</h3>
            
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
                <div class="flex flex-col gap-0.5 mt-0.5">
                    <h4 class="text-sm font-bold text-slate-900 leading-tight">Validasi Instan</h4>
                    <p class="text-xs font-medium text-slate-500 leading-relaxed">Tugas akan langsung diorbitkan ke dalam Kanban Board.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
                    <span class="material-symbols-outlined text-[20px]">notifications_active</span>
                </div>
                <div class="flex flex-col gap-0.5 mt-0.5">
                    <h4 class="text-sm font-bold text-slate-900 leading-tight">Audit Trail</h4>
                    <p class="text-xs font-medium text-slate-500 leading-relaxed">Nama Anda akan tercatat sebagai eksekutor pembuatan tiket ini.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL KALENDER CENTER (Identik dengan Beranda) -->
<div id="formCalendarModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleFormCalendar()"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="formCalendarContent">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
      <h3 class="font-bold text-slate-800 leading-none mt-0.5" id="calMonthYearForm">Bulan Tahun</h3>
      <div class="flex items-center gap-2">
        <button type="button" onclick="changeFormMonth(-1)" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 flex items-center justify-center"><span class="material-symbols-outlined">chevron_left</span></button>
        <button type="button" onclick="changeFormMonth(1)" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 flex items-center justify-center"><span class="material-symbols-outlined">chevron_right</span></button>
      </div>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-7 gap-1 mb-2 text-center text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
      </div>
      <div id="calGridForm" class="grid grid-cols-7 gap-1 text-center"></div>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-[10px] font-bold text-slate-500 flex justify-between items-center">
      <div class="flex gap-4">
          <span class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-red-500"></div> Ada Deadline</span>
          <span class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-blue-100 border border-blue-500"></div> Hari Ini</span>
      </div>
      <button type="button" onclick="clearDate()" class="text-red-500 hover:text-red-700 font-extrabold transition-colors">Hapus Tanggal</button>
    </div>
  </div>
</div>

<script>
// Toggle Custom Select
function toggleDropdownUI(id) {
    const el = document.getElementById(id);
    const isHidden = el.classList.contains('hidden');
    
    ['drop-prioritas', 'drop-status'].forEach(d => {
        if(d !== id) document.getElementById(d).classList.add('hidden');
    });

    if (isHidden) el.classList.remove('hidden');
    else el.classList.add('hidden');
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('#wrapper-prioritas') && !e.target.closest('#wrapper-status')) {
        document.getElementById('drop-prioritas').classList.add('hidden');
        document.getElementById('drop-status').classList.add('hidden');
    }
});

// Update UI
function setPrioritas(val, classColor, label) {
    document.getElementById('val-prioritas').value = val;
    document.getElementById('ui-prioritas').innerHTML = `<span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest ${classColor} leading-none shadow-sm">${label}</span>`;
    document.getElementById('drop-prioritas').classList.add('hidden');
}

function setStatus(val, classColor, svgIcon) {
    document.getElementById('val-status').value = val;
    document.getElementById('ui-status').innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${classColor} leading-none shadow-sm">${svgIcon} ${val}</span>`;
    document.getElementById('drop-status').classList.add('hidden');
}

// Data Deadlines dari PHP untuk Red Dots
const existingDeadlines = <?= $deadline_json ?>;

// Engine Modal Kalender
let formDate = new Date();
const formMonths = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

function renderFormCalendar() {
    const monthYear = document.getElementById("calMonthYearForm");
    const calGrid = document.getElementById("calGridForm");
    calGrid.innerHTML = "";

    monthYear.innerText = formMonths[formDate.getMonth()] + " " + formDate.getFullYear();

    const firstDay = new Date(formDate.getFullYear(), formDate.getMonth(), 1).getDay();
    const daysInMonth = new Date(formDate.getFullYear(), formDate.getMonth() + 1, 0).getDate();
    const today = new Date();
    const selectedVal = document.getElementById('val-date').value;

    for (let i = 0; i < firstDay; i++) {
        calGrid.appendChild(document.createElement("div"));
    }

    for (let i = 1; i <= daysInMonth; i++) {
        let dayDiv = document.createElement("a");
        let m = (formDate.getMonth() + 1).toString().padStart(2, '0');
        let d = i.toString().padStart(2, '0');
        let dateString = `${formDate.getFullYear()}-${m}-${d}`;
        
        dayDiv.innerText = i;
        dayDiv.className = "py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-blue-50 cursor-pointer relative transition-colors flex items-center justify-center";

        // Highlight Hari Ini
        if (i === today.getDate() && formDate.getMonth() === today.getMonth() && formDate.getFullYear() === today.getFullYear()) {
            dayDiv.classList.add("bg-blue-50", "text-blue-700", "ring-1", "ring-blue-300");
        }

        // Highlight Tanggal Terpilih
        if (dateString === selectedVal) {
            dayDiv.classList.add("bg-blue-600", "text-white");
            dayDiv.classList.remove("text-slate-700", "hover:bg-blue-50", "bg-blue-50", "text-blue-700", "ring-1", "ring-blue-300");
        }

        // Indikator Beban Kerja (Titik Merah)
        if (existingDeadlines.includes(dateString)) {
            let dot = document.createElement("div");
            dot.className = "absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-red-500";
            if (dateString === selectedVal) dot.classList.add("bg-white"); // Ubah putih jika sedang di-select
            dayDiv.appendChild(dot);
            dayDiv.classList.add("font-extrabold");
        }

        // Event Onclick: Set value, tutup modal
        dayDiv.onclick = function() {
            document.getElementById('val-date').value = dateString;
            document.getElementById('ui-date').innerText = `${d} ${formMonths[formDate.getMonth()].slice(0,3)} ${formDate.getFullYear()}`;
            document.getElementById('ui-date').classList.add('text-slate-900');
            document.getElementById('ui-date').classList.remove('text-slate-400');
            toggleFormCalendar();
            renderFormCalendar(); // re-render untuk update UI
        };

        calGrid.appendChild(dayDiv);
    }
}

function changeFormMonth(step) {
    formDate.setMonth(formDate.getMonth() + step);
    renderFormCalendar();
}

function clearDate() {
    document.getElementById('val-date').value = "";
    document.getElementById('ui-date').innerText = "Pilih Tanggal";
    document.getElementById('ui-date').classList.remove('text-slate-900');
    document.getElementById('ui-date').classList.add('text-slate-400');
    toggleFormCalendar();
    renderFormCalendar();
}

function toggleFormCalendar() {
    const modal = document.getElementById("formCalendarModal");
    const content = document.getElementById("formCalendarContent");
    if (modal.classList.contains("hidden")) {
        modal.classList.remove("hidden");
        setTimeout(() => {
            content.classList.remove("scale-95", "opacity-0");
            content.classList.add("scale-100", "opacity-100");
        }, 10);
        renderFormCalendar();
    } else {
        content.classList.remove("scale-100", "opacity-100");
        content.classList.add("scale-95", "opacity-0");
        setTimeout(() => { modal.classList.add("hidden"); }, 300);
    }
}
</script>

<?php renderFooter(); ?>