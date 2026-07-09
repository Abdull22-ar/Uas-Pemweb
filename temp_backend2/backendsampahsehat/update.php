<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

$baseDir = dirname(__DIR__);
$baseDirStandard = str_replace('\\', '/', $baseDir);
$action = $_GET['action'] ?? '';
$message = '';
$status = '';

function cleanDirectory($dirPath, $baseDir) {
    if (!is_dir($dirPath)) return;
    $iterator = new DirectoryIterator($dirPath);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot()) continue;
        
        $filePath = str_replace('\\', '/', $fileinfo->getPathname());
        $baseStandard = str_replace('\\', '/', $baseDir);
        $relativePath = substr($filePath, strlen($baseStandard) + 1);
        
        // Pengecualian (Protection)
        // 1. Lindungi folder public sepenuhnya agar isi di dalamnya (index.php, aset, update.php dll) aman
        if (strpos($relativePath, 'public') === 0) continue;
        
        // 2. Lindungi file zip instalasi agar tidak ikut terhapus
        if (strpos($relativePath, 'project.zip') === 0) continue;
        
        // 3. Lindungi .env agar konfigurasi database tidak hilang
        if ($relativePath === '.env') continue;

        if ($fileinfo->isDir()) {
            cleanDirectory($filePath, $baseDir);
            @rmdir($filePath);
        } else {
            @unlink($filePath);
        }
    }
}

if ($action === 'hapus') {
    cleanDirectory($baseDir, $baseDir);
    $status = 'success';
    $message = "Semua file dan folder lama berhasil dihapus!<br><em>(Catatan: folder public/, .env, dan file project.zip.* sengaja diamankan)</em>";
} elseif ($action === 'unzip') {
    $zipFileName = $baseDir . '/project.zip';
    $partFiles = glob($baseDir . '/project.zip.*');
    sort($partFiles);

    // 1. Gabungkan file part zip jika ada
    if (count($partFiles) > 0) {
        $out = fopen($zipFileName, 'wb');
        if ($out !== false) {
            foreach ($partFiles as $file) {
                $in = fopen($file, 'rb');
                while ($buffer = fread($in, 1048576)) {
                    fwrite($out, $buffer);
                }
                fclose($in);
            }
            fclose($out);
        } else {
            $status = 'error';
            $message = "Gagal membuat/menulis file project.zip di root direktori.";
        }
    }

    // 2. Ekstrak file zip
    if (!file_exists($zipFileName)) {
        $status = 'error';
        $message = "File project.zip atau pecahannya (project.zip.001) tidak ditemukan di direktori root!";
    } else {
        $zip = new ZipArchive;
        if ($zip->open($zipFileName) === TRUE) {
            $zip->extractTo($baseDir);
            $zip->close();
            
            // Hapus file zip setelah berhasil ekstrak
            @unlink($zipFileName);
            foreach ($partFiles as $file) {
                @unlink($file);
            }
            
            $status = 'success';
            $message = "Berhasil! Project terbaru telah diekstrak dan siap digunakan.<br>File ZIP instalasi juga telah dibersihkan.";
        } else {
            $status = 'error';
            $message = "Gagal mengekstrak project.zip. File mungkin korup atau permission ditolak.";
        }
    }
}

// Cek status saat ini
$filesInRoot = scandir($baseDir);
$hasZipParts = count(glob($baseDir . '/project.zip.*')) > 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Panel - InfinityFree</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100">
        <!-- Header -->
        <div class="bg-slate-900 px-6 py-5 text-white">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Deployment Panel - InfinityFree
            </h1>
            <p class="text-slate-400 text-sm mt-1">Kelola dan update project Laravel Anda secara manual.</p>
        </div>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl border <?php echo $status === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
                    <div class="flex items-start gap-3">
                        <?php if ($status === 'success'): ?>
                            <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php endif; ?>
                        <div>
                            <h3 class="font-bold text-sm <?php echo $status === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
                                <?php echo $status === 'success' ? 'Berhasil!' : 'Terjadi Kesalahan!'; ?>
                            </h3>
                            <p class="text-sm mt-1"><?php echo $message; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Langkah 1: Hapus -->
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 flex flex-col h-full">
                    <div class="mb-4">
                        <span class="inline-block bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full mb-2">Langkah 1</span>
                        <h3 class="font-bold text-slate-800">Hapus File Lama</h3>
                        <p class="text-sm text-slate-500 mt-2">Hapus seluruh file di direktori root (htdocs) agar tidak ada file sampah.</p>
                        <p class="text-[11px] text-slate-400 mt-2 italic">* Tenang, file .env, project.zip, dan update.php ini akan dilindungi secara otomatis.</p>
                    </div>
                    
                    <div class="mt-auto pt-4">
                        <a href="?action=hapus" onclick="return confirm('Yakin ingin menghapus seluruh file lama di htdocs? Pastikan Anda sudah mem-backup jika perlu.')" 
                           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus File Lama
                        </a>
                    </div>
                </div>

                <!-- Langkah 2: Unzip -->
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 flex flex-col h-full">
                    <div class="mb-4">
                        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full mb-2">Langkah 2</span>
                        <h3 class="font-bold text-slate-800">Ekstrak ZIP Baru</h3>
                        <p class="text-sm text-slate-500 mt-2">Gabungkan dan ekstrak file pecahan ZIP (project.zip.001, dst) yang sudah di-upload.</p>
                        <?php if ($hasZipParts): ?>
                            <div class="mt-3 flex items-center gap-1.5 text-xs text-green-600 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                File pecahan ZIP terdeteksi!
                            </div>
                        <?php else: ?>
                            <div class="mt-3 flex items-center gap-1.5 text-xs text-red-500 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                File project.zip.001 tidak terdeteksi. Upload dulu ke htdocs!
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-auto pt-4">
                        <a href="?action=unzip" onclick="return confirm('Mulai proses ekstraksi? Proses ini memakan waktu beberapa detik.')" 
                           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 <?php echo $hasZipParts ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-400 pointer-events-none'; ?> text-white font-semibold rounded-lg transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Mulai Ekstrak
                        </a>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Keamanan -->
            <div class="mt-6 border-t border-slate-100 pt-5 text-center">
                <p class="text-xs text-slate-500 font-medium bg-yellow-50 text-yellow-700 py-2 px-4 rounded-lg inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Penting: Segera hapus file public/update.php ini jika proses deployment sudah selesai.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
