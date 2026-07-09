<?php
/**
 * Script untuk membuat ZIP dari project dan memecahnya menjadi beberapa part
 * (Maksimal 9MB per file) agar bisa diupload ke InfinityFree.
 * 
 * Menggunakan PHP ZipArchive (cross-platform, tidak bergantung pada tar.exe)
 */

ini_set('max_execution_time', 600); // Waktu eksekusi 10 menit
ini_set('memory_limit', '512M');

$zipFileName = 'project.zip';
$chunkSize = 9 * 1024 * 1024; // 9MB (InfinityFree memiliki limit upload 10MB)

// Bersihkan file ZIP dan part-file yang sudah ada
function cleanUpOldFiles($zipName) {
    if (file_exists($zipName)) {
        echo "Menghapus file $zipName yang sudah ada...\n";
        @unlink($zipName);
    }
    $files = glob($zipName . '.*');
    if ($files) {
        foreach ($files as $file) {
            echo "Menghapus file $file yang sudah ada...\n";
            @unlink($file);
        }
    }
}

cleanUpOldFiles($zipFileName);

echo "Memulai proses kompresi...\n";

// ============================================================
// FILE/FOLDER YANG TIDAK DIikutkan DALAM ZIP DEPLOYMENT
// ============================================================

// Folder yang harus di-exclude sepenuhnya (cocokkan nama folder)
$excludeFolders = [
    // --- Version Control ---
    '.git',

    // --- IDE / Editor Config ---
    '.cursor',

    // --- Dependencies (dev/build) ---
    'node_modules',

    // --- Testing ---
    'tests',

    // --- Symlink (bisa menyebabkan error saat zip) ---
    'public/storage',

    // --- ZIP artifacts (di root saja) ---
];

// File spesifik yang harus di-exclude (cocokkan path relatif)
$excludeFiles = [
    // --- Version Control ---
    '.gitignore',
    '.gitattributes',

    // --- IDE / Editor Config ---
    '.editorconfig',

    // --- Dependencies (dev/build) ---
    '.npmrc',
    'package.json',
    'package-lock.json',
    'vite.config.js',
    'tailwind.config.js',
    'postcss.config.js',

    // --- Environment (lokal) ---
    '.env',
    '.env.local',
    '.env.example',

    // --- Testing ---
    'phpunit.xml',

    // --- Script dev / python ---
    'buat_zip.php',
    'extract.php',
    'scratch_modal.blade.php',
    'erd_chen.puml',
    'guide.txt',
    'PANDUAN_DESAIN.md',
    'update_bm.py',
    'update_bm_alert.py',
    'update_bm_v2.py',
    'update_bm_v3.py',
    'update_bm_v4.py',
    'update_table.py',
    'generate_key.php',
    'check_error.php',

    // --- SQLite (server pakai MySQL) ---
    'database/database.sqlite',

    // --- ZIP artifacts ---
    'project.zip',
];

// Pattern wildcard yang harus di-exclude
$excludePatterns = [
    'project.zip.',   // project.zip.001, project.zip.002, dst.
    '.py',            // Semua file python
];

// Pattern path yang harus di-exclude (match partial path)
$excludePathPatterns = [
    'database/factories/',
    'database/seeders/',
    'resources/css/',  // Sudah dicompile ke public/build
    'resources/js/',   // Sudah dicompile ke public/build
];

// ============================================================
// FILE/FOLDER YANG ISI-NYA DI-EXCLUDE TAPI FOLDER HARUS TETAP ADA
// (Akan dibuatkan .gitkeep sebagai placeholder)
// ============================================================
$emptyButRequired = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/app/public',
    'bootstrap/cache',
];

/**
 * Cek apakah file/folder harus di-exclude
 */
function shouldExclude($relativePath, $excludeFolders, $excludeFiles, $excludePatterns, $excludePathPatterns) {
    // Normalize separator
    $relativePath = str_replace('\\', '/', $relativePath);
    
    // Cek folder exclusion (apakah path dimulai dengan atau mengandung folder yang di-exclude)
    foreach ($excludeFolders as $folder) {
        if ($relativePath === $folder || strpos($relativePath, $folder . '/') === 0) {
            return true;
        }
        // Cek apakah ada di sub-path (e.g., public/storage)
        if (strpos($relativePath, '/' . $folder . '/') !== false || substr($relativePath, -strlen('/' . $folder)) === '/' . $folder) {
            // Hanya exclude jika match exact folder
        }
    }
    
    // Cek file spesifik
    if (in_array($relativePath, $excludeFiles)) {
        return true;
    }
    
    // Cek pattern (ekstensi / wildcard sederhana)
    foreach ($excludePatterns as $pattern) {
        // Cek apakah filename berakhir dengan pattern
        $basename = basename($relativePath);
        if (substr($basename, -strlen($pattern)) === $pattern) {
            return true;
        }
        // Cek apakah path dimulai dengan pattern (untuk prefix match)
        if (strpos($relativePath, $pattern) === 0) {
            return true;
        }
    }
    
    // Cek path patterns (partial path match)
    foreach ($excludePathPatterns as $pathPattern) {
        if (strpos($relativePath, $pathPattern) === 0) {
            return true;
        }
    }
    
    // Exclude file-file log besar
    if (basename($relativePath) === 'laravel.log') {
        return true;
    }
    
    // Exclude session files (random hash filenames di storage/framework/sessions)
    if (strpos($relativePath, 'storage/framework/sessions/') === 0 && basename($relativePath) !== '.gitignore' && basename($relativePath) !== '.gitkeep') {
        return true;
    }
    
    // Exclude compiled views di storage/framework/views (file .php acak)
    if (strpos($relativePath, 'storage/framework/views/') === 0 && basename($relativePath) !== '.gitignore' && basename($relativePath) !== '.gitkeep') {
        return true;
    }
    
    // Exclude cache data files
    if (strpos($relativePath, 'storage/framework/cache/data/') === 0 && basename($relativePath) !== '.gitignore' && basename($relativePath) !== '.gitkeep') {
        return true;
    }
    
    return false;
}

/**
 * Rekursif scan direktori dan tambahkan ke ZIP
 */
function addDirectoryToZip(ZipArchive $zip, $baseDir, $currentDir, $excludeFolders, $excludeFiles, $excludePatterns, $excludePathPatterns, &$fileCount) {
    $items = scandir($currentDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $fullPath);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        if (shouldExclude($relativePath, $excludeFolders, $excludeFiles, $excludePatterns, $excludePathPatterns)) {
            continue;
        }
        
        if (is_dir($fullPath)) {
            // Cek apakah folder ini harus di-exclude
            $folderName = basename($fullPath);
            $skipFolder = false;
            foreach ($excludeFolders as $exFolder) {
                if ($folderName === $exFolder) {
                    $skipFolder = true;
                    break;
                }
            }
            if ($skipFolder) continue;
            
            // Tambahkan folder ke ZIP
            $zip->addEmptyDir($relativePath);
            
            // Rekursif ke sub-folder
            addDirectoryToZip($zip, $baseDir, $fullPath, $excludeFolders, $excludeFiles, $excludePatterns, $excludePathPatterns, $fileCount);
        } else {
            // Skip symlinks
            if (is_link($fullPath)) continue;
            
            $zip->addFile($fullPath, $relativePath);
            $fileCount++;
            
            if ($fileCount % 100 === 0) {
                echo "  -> $fileCount file ditambahkan...\n";
            }
        }
    }
}

// ============================================================
// MULAI PROSES KOMPRESI MENGGUNAKAN PHP ZipArchive
// ============================================================

if (!class_exists('ZipArchive')) {
    die("\n❌ Ekstensi PHP ZIP tidak tersedia! Aktifkan ext-zip di php.ini.\n");
}

$zip = new ZipArchive();
$result = $zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($result !== true) {
    die("\n❌ Gagal membuat file ZIP! Error code: $result\n");
}

echo "\nSedang membuat ZIP menggunakan PHP ZipArchive...\n";
echo "Mohon tunggu, ini bisa memakan waktu beberapa menit...\n\n";

$baseDir = __DIR__;
$fileCount = 0;

// Tambahkan semua file project
addDirectoryToZip($zip, $baseDir, $baseDir, $excludeFolders, $excludeFiles, $excludePatterns, $excludePathPatterns, $fileCount);

// ============================================================
// TAMBAHKAN FOLDER KOSONG YANG WAJIB ADA (dengan .gitkeep)
// ============================================================
echo "\nMenambahkan folder required yang kosong...\n";
foreach ($emptyButRequired as $dir) {
    // Pastikan folder ada di ZIP
    $zip->addEmptyDir($dir);
    // Tambahkan .gitkeep sebagai placeholder agar folder tidak hilang
    $zip->addFromString($dir . '/.gitkeep', '');
    echo "  -> Folder: $dir/ (dengan .gitkeep)\n";
}

$zip->close();

if (!file_exists($zipFileName)) {
    die("\n❌ Gagal membuat file ZIP!\n");
}

echo "\n✅ ZIP berhasil dibuat: {$zipFileName}\n";
echo "Total file yang ditambahkan: $fileCount\n";
echo "Ukuran file ZIP: " . number_format(filesize($zipFileName) / 1024 / 1024, 2) . " MB\n";

echo "\nMulai memecah file ZIP menjadi bagian-bagian kecil (Maks 9MB)...\n";
$handle = @fopen($zipFileName, 'rb');
if ($handle === false) {
    die("\n❌ Gagal membuka file ZIP untuk dipecah!\n");
}
$part = 1;
while (!feof($handle)) {
    $buffer = fread($handle, $chunkSize);
    if ($buffer === false || strlen($buffer) === 0) break;
    
    $partName = sprintf("%s.%03d", $zipFileName, $part);
    file_put_contents($partName, $buffer);
    echo "-> Dibuat part: $partName (" . number_format(strlen($buffer) / 1024 / 1024, 2) . " MB)\n";
    $part++;
}
fclose($handle);

echo "\n" . str_repeat("=", 60) . "\n";
echo "SELESAI!\n";
echo str_repeat("=", 60) . "\n";
echo "Jumlah total part: " . ($part - 1) . "\n\n";

echo "LANGKAH DEPLOY KE INFINITYFREE:\n";
echo "1. Upload file project.zip.001, project.zip.002, dst. ke folder 'htdocs'\n";
echo "2. Upload file unzip.php ke folder 'htdocs'\n";
echo "3. Akses domain.com/unzip.php untuk ekstrak\n";
echo "4. Buat file .env secara MANUAL di File Manager (copy dari .env.example)\n";
echo "5. Isi DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD sesuai hosting\n";
echo "6. Akses domain.com/setup.php untuk import database\n";
echo "7. Akses domain.com/deploy-setup untuk clear cache & buat storage link\n";
echo "8. HAPUS file unzip.php dan setup.php setelah selesai!\n";
echo "\n(Penting: Jangan upload project.zip yang utuh karena akan ditolak server InfinityFree)\n";
