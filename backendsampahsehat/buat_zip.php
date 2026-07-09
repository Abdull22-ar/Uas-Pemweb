<?php
/**
 * Script untuk membuat ZIP dari project dan memecahnya menjadi beberapa part
 * (Maksimal 10MB per file) agar bisa diupload ke InfinityFree.
 * 
 * OPTIMIZED VERSION - Menggunakan RecursiveDirectoryIterator dan hash-based exclusion
 * untuk performa lebih cepat.
 */

ini_set('max_execution_time', 600); // Waktu eksekusi 10 menit
ini_set('memory_limit', '512M');

$zipFileName = 'project.zip';
$chunkSize = 10 * 1024 * 1024; // 10MB (InfinityFree memiliki limit upload 10MB)

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
$startTime = microtime(true);

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
    // '.env.example', // Include .env.example for deployment
    // '.env.infinityfree', // Include .env.infinityfree for deployment

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
    'update.php',

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

// OPTIMIZATION: Convert arrays to hash sets for O(1) lookups
$excludeFoldersSet = array_flip($excludeFolders);
$excludeFilesSet = array_flip($excludeFiles);

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
 * Cek apakah file/folder harus di-exclude (OPTIMIZED dengan hash sets)
 */
function shouldExclude($relativePath, $excludeFoldersSet, $excludeFilesSet, $excludePatterns, $excludePathPatterns) {
    // Normalize separator
    $relativePath = str_replace('\\', '/', $relativePath);
    
    // Cek folder exclusion menggunakan hash set O(1)
    $pathParts = explode('/', $relativePath);
    $firstPart = $pathParts[0];
    if (isset($excludeFoldersSet[$firstPart]) || isset($excludeFoldersSet[$relativePath])) {
        return true;
    }
    
    // Cek file spesifik menggunakan hash set O(1)
    if (isset($excludeFilesSet[$relativePath])) {
        return true;
    }
    
    // Cek pattern (ekstensi / wildcard sederhana)
    $basename = basename($relativePath);
    foreach ($excludePatterns as $pattern) {
        // Cek apakah filename berakhir dengan pattern
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
    if ($basename === 'laravel.log') {
        return true;
    }
    
    // Exclude session files (random hash filenames di storage/framework/sessions)
    if (strpos($relativePath, 'storage/framework/sessions/') === 0 && $basename !== '.gitignore' && $basename !== '.gitkeep') {
        return true;
    }
    
    // Exclude compiled views di storage/framework/views (file .php acak)
    if (strpos($relativePath, 'storage/framework/views/') === 0 && $basename !== '.gitignore' && $basename !== '.gitkeep') {
        return true;
    }
    
    // Exclude cache data files
    if (strpos($relativePath, 'storage/framework/cache/data/') === 0 && $basename !== '.gitignore' && $basename !== '.gitkeep') {
        return true;
    }
    
    return false;
}

/**
 * OPTIMIZED: Scan direktori menggunakan RecursiveDirectoryIterator dan tambahkan ke ZIP
 * Jauh lebih cepat daripada scandir() rekursif
 */
function addDirectoryToZipOptimized(ZipArchive $zip, $baseDir, $excludeFoldersSet, $excludeFilesSet, $excludePatterns, $excludePathPatterns, &$fileCount) {
    // Normalize baseDir untuk konsistensi
    $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
    $baseDir = str_replace('\\', '/', $baseDir);

    $directoryIterator = new RecursiveDirectoryIterator(
        $baseDir,
        RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS
    );
    $iterator = new RecursiveIteratorIterator(
        $directoryIterator,
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $fileInfo) {
        $fullPath = $fileInfo->getPathname();
        $fullPath = str_replace('\\', '/', $fullPath);

        // Hitung relative path dengan benar
        if (strpos($fullPath, $baseDir) === 0) {
            $relativePath = substr($fullPath, strlen($baseDir) + 1);
        } else {
            // Fallback jika path tidak match
            $relativePath = basename($fullPath);
        }

        // Skip symlinks
        if ($fileInfo->isLink()) {
            continue;
        }

        if (shouldExclude($relativePath, $excludeFoldersSet, $excludeFilesSet, $excludePatterns, $excludePathPatterns)) {
            continue;
        }

        if ($fileInfo->isDir()) {
            // Tambahkan folder kosong ke ZIP
            $zip->addEmptyDir($relativePath);
        } else {
            // Tambahkan file ke ZIP
            $zip->addFile($fullPath, $relativePath);
            $fileCount++;

            // Progress update setiap 500 file (lebih jarang untuk performa)
            if ($fileCount % 500 === 0) {
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

echo "\nSedang membuat ZIP menggunakan PHP ZipArchive (OPTIMIZED)...\n";
echo "Mohon tunggu, ini akan lebih cepat dari versi sebelumnya...\n\n";

$baseDir = __DIR__;
$fileCount = 0;

// Tambahkan semua file project menggunakan fungsi optimized
addDirectoryToZipOptimized($zip, $baseDir, $excludeFoldersSet, $excludeFilesSet, $excludePatterns, $excludePathPatterns, $fileCount);

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

$endTime = microtime(true);
$executionTime = $endTime - $startTime;

echo "\n✅ ZIP berhasil dibuat: {$zipFileName}\n";
echo "Total file yang ditambahkan: $fileCount\n";
echo "Ukuran file ZIP: " . number_format(filesize($zipFileName) / 1024 / 1024, 2) . " MB\n";
echo "Waktu eksekusi: " . number_format($executionTime, 2) . " detik\n";

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
echo "2. Upload file public/update.php ke folder 'htdocs'\n";
echo "3. Akses domain.com/update.php untuk automated deployment\n";
echo "   Script akan otomatis:\n";
echo "   - Merge dan ekstrak file ZIP\n";
echo "   - Setup .env dan generate APP_KEY\n";
echo "   - Setup storage directories\n";
echo "   - Run database migrations (jika .env sudah dikonfigurasi)\n";
echo "   - Clear cache\n";
echo "   - Cleanup deployment files\n";
echo "4. Edit .env secara MANUAL di File Manager untuk database credentials\n";
echo "5. Jika migrations gagal, run manual: php artisan migrate --force\n";
echo "\n(Penting: Jangan upload project.zip yang utuh karena akan ditolak server InfinityFree)\n";
