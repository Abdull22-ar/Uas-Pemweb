<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

// Keamanan: hanya POST dari Pelapor yang login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda harus login terlebih dahulu.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: laporan.php');
    exit();
}

if (($_SESSION['role'] ?? '') !== 'Pelapor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Pelapor yang dapat mengirim laporan.']);
    exit();
}

// Ambil semua input dari form
$nama_pelapor   = trim($_POST['nama_pelapor']   ?? '');
$kontak_pelapor = trim($_POST['kontak_pelapor'] ?? '');
$kategori_id    = trim($_POST['kategori_id']    ?? '');
$lokasi         = trim($_POST['lokasi']         ?? '');
$latitude       = trim($_POST['latitude']       ?? '');
$longitude      = trim($_POST['longitude']      ?? '');
$deskripsi      = trim($_POST['deskripsi']      ?? '');

// Validasi sederhana di sisi server
if (empty($nama_pelapor) || empty($kontak_pelapor) || empty($kategori_id) || empty($lokasi) || empty($deskripsi)) {
    echo '<script>';
    echo 'alert("Harap lengkapi semua field yang wajib diisi.");';
    echo 'window.location="laporan.php";';
    echo '</script>';
    exit();
}

$api_url = API_BASE_URL . '/api/laporan';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Agar tidak error SSL di hosting shared

$postData = [
    'nama_pelapor'   => $nama_pelapor,
    'kontak_pelapor' => $kontak_pelapor,
    'kategori_id'    => $kategori_id,
    'lokasi'         => $lokasi,
    'latitude'       => $latitude,
    'longitude'      => $longitude,
    'deskripsi'      => $deskripsi,
];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $postData['foto'] = new CURLFile(
        $_FILES['foto']['tmp_name'],
        $_FILES['foto']['type'],
        $_FILES['foto']['name']
    );
}

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$response    = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error  = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo '<script>';
    echo 'alert(' . json_encode('Gagal terhubung ke server: ' . $curl_error) . ');';
    echo 'window.location="laporan.php";';
    echo '</script>';
    exit();
}

$resData = json_decode($response, true);

if ($http_status >= 200 && $http_status < 300 && !empty($resData['success'])) {
    $kode = $resData['data']['kode_laporan'] ?? '';
    $pesan = 'Laporan berhasil dikirim!';
    if ($kode !== '') {
        $pesan .= ' Kode Laporan Anda: ' . $kode;
    }
    echo '<script>';
    echo 'alert(' . json_encode($pesan) . ');';
    $redirect = $kode !== '' ? 'cek-status.php?kode=' . rawurlencode($kode) : 'daftar-laporan.php';
    echo 'window.location=' . json_encode($redirect) . ';';
    echo '</script>';
} else {
    $errorMsg = $resData['message'] ?? 'Terjadi kesalahan sistem. Coba lagi nanti.';
    if (!empty($resData['errors'])) {
        $errDetails = [];
        foreach ($resData['errors'] as $field => $msgs) {
            $errDetails[] = implode(', ', (array) $msgs);
        }
        $errorMsg .= "\n" . implode("\n", $errDetails);
    }
    echo '<script>';
    echo 'alert(' . json_encode('Gagal mengirim laporan:\n' . $errorMsg) . ');';
    echo 'window.location="laporan.php";';
    echo '</script>';
}
?>
