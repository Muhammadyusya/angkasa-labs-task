<?php

declare(strict_types=1);

require_once 'includes/auth.php';
require_once 'koneksi.php';

$taskId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$taskId) {
    header("Location: daftar_tugas.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        // Langsung cut kalau ID-nya sampah atau data udah kehapus
        die("Record not found in database.");
    }
} catch (PDOException $e) {
    error_log("Edit Page Fetch Error: " . $e->getMessage());
    die("Database connectivity issue.");
}

require_once 'includes/layout.php';
renderHeader('Edit Penugasan');
?>

<div class="max-w-3xl mx-auto">
  <div class="mb-8 flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Penugasan</h1>
      <p class="text-sm text-slate-500 mt-2">Perbarui status, prioritas, atau jadwal operasional.</p>
    </div>
    <span class="px-3 py-1.5 bg-slate-100 text-slate-500 font-mono font-bold text-sm rounded-lg border border-slate-200">
      #AL-<?= sprintf('%04d', $task['id']) ?>
    </span>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 hover:shadow-md transition-shadow duration-300">
    <form action="proses_edit.php" method="POST" class="space-y-6">
      <input type="hidden" name="id" value="<?= $task['id'] ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">Klien / Proyek <span class="text-red-500">*</span></label>
          <input type="text" name="nama_klien" value="<?= htmlspecialchars($task['nama_klien']) ?>" required
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm">
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">PIC Internal <span class="text-red-500">*</span></label>
          <input type="text" name="pic" value="<?= htmlspecialchars($task['pic']) ?>" required
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm">
        </div>
      </div>

      <div class="space-y-2">
        <label class="block text-xs font-bold text-slate-700">Spesifikasi Pekerjaan <span class="text-red-500">*</span></label>
        <textarea name="deskripsi" required
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm resize-none"
          rows="4"><?= htmlspecialchars($task['deskripsi']) ?></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">Prioritas</label>
          <select name="prioritas"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm cursor-pointer">
            <option value="Rendah" <?= ($task['prioritas'] === 'Rendah') ? 'selected' : '' ?>>Rendah</option>
            <option value="Sedang" <?= ($task['prioritas'] === 'Sedang') ? 'selected' : '' ?>>Sedang</option>
            <option value="Tinggi" <?= ($task['prioritas'] === 'Tinggi') ? 'selected' : '' ?>>Tinggi / Urgent</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">Status Proyek</label>
          <select name="status"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm cursor-pointer">
            <option value="Pending" <?= ($task['status'] === 'Pending') ? 'selected' : '' ?>>Antrean / Pending</option>
            <option value="Dalam Proses" <?= ($task['status'] === 'Dalam Proses') ? 'selected' : '' ?>>Dalam Proses</option>
            <option value="Selesai" <?= ($task['status'] === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700">Deadline</label>
          <input type="date" name="due_date" value="<?= htmlspecialchars($task['due_date'] ?? '') ?>"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all shadow-sm cursor-pointer">
        </div>
      </div>

      <div class="border-t border-slate-200 pt-6 mt-4 flex justify-end gap-3">
        <a href="daftar_tugas.php"
          class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-xl text-sm transition-colors">Batal</a>
        <button type="submit"
          class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 hover:bg-blue-700 transition-all text-sm flex items-center gap-2">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<?php renderFooter(); ?>