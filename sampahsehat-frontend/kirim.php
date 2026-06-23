<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak");
}

$nama_pelapor   = $_POST['nama'] ?? $_SESSION['nama'];
$kontak_pelapor = $_SESSION['email']; 
$kategori_id    = $_POST['kategori_id'];
$lokasi         = $_POST['lokasi'];
$latitude       = $_POST['latitude']; 
$longitude      = $_POST['longitude'];
$deskripsi      = $_POST['deskripsi'];

$api_url = API_BASE_URL . '/laporan';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);

$postData = [
    'nama_pelapor' => $nama_pelapor,
    'kontak_pelapor' => $kontak_pelapor,
    'kategori_id' => $kategori_id,
    'lokasi' => $lokasi,
    'latitude' => $latitude,
    'longitude' => $longitude,
    'deskripsi' => $deskripsi,
];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $postData['foto'] = new CURLFile($_FILES['foto']['tmp_name'], $_FILES['foto']['type'], $_FILES['foto']['name']);
}

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resData = json_decode($response, true);

if ($http_status >= 200 && $http_status < 300) {
    $kode = isset($resData['data']['kode_laporan']) ? $resData['data']['kode_laporan'] : '';
    echo "<script>
    alert('Laporan berhasil dikirim dengan Kode Laporan: $kode');
    window.location='daftar-laporan.php';
    </script>";
} else {
    $errorMsg = isset($resData['message']) ? $resData['message'] : 'Terjadi kesalahan sistem.';
    if (isset($resData['errors'])) {
        $errorMsg .= " " . implode(", ", array_map(function($err) { return implode(", ", $err); }, $resData['errors']));
    }
    echo "<script>
    alert('Gagal mengirim laporan: " . addslashes($errorMsg) . "');
    window.history.back();
    </script>";
}
?>