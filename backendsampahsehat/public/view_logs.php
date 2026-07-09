<?php
/**
 * Simple script to view Laravel logs without terminal access
 * Access via: http://your-domain.com/view_logs.php
 */

$logFile = dirname(__DIR__) . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "<h2>Laravel Log File Not Found</h2>";
    echo "<p>Path: $logFile</p>";
    echo "<p>The log file doesn't exist yet.</p>";
    exit;
}

if (!is_readable($logFile)) {
    echo "<h2>Laravel Log File Not Readable</h2>";
    echo "<p>Path: $logFile</p>";
    echo "<p>Check file permissions.</p>";
    exit;
}

// Read last 100 lines of log
$lines = file($logFile);
$lastLines = array_slice($lines, -100);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Laravel Logs</title>";
echo "<style>";
echo "body { font-family: monospace; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }";
echo "h1 { color: #333; }";
echo ".log-entry { padding: 10px; margin: 5px 0; border-left: 4px solid #ccc; background: #f9f9f9; }";
echo ".log-error { border-left-color: #dc3545; background: #f8d7da; }";
echo ".log-warning { border-left-color: #ffc107; background: #fff3cd; }";
echo ".log-info { border-left-color: #17a2b8; background: #d1ecf1; }";
echo "pre { white-space: pre-wrap; word-wrap: break-word; }";
echo ".btn { padding: 10px 20px; margin: 10px 5px; border: none; border-radius: 5px; cursor: pointer; }";
echo ".btn-primary { background: #007bff; color: white; }";
echo ".btn-danger { background: #dc3545; color: white; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>📋 Laravel Logs (Last 100 Lines)</h1>";
echo "<p>File: $logFile</p>";

echo "<button class='btn btn-primary' onclick='location.reload()'>🔄 Refresh</button>";
echo "<button class='btn btn-danger' onclick='clearLogs()'>🗑️ Clear Logs</button>";

echo "<hr>";

foreach ($lastLines as $line) {
    $line = htmlspecialchars($line);
    $class = 'log-entry';

    if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
        $class .= ' log-error';
    } elseif (strpos($line, 'WARNING') !== false || strpos($line, 'Warning') !== false) {
        $class .= ' log-warning';
    } elseif (strpos($line, 'INFO') !== false) {
        $class .= ' log-info';
    }

    echo "<div class='$class'><pre>$line</pre></div>";
}

echo "</div>";

echo "<script>";
echo "function clearLogs() {";
echo "  if (confirm('Are you sure you want to clear the log file?')) {";
echo "    window.location.href = '?action=clear';";
echo "  }";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";

// Handle log clearing
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    file_put_contents($logFile, '');
    echo "<script>window.location.href = 'view_logs.php';</script>";
}
