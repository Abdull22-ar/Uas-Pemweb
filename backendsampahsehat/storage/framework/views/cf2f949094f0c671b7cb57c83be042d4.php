<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Admin - Silaris Sistem Pengelolaan Laporan Sampah">
    <title>Login Admin – Silaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-base: #0f1117;
            --bg-surface: #161b27;
            --bg-card: #1e2536;
            --accent: #22c55e;
            --accent-dark: #16a34a;
            --accent-glow: rgba(34,197,94,0.2);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: rgba(255,255,255,0.07);
            --border-accent: rgba(34,197,94,0.4);
            --danger: #ef4444;
            --radius: 16px;
            --radius-sm: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
            animation: blobFloat 8s ease-in-out infinite;
        }

        .blob-1 {
            width: 500px; height: 500px;
            background: rgba(34,197,94,0.08);
            top: -150px; left: -100px;
            animation-delay: 0s;
        }

        .blob-2 {
            width: 400px; height: 400px;
            background: rgba(59,130,246,0.06);
            bottom: -100px; right: -80px;
            animation-delay: 3s;
        }

        .blob-3 {
            width: 300px; height: 300px;
            background: rgba(139,92,246,0.05);
            top: 40%; left: 50%;
            animation-delay: 5s;
        }

        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(20px, -20px) scale(1.05); }
            66%       { transform: translate(-15px, 15px) scale(0.95); }
        }

        /* Grid pattern */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 16px;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            animation: slideUp .5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-header { text-align: center; margin-bottom: 36px; }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border-radius: 16px;
            font-size: 28px;
            margin-bottom: 20px;
            box-shadow: 0 0 40px var(--accent-glow);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px var(--accent-glow); }
            50%       { box-shadow: 0 0 40px rgba(34,197,94,0.4); }
        }

        .login-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .login-subtitle { font-size: 14px; color: var(--text-muted); }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
            transition: color .2s;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: var(--border-accent);
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12);
        }

        .form-control:focus ~ .input-icon { color: var(--accent); }
        .form-control::placeholder { color: var(--text-muted); }
        .form-control.is-invalid { border-color: rgba(239,68,68,0.5); }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color .2s;
        }

        .toggle-password:hover { color: var(--text-secondary); }

        .form-error {
            font-size: 12px;
            color: #f87171;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .remember-row input[type="checkbox"] { accent-color: var(--accent); width: 15px; height: 15px; cursor: pointer; }
        .remember-row label { font-size: 13px; color: var(--text-secondary); cursor: pointer; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border: none;
            border-radius: var(--radius-sm);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 20px var(--accent-glow);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(34,197,94,0.4);
        }

        .btn-login:active { transform: translateY(0); }

        .public-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .public-link a { color: var(--accent); text-decoration: none; font-weight: 500; }
        .public-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Logo Silaris" style="height: 64px; width: auto; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;">
                <h1 class="login-title">Selamat Datang</h1>
                <p class="login-subtitle">Masuk ke Panel Admin Silaris</p>
            </div>

            <?php if($errors->any() && !$errors->has('email')): ?>
                <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login.post')); ?>" id="loginForm">
                <?php echo csrf_field(); ?>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="admin@silaris.id"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        <i class="bi bi-envelope input-icon"></i>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Remember me -->
                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk ke Dashboard
                </button>
            </form>        </div>

        <div class="public-link">
            Bukan admin? <a href="<?php echo e(rtrim(env('FRONTEND_URL', 'http://localhost:8080'), '/')); ?>/laporan.php">Buat laporan sampah</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;border:2px solid #fff;border-top-color:transparent;border-radius:50%;width:16px;height:16px;"></span> Memproses...';
            btn.disabled = true;
        });
    </script>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</body>
</html>

<?php /**PATH C:\UAS\backendsampahsehat\resources\views/admin/auth/login.blade.php ENDPATH**/ ?>