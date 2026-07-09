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
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $dir_user  = __DIR__ . '/data';
    $file_user = $dir_user . '/users.json';

    if (!is_dir($dir_user)) {
        mkdir($dir_user, 0777, true);
    }

    if (!file_exists($file_user)) {
        $default_users = [
            [
                "id"         => 1,
                "nama"       => "User",
                "email"      => "user@pelapor.com",
                "role"       => "Pelapor",
                "password"   => "password",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id"         => 2,
                "nama"       => "Budi Koordinator",
                "email"      => "koordinator@silaris.com",
                "role"       => "Koordinator",
                "password"   => "koordinator123",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ]
        ];
        file_put_contents($file_user, json_encode($default_users, JSON_PRETTY_PRINT));
    }

    $users        = json_decode(file_get_contents($file_user), true);
    $login_sukses = false;

    if (is_array($users)) {
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];
                $login_sukses        = true;
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
    <title>Login Pengguna - SilariS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #e3f2fd 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            max-width: 430px;
            width: 100%;
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
        }
        .btn-primary-custom {
            background-color: #2e8b57;
            border-color: #2e8b57;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .btn-primary-custom:hover {
            background-color: #246b43;
            border-color: #246b43;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(46,139,87,0.3);
        }
        .form-control:focus, .form-select:focus {
            border-color: #2e8b57;
            box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.2);
        }
        .nav-link.active {
            background-color: #2e8b57 !important;
            color: white !important;
        }
        .nav-link:not(.active) {
            color: #555;
        }
    </style>
</head>
<body>

<div class="card login-card p-4 p-md-5 mx-2">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3" style="width:64px;height:64px;">
            <i class="bi bi-shield-lock text-success fs-2"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Login Pengguna</h3>
        <p class="text-muted small mb-0">Masuk sebagai Pelapor atau Koordinator</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 small" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Role Tabs -->
    <ul class="nav nav-pills nav-fill mb-4 bg-light rounded-3 p-1" id="roleTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-3 fw-semibold small py-2" id="pelapor-tab"
                    type="button" data-role="pelapor" aria-pressed="true">
                <i class="bi bi-person me-1"></i>Pelapor
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-3 fw-semibold small py-2" id="koordinator-tab"
                    type="button" data-role="koordinator" aria-pressed="false">
                <i class="bi bi-person-badge me-1"></i>Koordinator
            </button>
        </li>
    </ul>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="emailInput" class="form-label fw-semibold small text-secondary">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" id="emailInput"
                       class="form-control py-2"
                       placeholder="email@contoh.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="passwordInput" class="form-label fw-semibold small text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="passwordInput"
                       class="form-control py-2"
                       placeholder="••••••••" required>
                <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                        aria-label="Tampilkan atau sembunyikan password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="bg-light rounded-3 p-3 small text-muted mb-4" id="loginHint">
            Pilih tab role untuk mengisi otomatis akun demo.
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
    </form>

    <div class="text-center mt-2">
        <a href="index.php" class="text-decoration-none text-success small fw-medium">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput    = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const loginHint     = document.getElementById('loginHint');
    const togglePwd     = document.getElementById('togglePassword');

    const roleDefaults = {
        pelapor: {
            email: 'user@pelapor.com',
            password: 'password',
            hint: 'Akun demo Pelapor: <strong>user@pelapor.com</strong> / <strong>password</strong>'
        },
        koordinator: {
            email: 'koordinator@silaris.com',
            password: 'koordinator123',
            hint: 'Akun demo Koordinator: <strong>koordinator@silaris.com</strong> / <strong>koordinator123</strong>'
        }
    };

    function gunakanRole(role) {
        const data = roleDefaults[role];
        if (!data) return;

        document.querySelectorAll('#roleTab .nav-link').forEach(function (btn) {
            const aktif = btn.dataset.role === role;
            btn.classList.toggle('active', aktif);
            btn.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });

        emailInput.value    = data.email;
        passwordInput.value = data.password;
        loginHint.innerHTML = data.hint;
    }

    document.querySelectorAll('#roleTab .nav-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            gunakanRole(this.dataset.role);
        });
    });

    togglePwd.addEventListener('click', function () {
        const visible = passwordInput.type === 'text';
        passwordInput.type  = visible ? 'password' : 'text';
        this.innerHTML = visible
            ? '<i class="bi bi-eye"></i>'
            : '<i class="bi bi-eye-slash"></i>';
    });

    // Auto-fill Pelapor saat halaman pertama kali dibuka
    if (!emailInput.value) {
        gunakanRole('pelapor');
    }
});
</script>
</body>
</html>
