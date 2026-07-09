<?php
/**
 * Manual cache clearing script for Laravel without terminal access
 * Access via: http://your-domain.com/clear_cache_manual.php
 */

$baseDir = dirname(__DIR__);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Clear Laravel Cache</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }";
echo "h1 { color: #333; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo ".info { color: blue; }";
echo "pre { background: #f9f9f9; padding: 10px; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🧹 Clear Laravel Cache (Manual)</h1>";

$actions = [
    'Clear Application Cache' => function() use ($baseDir) {
        $cacheDir = $baseDir . '/bootstrap/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.php');
            foreach ($files as $file) {
                @unlink($file);
            }
            return "Cleared " . count($files) . " files from bootstrap/cache";
        }
        return "bootstrap/cache directory not found";
    },
    'Clear Config Cache' => function() use ($baseDir) {
        $configCache = $baseDir . '/bootstrap/cache/config.php';
        if (file_exists($configCache)) {
            @unlink($configCache);
            return "Cleared config cache";
        }
        return "Config cache not found";
    },
    'Clear Routes Cache' => function() use ($baseDir) {
        $routesCache = $baseDir . '/bootstrap/cache/routes-v7.php';
        if (file_exists($routesCache)) {
            @unlink($routesCache);
            return "Cleared routes cache";
        }
        return "Routes cache not found";
    },
    'Clear Views Cache' => function() use ($baseDir) {
        $viewsDir = $baseDir . '/storage/framework/views';
        if (is_dir($viewsDir)) {
            $files = glob($viewsDir . '/*.php');
            foreach ($files as $file) {
                @unlink($file);
            }
            return "Cleared " . count($files) . " view cache files";
        }
        return "Views cache directory not found";
    },
    'Clear Compiled Views' => function() use ($baseDir) {
        $compiledDir = $baseDir . '/storage/framework/cache/data';
        if (is_dir($compiledDir)) {
            $files = glob($compiledDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            return "Cleared compiled cache";
        }
        return "Compiled cache directory not found";
    },
    'Clear Session Files' => function() use ($baseDir) {
        $sessionDir = $baseDir . '/storage/framework/sessions';
        if (is_dir($sessionDir)) {
            $files = glob($sessionDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            return "Cleared " . count($files) . " session files";
        }
        return "Session directory not found";
    },
];

echo "<h2>Cache Clearing Results:</h2>";

foreach ($actions as $name => $action) {
    try {
        $result = $action();
        echo "<p class='success'>✅ $name: $result</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ $name: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h2>Additional Actions:</h2>";

// Try to regenerate autoloader
echo "<p class='info'>🔄 Attempting to regenerate autoloader...</p>";
$autoloadFile = $baseDir . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
    echo "<p class='success'>✅ Autoloader loaded successfully</p>";
} else {
    echo "<p class='error'>❌ Autoloader not found</p>";
}

// Check if composer dump-autoload is needed
echo "<p class='info'>ℹ️  If the error persists, you may need to run:</p>";
echo "<pre>composer dump-autoload</pre>";
echo "<p class='info'>ℹ️  Or upload a fresh vendor folder from local development</p>";

echo "<hr>";
echo "<p><a href='update.php'>← Back to Update Script</a></p>";
echo "<p><a href='view_logs.php'>View Laravel Logs</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
