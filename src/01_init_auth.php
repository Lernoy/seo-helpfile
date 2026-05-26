<?php
// Файл: /helpfile.php (в корне сайта, удалить после использования)

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

define('HELPFILE_DEBUG', false); // Включите для отладки (лог в helpfile_debug.log)
ini_set('error_log', __DIR__ . '/helpfile_debug.log');
ini_set('log_errors', '1');

// ===================== ОПРЕДЕЛЕНИЕ CMS =====================
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$isBitrix = file_exists($docRoot . '/bitrix/modules/main/include/prolog_before.php');
$isWordpress = file_exists($docRoot . '/wp-load.php');

if ($isBitrix) {
    define('CMS_TYPE', 'BITRIX');
} elseif ($isWordpress) {
    define('CMS_TYPE', 'WORDPRESS');
} else {
    define('CMS_TYPE', 'NONE');
}

// ===================== АВТОРИЗАЦИЯ =====================
define('HELPFILE_PASSWORD',    'Qazwsxed35');
define('HELPFILE_SECRET_FILE', __DIR__ . '/.helpfile_secret');

function hf_active_password() {
    if (file_exists(HELPFILE_SECRET_FILE)) {
        $p = trim(file_get_contents(HELPFILE_SECRET_FILE));
        if ($p !== '') return $p;
    }
    return HELPFILE_PASSWORD;
}

function hf_generate_password() {
    $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$';
    $pwd = '';
    for ($i = 0; $i < 18; $i++) {
        $pwd .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pwd;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['helpfile_login'])) {
    $activePassword = hf_active_password();
    if ($_POST['helpfile_password'] === $activePassword) {
        $_SESSION['helpfile_auth'] = true;
        // Первый вход по дефолтному паролю — генерируем постоянный
        if (!file_exists(HELPFILE_SECRET_FILE)) {
            $newPwd = hf_generate_password();
            file_put_contents(HELPFILE_SECRET_FILE, $newPwd);
            $_SESSION['helpfile_new_password'] = $newPwd;
        }
    } else {
        $loginError = 'Неверный пароль';
    }
}

if (isset($_POST['helpfile_logout'])) {
    $_SESSION['helpfile_auth'] = false;
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ── Показ нового пароля (один раз после первого входа) ──
if (!empty($_SESSION['helpfile_auth']) && !empty($_SESSION['helpfile_new_password'])) {
    $shownPwd = $_SESSION['helpfile_new_password'];
    unset($_SESSION['helpfile_new_password']);
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SEO Meta Editor — Новый пароль</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Helvetica Neue", Arial, sans-serif; background: #eef0f3; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .wrap { background: #fff; border: 1px solid #d8dde6; border-radius: 4px; width: 420px; padding: 40px 36px 36px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .icon { text-align: center; margin-bottom: 20px; }
        h2 { font-size: 17px; font-weight: 700; color: #333; text-align: center; margin-bottom: 8px; }
        .sub { font-size: 12px; color: #e67e22; text-align: center; margin-bottom: 28px; font-weight: 600; }
        .pwd-box { display: flex; align-items: center; gap: 8px; background: #f5f7fa; border: 1px solid #d0d5dd; border-radius: 4px; padding: 12px 14px; margin-bottom: 20px; }
        .pwd-val { flex: 1; font-family: "Courier New", monospace; font-size: 16px; font-weight: 700; color: #1a1a2e; letter-spacing: 1px; word-break: break-all; }
        .btn-copy { background: #4a90d9; color: #fff; border: none; border-radius: 3px; padding: 7px 14px; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s; }
        .btn-copy:hover { background: #357abd; }
        .btn-copy.ok { background: #27ae60; }
        .warn { background: #fff8e1; border: 1px solid #ffe082; border-radius: 3px; padding: 10px 14px; font-size: 12px; color: #795548; margin-bottom: 24px; line-height: 1.6; }
        .btn-go { display: block; width: 100%; padding: 11px; background: #27ae60; color: #fff; border: none; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; text-align: center; text-decoration: none; }
        .btn-go:hover { background: #219a52; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="icon">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
            <rect width="48" height="48" rx="8" fill="#27ae60"/>
            <path d="M20 32l-8-8 2.8-2.8L20 26.4l13.2-13.2L36 16z" fill="white"/>
        </svg>
    </div>
    <h2>Ваш персональный пароль</h2>
    <div class="sub">Показывается только один раз — сохраните его!</div>
    <div class="pwd-box">
        <span class="pwd-val" id="pwd"><?= htmlspecialchars($shownPwd) ?></span>
        <button class="btn-copy" id="copyBtn" onclick="
            navigator.clipboard.writeText(document.getElementById('pwd').textContent).then(function(){
                var b = document.getElementById('copyBtn');
                b.textContent = 'Скопировано!';
                b.classList.add('ok');
                setTimeout(function(){ b.textContent = 'Копировать'; b.classList.remove('ok'); }, 2000);
            });
        ">Копировать</button>
    </div>
    <div class="warn">
        Дефолтный пароль больше не работает.<br>
        Этот пароль сохранён на сервере и будет использоваться при всех следующих входах.
    </div>
    <a class="btn-go" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Я сохранил пароль — войти →</a>
</div>
</body>
</html>
<?php
    exit;
}

if (empty($_SESSION['helpfile_auth'])) {
    header('Content-Type: text/html; charset=utf-8');
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>SEO Meta Editor — Вход</title>
        <style>
            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: "Helvetica Neue", Arial, sans-serif;
                background: #eef0f3;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-wrap {
                background: #fff;
                border: 1px solid #d8dde6;
                border-radius: 4px;
                width: 360px;
                padding: 40px 36px 36px;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
                animation: fadeUp 0.4s ease both;
            }

            @keyframes fadeUp {
                from {
                    opacity: 0;
                    transform: translateY(18px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .login-logo {
                text-align: center;
                margin-bottom: 28px;
            }

            .login-logo svg {
                width: 48px;
                height: 48px;
            }

            .login-title {
                font-size: 17px;
                font-weight: 600;
                color: #333;
                text-align: center;
                margin-bottom: 6px;
            }

            .login-sub {
                font-size: 12px;
                color: #9aa0ab;
                text-align: center;
                margin-bottom: 28px;
            }

            .field {
                margin-bottom: 18px;
            }

            label {
                display: block;
                font-size: 12px;
                color: #6b7280;
                margin-bottom: 5px;
                font-weight: 500;
            }

            input[type=password] {
                width: 100%;
                padding: 9px 12px;
                border: 1px solid #d0d5dd;
                border-radius: 3px;
                font-size: 14px;
                color: #1a1a2e;
                transition: border-color 0.2s, box-shadow 0.2s;
                outline: none;
            }

            input[type=password]:focus {
                border-color: #4a90d9;
                box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
            }

            .error {
                background: #fff0f0;
                border: 1px solid #fca5a5;
                color: #c0392b;
                padding: 8px 12px;
                border-radius: 3px;
                font-size: 12px;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 6px;
                animation: shake 0.35s ease;
            }

            @keyframes shake {

                0%,
                100% {
                    transform: translateX(0);
                }

                20%,
                60% {
                    transform: translateX(-5px);
                }

                40%,
                80% {
                    transform: translateX(5px);
                }
            }

            .btn-login {
                width: 100%;
                padding: 10px;
                background: #4a90d9;
                color: #fff;
                border: none;
                border-radius: 3px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s, transform 0.1s;
            }

            .btn-login:hover {
                background: #357abd;
            }

            .btn-login:active {
                transform: scale(0.98);
            }
        </style>
    </head>

    <body>
        <div class="login-wrap">
            <div class="login-logo">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="8" fill="#4a90d9" />
                    <path d="M14 34V20l10-8 10 8v14H28v-8h-8v8H14z" fill="white" />
                </svg>
            </div>
            <div class="login-title">SEO Meta Editor</div>
            <div class="login-sub">Панель управления метатегами</div>
            <?php if (!empty($loginError)): ?>
                <div class="error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <?= htmlspecialchars($loginError) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="helpfile_login" value="1">
                <div class="field">
                    <label>Пароль</label>
                    <input type="password" name="helpfile_password" autofocus placeholder="Введите пароль">
                </div>
                <button type="submit" class="btn-login">Войти</button>
            </form>
        </div>
    </body>

    </html>
<?php
    exit;
}