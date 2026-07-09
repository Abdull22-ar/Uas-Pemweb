<?php
/**
 * Deployment Script untuk InfinityFree
 * 
 * Fungsi:
 * 1. Menggabungkan dan mengekstrak file ZIP yang dipecah
 * 2. Setup environment (.env)
 * 3. Generate APP_KEY
 * 4. Run database migrations
 * 5. Setup storage directories
 * 6. Clear cache
 * 7. Cleanup deployment files
 * 
 * Usage: Akses domain.com/update.php dan klik tombol untuk setiap step
 */

ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// ============================================================
// CONFIGURATION
// ============================================================
$zipFileName = 'project.zip';
$baseDir = dirname(__DIR__); // Root project

// ============================================================
// UTILITY FUNCTIONS
// ============================================================
function showMessage($message, $type = 'info') {
    // Jika running di CLI, output ANSI color codes
    if (php_sapi_name() === 'cli') {
        $colors = [
            'success' => "\033[32m",
            'error' => "\033[31m",
            'warning' => "\033[33m",
            'info' => "\033[36m",
            'reset' => "\033[0m"
        ];

        $color = $colors[$type] ?? $colors['info'];
        echo $color . $message . $colors['reset'] . "\n";
    } else {
        // Jika running di web, JANGAN output apapun (output akan di-capture oleh ob_start())
        // Output akan dikirim via JSON response
    }
}

function isWebInterface() {
    return php_sapi_name() !== 'cli';
}

function returnJson($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function checkRequirements() {
    global $baseDir;

    showMessage("=== CHECKING REQUIREMENTS ===", 'info');

    $errors = [];
    $warnings = [];

    // Check PHP version
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        $errors[] = "PHP version must be 8.1 or higher. Current: " . PHP_VERSION;
    } else {
        showMessage("✅ PHP Version: " . PHP_VERSION, 'success');
    }

    // Check required extensions
    $requiredExtensions = ['zip', 'pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'ctype', 'tokenizer', 'xml'];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Required extension missing: $ext";
        }
    }

    // CRITICAL: Check vendor folder
    if (!file_exists($baseDir . '/vendor')) {
        $errors[] = "CRITICAL: vendor folder not found! Laravel cannot run without dependencies.";
    } else {
        showMessage("✅ vendor folder exists", 'success');
    }

    // Check .env file
    if (!file_exists($baseDir . '/.env')) {
        $errors[] = "CRITICAL: .env file not found!";
    } else {
        if (!is_readable($baseDir . '/.env')) {
            $errors[] = "CRITICAL: .env file is not readable!";
        } else {
            showMessage("✅ .env file exists and readable", 'success');
        }
    }

    // Check .env.infinityfree
    if (file_exists($baseDir . '/.env.infinityfree')) {
        showMessage("✅ .env.infinityfree found (will be used for .env)", 'success');
    } else {
        $warnings[] = ".env.infinityfree not found, will use .env.example or basic .env";
    }

    // Check write permissions
    $writablePaths = [
        $baseDir . '/storage',
        $baseDir . '/bootstrap/cache',
        dirname(__DIR__) . '/public/storage'
    ];

    foreach ($writablePaths as $path) {
        if (!file_exists($path)) {
            @mkdir($path, 0755, true);
        }
        if (!is_writable($path)) {
            $errors[] = "Directory not writable: $path";
        } else {
            showMessage("✅ Writable: $path", 'success');
        }
    }

    // Display warnings
    if (!empty($warnings)) {
        showMessage("WARNINGS:", 'warning');
        foreach ($warnings as $warning) {
            showMessage("  - $warning", 'warning');
        }
    }

    if (!empty($errors)) {
        showMessage("ERRORS FOUND:", 'error');
        foreach ($errors as $error) {
            showMessage("  - $error", 'error');
        }
        return false;
    }

    showMessage("✅ All requirements met", 'success');
    return true;
}

// ============================================================
// STEP 1: MERGE AND EXTRACT ZIP
// ============================================================
function mergeAndExtractZip() {
    global $zipFileName, $baseDir;
    
    showMessage("\n=== STEP 1: MERGE AND EXTRACT ZIP ===", 'info');
    
    // Cek apakah file zip utuh sudah ada
    if (file_exists($baseDir . '/' . $zipFileName)) {
        showMessage("File $zipFileName already exists, skipping merge...", 'warning');
    } else {
        // Cari file part
        $partFiles = glob($baseDir . '/' . $zipFileName . '.*');
        
        if (empty($partFiles)) {
            showMessage("No ZIP parts found ($zipFileName.001, etc.)", 'error');
            showMessage("Please upload the ZIP parts first!", 'error');
            return false;
        }
        
        // Sort part files
        natsort($partFiles);
        
        showMessage("Found " . count($partFiles) . " ZIP parts", 'info');
        showMessage("Merging parts...", 'info');
        
        // Merge parts
        $outputFile = $baseDir . '/' . $zipFileName;
        $handle = fopen($outputFile, 'wb');
        
        if (!$handle) {
            showMessage("Failed to create merged ZIP file", 'error');
            return false;
        }
        
        foreach ($partFiles as $partFile) {
            $content = file_get_contents($partFile);
            if ($content === false) {
                showMessage("Failed to read part: $partFile", 'error');
                fclose($handle);
                return false;
            }
            fwrite($handle, $content);
            showMessage("  - Merged: " . basename($partFile), 'info');
        }
        
        fclose($handle);
        showMessage("✅ ZIP merged successfully", 'success');
    }
    
    // Extract ZIP
    showMessage("Extracting ZIP...", 'info');
    
    $zip = new ZipArchive();
    $result = $zip->open($baseDir . '/' . $zipFileName);
    
    if ($result !== true) {
        showMessage("Failed to open ZIP file. Error code: $result", 'error');
        return false;
    }
    
    // Extract to base directory
    if (!$zip->extractTo($baseDir)) {
        showMessage("Failed to extract ZIP", 'error');
        $zip->close();
        return false;
    }
    
    $zip->close();
    showMessage("✅ ZIP extracted successfully", 'success');
    
    // Delete merged ZIP
    @unlink($baseDir . '/' . $zipFileName);
    showMessage("Deleted merged ZIP file", 'info');
    
    return true;
}

// ============================================================
// STEP 2: SETUP ENVIRONMENT
// ============================================================
function setupEnvironment() {
    global $baseDir;

    showMessage("\n=== STEP 2: SETUP ENVIRONMENT ===", 'info');

    $envFile = $baseDir . '/.env';
    $envExample = $baseDir . '/.env.example';
    $envInfinityfree = $baseDir . '/.env.infinityfree';

    // Priority: .env.infinityfree > .env.example > basic .env
    if (file_exists($envInfinityfree)) {
        showMessage("Found .env.infinityfree, copying to .env...", 'info');
        copy($envInfinityfree, $envFile);
        showMessage("✅ .env created from .env.infinityfree", 'success');
        return true;
    }

    if (file_exists($envFile)) {
        showMessage(".env file already exists, skipping creation...", 'warning');
        return true;
    }

    if (!file_exists($envExample)) {
        showMessage(".env.example not found, creating basic .env...", 'warning');

        $basicEnv = <<<ENV
APP_NAME="Sampah Sehat"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
ENV;

        file_put_contents($envFile, $basicEnv);
        showMessage("✅ Basic .env created", 'success');
        showMessage("⚠️  PLEASE EDIT .env MANUALLY with your database credentials!", 'warning');
        return true;
    }

    // Copy from .env.example
    copy($envExample, $envFile);
    showMessage("✅ .env created from .env.example", 'success');
    showMessage("⚠️  PLEASE EDIT .env MANUALLY with your database credentials!", 'warning');

    return true;
}

// ============================================================
// STEP 3: GENERATE APP KEY
// ============================================================
function generateAppKey() {
    global $baseDir;
    
    showMessage("\n=== STEP 3: GENERATE APP KEY ===", 'info');
    
    $envFile = $baseDir . '/.env';
    
    if (!file_exists($envFile)) {
        showMessage(".env file not found!", 'error');
        return false;
    }
    
    $envContent = file_get_contents($envFile);
    
    // Check if APP_KEY is already set
    if (preg_match('/APP_KEY=base64:([A-Za-z0-9\/+=]+)/', $envContent)) {
        showMessage("APP_KEY already set, skipping...", 'info');
        return true;
    }
    
    // Generate random key
    $key = base64_encode(random_bytes(32));
    
    // Replace APP_KEY in .env
    $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=base64:' . $key, $envContent);
    
    file_put_contents($envFile, $envContent);
    showMessage("✅ APP_KEY generated: base64:$key", 'success');
    
    return true;
}

// ============================================================
// STEP 4: RUN MIGRATIONS
// ============================================================
function runMigrations() {
    global $baseDir;
    
    showMessage("\n=== STEP 4: RUN DATABASE MIGRATIONS ===", 'info');
    
    // Check if .env has database config
    $envFile = $baseDir . '/.env';
    $envContent = file_get_contents($envFile);
    
    if (strpos($envContent, 'your_database') !== false || 
        strpos($envContent, 'your_username') !== false ||
        strpos($envContent, 'your_password') !== false) {
        showMessage("⚠️  Database credentials not configured in .env", 'warning');
        showMessage("Please edit .env with your database credentials first!", 'warning');
        showMessage("Then run migrations manually: php artisan migrate", 'warning');
        return false;
    }
    
    // Load Laravel
    require $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    
    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        // Run migrations
        $exitCode = $kernel->call('migrate', ['--force' => true]);
        
        if ($exitCode === 0) {
            showMessage("✅ Database migrations completed successfully", 'success');
        } else {
            showMessage("⚠️  Migration completed with warnings", 'warning');
        }
        
        return true;
    } catch (Exception $e) {
        showMessage("❌ Migration failed: " . $e->getMessage(), 'error');
        return false;
    }
}

// ============================================================
// DIAGNOSTIC STEP: CHECK HTTP 500 ISSUES
// ============================================================
function diagnoseHttp500() {
    global $baseDir;

    showMessage("\n=== DIAGNOSTIC: HTTP 500 ISSUES ===", 'info');

    $issues = [];
    $warnings = [];

    // Check 1: Vendor folder
    if (!file_exists($baseDir . '/vendor')) {
        $issues[] = "❌ CRITICAL: vendor folder NOT FOUND! This causes HTTP 500.";
        $issues[] = "   Solution: Re-upload the ZIP file with vendor folder included.";
    } else {
        showMessage("✅ vendor folder exists", 'success');
    }

    // Check 2: .env file
    if (!file_exists($baseDir . '/.env')) {
        $issues[] = "❌ CRITICAL: .env file NOT FOUND!";
    } else {
        if (!is_readable($baseDir . '/.env')) {
            $issues[] = "❌ CRITICAL: .env file is NOT readable!";
        } else {
            $envContent = file_get_contents($baseDir . '/.env');
            if (strpos($envContent, 'APP_KEY=') === false) {
                $issues[] = "❌ CRITICAL: APP_KEY not set in .env!";
            } else {
                showMessage("✅ .env file exists and has APP_KEY", 'success');
            }
        }
    }

    // Check 3: Storage permissions
    $storagePaths = [
        $baseDir . '/storage',
        $baseDir . '/storage/logs',
        $baseDir . '/storage/framework',
        $baseDir . '/bootstrap/cache',
    ];

    $permissionIssues = false;
    foreach ($storagePaths as $path) {
        if (file_exists($path)) {
            if (!is_writable($path)) {
                $issues[] = "❌ NOT WRITABLE: $path";
                $permissionIssues = true;
            }
        }
    }

    if (!$permissionIssues) {
        showMessage("✅ Storage directories are writable", 'success');
    }

    // Check 4: PHP version
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        $issues[] = "❌ PHP version too old: " . PHP_VERSION . " (requires 8.1+)";
    } else {
        showMessage("✅ PHP version: " . PHP_VERSION, 'success');
    }

    // Check 5: Required extensions
    $requiredExtensions = ['mbstring', 'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml'];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $issues[] = "❌ Missing PHP extension: $ext";
        }
    }

    // Check 6: Public index.php
    if (!file_exists($baseDir . '/public/index.php')) {
        $issues[] = "❌ CRITICAL: public/index.php not found!";
    } else {
        showMessage("✅ public/index.php exists", 'success');
    }

    // Display results
    if (!empty($issues)) {
        showMessage("\n❌ CRITICAL ISSUES FOUND:", 'error');
        foreach ($issues as $issue) {
            showMessage("  $issue", 'error');
        }
        showMessage("\n📝 RECOMMENDED ACTIONS:", 'warning');
        showMessage("  1. If vendor is missing: Re-upload ZIP with vendor folder", 'warning');
        showMessage("  2. If .env is missing: Run Step 2 (Setup Environment)", 'warning');
        showMessage("  3. If permissions issue: Run Step 5 (Setup Storage) again", 'warning');
        showMessage("  4. If PHP extensions missing: Contact InfinityFree support", 'warning');
        return false;
    }

    if (!empty($warnings)) {
        showMessage("\n⚠️  WARNINGS:", 'warning');
        foreach ($warnings as $warning) {
            showMessage("  $warning", 'warning');
        }
    }

    showMessage("\n✅ No critical issues found. If still HTTP 500, check Laravel logs:", 'success');
    showMessage("  storage/logs/laravel.log", 'info');
    return true;
}

// ============================================================
// STEP 5: SETUP STORAGE DIRECTORIES
// ============================================================
function setupStorage() {
    global $baseDir;

    showMessage("\n=== STEP 5: SETUP STORAGE DIRECTORIES ===", 'info');

    $storageDirs = [
        $baseDir . '/storage/app',
        $baseDir . '/storage/app/public',
        $baseDir . '/storage/app/public/laporan',
        $baseDir . '/storage/app/public/laporan/foto',
        $baseDir . '/storage/framework',
        $baseDir . '/storage/framework/cache',
        $baseDir . '/storage/framework/cache/data',
        $baseDir . '/storage/framework/sessions',
        $baseDir . '/storage/framework/views',
        $baseDir . '/storage/logs',
        $baseDir . '/bootstrap/cache',
        dirname(__DIR__) . '/public/storage',
        dirname(__DIR__) . '/public/storage/laporan',
        dirname(__DIR__) . '/public/storage/laporan/foto',
    ];

    foreach ($storageDirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            showMessage("  - Created: $dir", 'info');
        }

        // Set permissions (try multiple permission levels for InfinityFree)
        @chmod($dir, 0777);
        @chmod($dir, 0755);
    }

    // Create .gitkeep files
    $gitkeepDirs = [
        $baseDir . '/storage/app/public',
        $baseDir . '/storage/framework/cache/data',
        $baseDir . '/storage/framework/sessions',
        $baseDir . '/storage/framework/views',
        $baseDir . '/storage/logs',
        $baseDir . '/bootstrap/cache',
        dirname(__DIR__) . '/public/storage/laporan/foto',
    ];

    foreach ($gitkeepDirs as $dir) {
        if (!file_exists($dir . '/.gitkeep')) {
            file_put_contents($dir . '/.gitkeep', '');
        }
    }

    showMessage("✅ Storage directories setup completed", 'success');
    return true;
}

// ============================================================
// STEP 6: CLEAR CACHE
// ============================================================
function clearCache() {
    global $baseDir;
    
    showMessage("\n=== STEP 6: CLEAR CACHE ===", 'info');
    
    try {
        require $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        // Clear various caches
        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'config:cache',
            'route:cache',
        ];
        
        foreach ($commands as $command) {
            $kernel->call($command);
            showMessage("  - $command", 'info');
        }
        
        showMessage("✅ Cache cleared successfully", 'success');
        return true;
    } catch (Exception $e) {
        showMessage("⚠️  Cache clear failed (non-critical): " . $e->getMessage(), 'warning');
        return true;
    }
}

// ============================================================
// STEP 7: CLEANUP
// ============================================================
function cleanup() {
    global $baseDir, $zipFileName;

    showMessage("\n=== STEP 7: CLEANUP DEPLOYMENT FILES ===", 'info');

    // File dan folder yang HARUS dipertahankan (tidak boleh dihapus)
    $preserveItems = [
        '.env',
        '.env.production',
        '.env.infinityfree',
        'storage',
        'public/storage',
    ];

    // Folder dan file yang akan dihapus (application folders)
    $deleteItems = [
        'app',
        'bootstrap',
        'config',
        'database',
        'node_modules',
        'public',
        'resources',
        'routes',
        'tests',
        // 'vendor', // CRITICAL: JANGAN HAPUS vendor! Laravel butuh dependencies
        '.vscode',
        '.git',
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'phpunit.xml',
        'README.md',
        '.gitignore',
        '.gitattributes',
        '.editorconfig',
    ];

    // Fungsi untuk menghapus directory secara rekursif
    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return @unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return @rmdir($dir);
    }

    // Hapus ZIP parts
    $partFiles = glob($baseDir . '/' . $zipFileName . '.*');
    foreach ($partFiles as $partFile) {
        @unlink($partFile);
        showMessage("  - Deleted: " . basename($partFile), 'info');
    }

    // Hapus file ZIP utuh jika masih ada
    if (file_exists($baseDir . '/' . $zipFileName)) {
        @unlink($baseDir . '/' . $zipFileName);
        showMessage("  - Deleted: $zipFileName", 'info');
    }

    // Hapus folder dan file application
    foreach ($deleteItems as $item) {
        $itemPath = $baseDir . '/' . $item;

        // Skip jika ada di preserve list
        if (in_array($item, $preserveItems)) {
            continue;
        }

        if (file_exists($itemPath)) {
            if (is_dir($itemPath)) {
                if (deleteDirectory($itemPath)) {
                    showMessage("  - Deleted directory: $item", 'info');
                } else {
                    showMessage("  - Failed to delete directory: $item", 'warning');
                }
            } else {
                if (@unlink($itemPath)) {
                    showMessage("  - Deleted file: $item", 'info');
                } else {
                    showMessage("  - Failed to delete file: $item", 'warning');
                }
            }
        }
    }

    // Jangan hapus update.php - biarkan user bisa menjalankan lagi jika perlu
    // $thisScript = __FILE__;
    // if (file_exists($thisScript)) {
    //     @unlink($thisScript);
    //     showMessage("  - Deleted: update.php", 'info');
    // }

    showMessage("✅ Cleanup completed", 'success');
    showMessage("⚠️  Preserved: .env, storage folders, update.php", 'warning');
    showMessage("📝 Deleted: app, bootstrap, config, database, etc. (vendor preserved)", 'info');
    return true;
}

// ============================================================
// AJAX API HANDLER
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $output = [];

    // Capture output - start buffer BEFORE any output
    ob_start();

    try {
        switch ($action) {
            case 'checkRequirements':
                $result = checkRequirements();
                $output = ob_get_clean();
                // Clean any whitespace before JSON
                ob_clean();
                returnJson($result, $result ? 'Requirements check passed' : 'Requirements check failed', ['output' => $output]);

            case 'mergeAndExtractZip':
                $result = mergeAndExtractZip();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, $result ? 'ZIP merged and extracted successfully' : 'Failed to merge/extract ZIP', ['output' => $output]);

            case 'setupEnvironment':
                $result = setupEnvironment();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, $result ? 'Environment setup completed' : 'Environment setup failed', ['output' => $output]);

            case 'generateAppKey':
                $result = generateAppKey();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, $result ? 'APP_KEY generated successfully' : 'Failed to generate APP_KEY', ['output' => $output]);

            case 'setupStorage':
                $result = setupStorage();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, 'Storage directories setup completed', ['output' => $output]);

            case 'runMigrations':
                $result = runMigrations();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, $result ? 'Migrations completed successfully' : 'Migrations failed or skipped', ['output' => $output]);

            case 'clearCache':
                $result = clearCache();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, 'Cache cleared successfully', ['output' => $output]);

            case 'cleanup':
                $result = cleanup();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, 'Cleanup completed', ['output' => $output]);

            case 'diagnoseHttp500':
                $result = diagnoseHttp500();
                $output = ob_get_clean();
                ob_clean();
                returnJson($result, $result ? 'No critical issues found' : 'Critical issues found', ['output' => $output]);

            default:
                $output = ob_get_clean();
                ob_clean();
                returnJson(false, 'Unknown action', ['output' => $output]);
        }
    } catch (Exception $e) {
        $output = ob_get_clean();
        ob_clean();
        returnJson(false, 'Error: ' . $e->getMessage(), ['output' => $output]);
    }
}

// ============================================================
// MAIN EXECUTION (CLI MODE)
// ============================================================
if (!isWebInterface()) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║     INFINITYFREE DEPLOYMENT SCRIPT                       ║\n";
    echo "║     Sampah Sehat Application                             ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";

    $steps = [
        'checkRequirements' => 'Checking Requirements',
        'mergeAndExtractZip' => 'Merge and Extract ZIP',
        'setupEnvironment' => 'Setup Environment',
        'generateAppKey' => 'Generate APP_KEY',
        'setupStorage' => 'Setup Storage Directories',
        'runMigrations' => 'Run Database Migrations',
        'clearCache' => 'Clear Cache',
        'cleanup' => 'Cleanup Deployment Files',
    ];

    $failed = false;
    $completedSteps = [];

    foreach ($steps as $function => $name) {
        if ($failed && $function !== 'cleanup') {
            showMessage("Skipping $name due to previous failure", 'warning');
            continue;
        }

        try {
            $result = $function();
            if ($result === false && $function !== 'runMigrations') {
                $failed = true;
            }
            $completedSteps[] = $name;
        } catch (Exception $e) {
            showMessage("Error in $name: " . $e->getMessage(), 'error');
            if ($function !== 'runMigrations') {
                $failed = true;
            }
        }
    }

    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║     DEPLOYMENT SUMMARY                                  ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";

    foreach ($completedSteps as $step) {
        showMessage("✅ $step", 'success');
    }

    if ($failed) {
        showMessage("\n❌ DEPLOYMENT FAILED - Please check errors above", 'error');
        showMessage("Fix the issues and run this script again", 'error');
    } else {
        showMessage("\n🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!", 'success');
        showMessage("\nNEXT STEPS:", 'info');
        showMessage("1. Edit .env with your database credentials (if not done)", 'info');
        showMessage("2. Access your domain to test the application", 'info');
        showMessage("3. If migrations were skipped, run: php artisan migrate --force", 'info');
    }
}

// ============================================================
// WEB INTERFACE (HTML WITH BUTTONS)
// ============================================================
if (isWebInterface()) {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Script - Sampah Sehat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.9em;
        }
        .step-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .step-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }
        .step-card.completed {
            background: #d4edda;
            border-color: #28a745;
        }
        .step-card.error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .step-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .step-title {
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
        }
        .step-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .status-pending {
            background: #e9ecef;
            color: #6c757d;
        }
        .status-running {
            background: #fff3cd;
            color: #856404;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .output {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .output:empty {
            display: none;
        }
        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #667eea;
            width: 0%;
            transition: width 0.3s ease;
        }
        .overall-progress {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .overall-progress h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .overall-progress .progress-bar {
            height: 10px;
        }
        .overall-progress .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .final-message {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 600;
        }
        .final-message.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .final-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Deployment Script</h1>
        <p class="subtitle">InfinityFree Deployment - Sampah Sehat Application</p>

        <div class="overall-progress">
            <h3>Overall Progress</h3>
            <div class="progress-bar">
                <div class="progress-fill" id="overallProgress"></div>
            </div>
            <p style="margin-top: 10px; color: #666;">
                <span id="completedSteps">0</span> / <span id="totalSteps">8</span> steps completed
            </p>
        </div>

        <div id="stepsContainer">
            <?php
            $steps = [
                'checkRequirements' => '1. Check Requirements',
                'mergeAndExtractZip' => '2. Merge and Extract ZIP',
                'setupEnvironment' => '3. Setup Environment (.env)',
                'generateAppKey' => '4. Generate APP_KEY',
                'setupStorage' => '5. Setup Storage Directories',
                'runMigrations' => '6. Run Database Migrations',
                'clearCache' => '7. Clear Cache',
                'diagnoseHttp500' => '🔍 Diagnose HTTP 500 Issues',
                'cleanup' => '8. Cleanup Deployment Files',
            ];

            foreach ($steps as $action => $title): ?>
                <div class="step-card" id="step-<?php echo $action; ?>">
                    <div class="step-header">
                        <span class="step-title"><?php echo $title; ?></span>
                        <span class="step-status status-pending" id="status-<?php echo $action; ?>">Pending</span>
                    </div>
                    <button class="btn btn-primary" onclick="runStep('<?php echo $action; ?>')" id="btn-<?php echo $action; ?>">
                        ▶ Run Step
                    </button>
                    <div class="output" id="output-<?php echo $action; ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-success" onclick="runAllSteps()" id="btnRunAll">
                ▶▶ Run All Steps
            </button>
            <button class="btn btn-danger" onclick="resetAll()" id="btnReset">
                ↻ Reset
            </button>
        </div>

        <div class="final-message" id="finalMessage" style="display: none;"></div>
    </div>

    <script>
        const steps = [
            'checkRequirements',
            'mergeAndExtractZip',
            'setupEnvironment',
            'generateAppKey',
            'setupStorage',
            'runMigrations',
            'clearCache',
            'diagnoseHttp500',
            'cleanup'
        ];

        let completedCount = 0;
        let isRunningAll = false;

        function updateProgress() {
            const progress = (completedCount / steps.length) * 100;
            document.getElementById('overallProgress').style.width = progress + '%';
            document.getElementById('completedSteps').textContent = completedCount;
        }

        function setStepStatus(action, status, output = '') {
            const statusEl = document.getElementById('status-' + action);
            const cardEl = document.getElementById('step-' + action);
            const outputEl = document.getElementById('output-' + action);
            const btnEl = document.getElementById('btn-' + action);

            statusEl.className = 'step-status status-' + status;
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

            if (status === 'running') {
                btnEl.disabled = true;
                btnEl.textContent = '⏳ Running...';
            } else if (status === 'success') {
                btnEl.disabled = true;
                btnEl.className = 'btn btn-success';
                btnEl.textContent = '✓ Completed';
                cardEl.classList.add('completed');
                completedCount++;
                updateProgress();
            } else if (status === 'error') {
                btnEl.disabled = false;
                btnEl.className = 'btn btn-primary';
                btnEl.textContent = '↻ Retry';
                cardEl.classList.add('error');
            }

            if (output) {
                outputEl.textContent = output;
                outputEl.style.display = 'block';
            }
        }

        async function runStep(action) {
            setStepStatus(action, 'running');

            const formData = new FormData();
            formData.append('action', action);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    setStepStatus(action, 'success', result.data.output || result.message);
                } else {
                    setStepStatus(action, 'error', result.data.output || result.message);
                }

                if (isRunningAll) {
                    const currentIndex = steps.indexOf(action);
                    if (currentIndex < steps.length - 1) {
                        setTimeout(() => runStep(steps[currentIndex + 1]), 500);
                    } else {
                        isRunningAll = false;
                        showFinalMessage();
                    }
                }
            } catch (error) {
                setStepStatus(action, 'error', 'Network error: ' + error.message);
                isRunningAll = false;
            }
        }

        async function runAllSteps() {
            if (isRunningAll) return;

            isRunningAll = true;
            document.getElementById('btnRunAll').disabled = true;
            document.getElementById('btnReset').disabled = true;

            completedCount = 0;
            updateProgress();

            // Reset all steps first
            steps.forEach(action => {
                const cardEl = document.getElementById('step-' + action);
                const btnEl = document.getElementById('btn-' + action);
                cardEl.classList.remove('completed', 'error');
                btnEl.disabled = false;
                btnEl.className = 'btn btn-primary';
                btnEl.textContent = '▶ Run Step';
                document.getElementById('output-' + action).style.display = 'none';
            });

            // Start with first step
            await runStep(steps[0]);
        }

        function resetAll() {
            if (isRunningAll) return;

            completedCount = 0;
            updateProgress();

            steps.forEach(action => {
                const cardEl = document.getElementById('step-' + action);
                const statusEl = document.getElementById('status-' + action);
                const btnEl = document.getElementById('btn-' + action);
                const outputEl = document.getElementById('output-' + action);

                cardEl.classList.remove('completed', 'error');
                statusEl.className = 'step-status status-pending';
                statusEl.textContent = 'Pending';
                btnEl.disabled = false;
                btnEl.className = 'btn btn-primary';
                btnEl.textContent = '▶ Run Step';
                outputEl.style.display = 'none';
                outputEl.textContent = '';
            });

            document.getElementById('finalMessage').style.display = 'none';
            document.getElementById('btnRunAll').disabled = false;
        }

        function showFinalMessage() {
            const finalMessage = document.getElementById('finalMessage');
            const allSuccess = steps.every(action => {
                const cardEl = document.getElementById('step-' + action);
                return cardEl.classList.contains('completed');
            });

            if (allSuccess) {
                finalMessage.className = 'final-message success';
                finalMessage.innerHTML = `
                    <h2>🎉 Deployment Completed Successfully!</h2>
                    <p style="margin-top: 10px;">Next steps:</p>
                    <ul style="text-align: left; margin-top: 10px; padding-left: 20px;">
                        <li>Edit .env with your database credentials (if not done)</li>
                        <li>Access your domain to test the application</li>
                        <li>If migrations were skipped, run: php artisan migrate --force</li>
                    </ul>
                `;
            } else {
                finalMessage.className = 'final-message error';
                finalMessage.innerHTML = `
                    <h2>❌ Deployment Failed</h2>
                    <p style="margin-top: 10px;">Please check the errors above and retry the failed steps.</p>
                `;
            }

            finalMessage.style.display = 'block';
            document.getElementById('btnRunAll').disabled = false;
            document.getElementById('btnReset').disabled = false;
        }
    </script>
</body>
</html>
    <?php
}

