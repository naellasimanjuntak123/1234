<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if (!$name || !$email || !$password) {
        $error = 'Nama, email, dan kata sandi harus diisi.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal 8 karakter.'; #Kebijakan password lebih kuat
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
    $error = 'Nomor telepon tidak valid.'; #Validasi nomor telepon
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            if ($stmt->execute([$name, $email, $hashed, $phone, $address])) {
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
    .input-eye {
        position: relative;
    }

    .input-eye input {
        width: 100%;
        padding-right: 42px;
        box-sizing: border-box;
    }

    .input-eye .eye-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        font-size: 15px;
        padding: 0;
    }

    .input-eye .eye-btn:hover {
        color: #475569;
    }

    .pass-hint {
        font-size: 12px;
        margin-top: 4px;
    }

    .match-hint {
        font-size: 12px;
        margin-top: 4px;
    }
    </style>
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1><i class="fas fa-snowflake"></i> FrozenFood</h1>
                <p>Buat akun baru Anda</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php">Login sekarang</a>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap" required
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi <span style="color:red">*</span></label>
                    <div class="input-eye">
                        <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                            minlength="8" required oninput="checkStrength()"> #Kebijakan password lebih kuat
                        <button type="button" class="eye-btn" onclick="toggleEye('password','eyeIcon1')">
                            <i class="fas fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                    <div class="pass-hint" id="passHint"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Kata Sandi <span style="color:red">*</span></label>
                    <div class="input-eye">
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Ulangi kata sandi" required oninput="checkMatch()">
                        <button type="button" class="eye-btn" onclick="toggleEye('confirm_password','eyeIcon2')">
                            <i class="fas fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                    <div class="match-hint" id="matchHint"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
            </form>

            <div class="login-footer">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script>
    function toggleEye(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    function checkStrength() {
        const val = document.getElementById("password").value;
        const hint = document.getElementById("passHint");
        const len = val.length;

        if (len === 0) {
            hint.textContent = "";
            return;
        }
        if (len < 8) { #Kebijakan password lebih kuat
            hint.textContent = "Kata sandi terlalu pendek (" + len + "/6 karakter)";
            hint.style.color = "#ef4444";
        } else {
            hint.textContent = "✅ Kata sandi valid!";
            hint.style.color = "#10b981";
        }
        checkMatch();
    }

    function checkMatch() {
        const pass = document.getElementById("password").value;
        const confirm = document.getElementById("confirm_password").value;
        const hint = document.getElementById("matchHint");
        if (confirm.length === 0) {
            hint.textContent = "";
            return;
        }
        if (pass === confirm) {
            hint.textContent = "✅ Kata sandi cocok";
            hint.style.color = "#10b981";
        } else {
            hint.textContent = "❌ Kata sandi tidak cocok";
            hint.style.color = "#ef4444";
        }
    }

    function validateForm() {
        const pass = document.getElementById("password").value;
        const confirm = document.getElementById("confirm_password").value;
        if (pass.length < 8) {
            alert("Kata sandi minimal 8 karakter!"); #Kebijakan password lebih kuat
            return false;
        }
        if (pass !== confirm) {
            alert("Konfirmasi kata sandi tidak cocok!");
            return false;
        }
        return true;
    }
    </script>
</body>

</html>