<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, langsung alihkan ke beranda utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $file_user = 'data/users.json';
    
    if (!file_exists('data')) {
        mkdir('data', 0777, true);
    }
    
    if (!file_exists($file_user)) {
        $default_user = [
            [
                "id" => 1,
                "nama" => "Annisa Kholifatul",
                "email" => "annisa@pelapor.com",
                "role" => "Pelapor",
                "password" => "password123",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 2,
                "nama" => "Budi Koordinator",
                "email" => "koordinator@silaris.com",
                "role" => "Koordinator",
                "password" => "koordinator123",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ]
        ];
        file_put_contents($file_user, json_encode($default_user, JSON_PRETTY_PRINT));
    }

    $users = json_decode(file_get_contents($file_user), true);
    $login_sukses = false;

    if ($users) {
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $login_sukses = true;
                break;
            }
        }
    }

    if ($login_sukses) {
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Email atau password salah! Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengguna - Silaris</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .btn-primary-custom {
            background-color: #2e8b57;
            border-color: #2e8b57;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
        }
        .btn-primary-custom:hover {
            background-color: #246b43;
            border-color: #246b43;
            color: white;
        }
        .form-control:focus {
            border-color: #2e8b57;
            box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.25);
        }
    </style>
</head>
<body>

<div class="card login-card p-4 mx-3">
    <div class="text-center mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 60px; height: 60px;">
            <i class="bi bi-shield-lock text-success fs-2"></i>
        </div>
        <h3 class="fw-bold text-dark">Login Pengguna</h3>
        <p class="text-muted small mb-0">Masuk sebagai Pelapor atau Koordinator</p>
    </div>

    <!-- Role Tabs -->
    <ul class="nav nav-pills nav-fill mb-3 bg-light rounded-3 p-1" id="roleTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-3 fw-semibold small py-2" id="pelapor-tab" data-bs-toggle="pill" data-bs-target="#pelapor-pane" type="button" role="tab">
                <i class="bi bi-person me-1"></i>Pelapor
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-3 fw-semibold small py-2" id="koordinator-tab" data-bs-toggle="pill" data-bs-target="#koordinator-pane" type="button" role="tab">
                <i class="bi bi-person-gear me-1"></i>Koordinator
            </button>
        </li>
    </ul>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center py-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
            <div><?= htmlspecialchars($error_message) ?></div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold text-secondary small">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" id="emailInput" class="form-control py-2" placeholder="annisa@pelapor.com" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold text-secondary small">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="passwordInput" class="form-control py-2" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="text-decoration-none text-success small fw-medium">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>