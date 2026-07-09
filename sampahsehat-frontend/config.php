<?php
// Konfigurasi base URL aplikasi backend/API.
// Simpan tanpa trailing slash agar konsisten saat digabung dengan path endpoint.
//
// UNTUK DEPLOYMENT PRODUCTION:
$backendBaseUrl = 'http://admin.silaris.my.id/backend/public';

// UNTUK DEVELOPMENT (LOCALHOST):
// $backendBaseUrl = 'http://127.0.0.1:8000';

define('API_BASE_URL', rtrim($backendBaseUrl, '/'));
define('ADMIN_DASHBOARD_URL', API_BASE_URL . '/admin/dashboard');
?>
