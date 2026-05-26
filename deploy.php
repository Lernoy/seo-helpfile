<?php
// deploy.php — скачивает актуальные исходники с GitHub и собирает helpfile.php
// Удалить с сервера после использования

define('DEPLOY_PASSWORD', 'Qazwsxed35');
define('REPO_RAW', 'https://raw.githubusercontent.com/Lernoy/seo-helpfile/master/');
define('PARTS', [
    'src/01_init_auth.php',
    'src/02_cms_functions.php',
    'src/03_functions_files.php',
    'src/04_ajax_docx.php',
    'src/05_ajax_seo.php',
    'src/06_html_head.php',
    'src/07_html_body.php',
    'src/08_js.php',
]);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pw'])) {
    if ($_POST['pw'] === DEPLOY_PASSWORD) {
        $_SESSION['deploy_ok'] = true;
    } else {
        $error = 'Неверный пароль';
    }
}

function fetch_url($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'deploy.php/1.0',
        ]);
        $body = curl_exec($ch);
        $ok   = !curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) < 400;
        curl_close($ch);
        if ($ok && $body !== '') return $body;
    }
    if (ini_get('allow_url_fopen')) {
        $body = @file_get_contents($url);
        if ($body !== false && $body !== '') return $body;
    }
    return false;
}

$deployed = false;
$errors   = [];

if (!empty($_SESSION['deploy_ok']) && isset($_POST['do_deploy'])) {
    $out = '';
    foreach (PARTS as $i => $part) {
        $content = fetch_url(REPO_RAW . $part);
        if ($content === false) {
            $errors[] = $part;
            continue;
        }
        if ($i > 0) {
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // strip BOM
            $content = preg_replace('/^<\?php\s*/u', '', $content);
            $out .= "\n";
        }
        $out .= $content;
    }

    if (empty($errors)) {
        // Вшиваем SHA последнего коммита, чтобы helpfile.php умел проверять обновления
        $apiData = fetch_url('https://api.github.com/repos/Lernoy/seo-helpfile/commits/master');
        if ($apiData) {
            $commit = json_decode($apiData, true);
            if (!empty($commit['sha'])) {
                $sha7 = substr($commit['sha'], 0, 7);
                $out  = str_replace("define('HELPFILE_BUILD', 'dev')", "define('HELPFILE_BUILD', '{$sha7}')", $out);
            }
        }
        file_put_contents(__DIR__ . '/helpfile.php', $out);
        $deployed = true;
    }
}

if (empty($_SESSION['deploy_ok'])):
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deploy — SEO Helpfile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef0f3; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; border: 1px solid #d8dde6; border-radius: 4px; padding: 36px; width: 320px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        h2 { font-size: 16px; margin: 0 0 20px; color: #333; }
        input[type=password] { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #d0d5dd; border-radius: 3px; font-size: 14px; margin-bottom: 14px; }
        button { width: 100%; padding: 10px; background: #4a90d9; color: #fff; border: none; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; }
        button:hover { background: #357abd; }
        .err { color: #c0392b; font-size: 12px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Деплой SEO Helpfile</h2>
    <?php if (!empty($error)): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <input type="password" name="pw" placeholder="Пароль" autofocus>
        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>
<?php else: ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deploy — SEO Helpfile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef0f3; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; border: 1px solid #d8dde6; border-radius: 4px; padding: 36px; width: 380px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        h2 { font-size: 16px; margin: 0 0 16px; color: #333; }
        .info { font-size: 12px; color: #6b7280; margin-bottom: 20px; line-height: 1.6; }
        .info code { background: #f1f5f9; padding: 1px 5px; border-radius: 2px; font-size: 11px; }
        button { width: 100%; padding: 11px; background: #27ae60; color: #fff; border: none; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; }
        button:hover { background: #219a52; }
        .ok { background: #f0fdf4; border: 1px solid #86efac; border-radius: 4px; padding: 16px; text-align: center; }
        .ok a { display: inline-block; margin-top: 12px; padding: 10px 24px; background: #4a90d9; color: #fff; border-radius: 3px; text-decoration: none; font-weight: 600; font-size: 14px; }
        .ok a:hover { background: #357abd; }
        .err-list { background: #fff0f0; border: 1px solid #fca5a5; border-radius: 4px; padding: 12px; font-size: 12px; color: #c0392b; margin-top: 14px; }
        .err-list li { margin-top: 4px; }
    </style>
</head>
<body>
<div class="box">
<?php if ($deployed): ?>
    <div class="ok">
        <strong style="color:#166534;font-size:15px">Готово!</strong><br>
        <span style="font-size:12px;color:#6b7280">helpfile.php собран из <?= count(PARTS) ?> частей</span>
        <br><a href="/helpfile.php">Открыть helpfile.php →</a>
    </div>
<?php elseif (!empty($errors)): ?>
    <h2>Ошибка загрузки</h2>
    <div class="err-list">Не удалось скачать:<ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <form method="POST" style="margin-top:16px">
        <input type="hidden" name="do_deploy" value="1">
        <button type="submit">Повторить</button>
    </form>
<?php else: ?>
    <h2>Деплой SEO Helpfile</h2>
    <div class="info">
        Скачает <?= count(PARTS) ?> файлов из репозитория<br>
        <code>github.com/Lernoy/seo-helpfile</code><br>
        и соберёт <code>helpfile.php</code> в корне сайта.
    </div>
    <form method="POST">
        <input type="hidden" name="do_deploy" value="1">
        <button type="submit">Скачать и собрать</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
<?php endif; ?>
