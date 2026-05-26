<?php
/**
 * Сборка: объединяет src/*.php в один helpfile.php
 * Запуск: php build.php
 */

$parts = glob(__DIR__ . '/src/*.php');
sort($parts);

$out = '';
foreach ($parts as $i => $file) {
    $content = file_get_contents($file);
    if ($i === 0) {
        $out .= $content;
    } else {
        // Убираем открывающий <?php из всех частей кроме первой
        // (части 07/08 начинаются с HTML — тег и так отсутствует)
        $content = preg_replace('/^<\?php\s*/u', '', $content);
        $out .= "\n" . $content;
    }
}

file_put_contents(__DIR__ . '/helpfile.php', $out);

$bytes = strlen($out);
$lines = substr_count($out, "\n") + 1;
echo "Built helpfile.php: {$bytes} bytes, ~{$lines} lines\n";
