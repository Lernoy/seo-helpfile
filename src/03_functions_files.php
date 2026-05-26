<?php
// ===================== ОБЩИЕ ФУНКЦИИ СКАНИРОВАНИЯ ФАЙЛОВ =====================

function getMetaFromFile($url)
{
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $trimmed = rtrim($url, '/');
    $paths = [
        $docRoot . $trimmed . '/index.php',
        $docRoot . $url . 'index.php',
        $docRoot . $trimmed . '.php',
    ];
    foreach ($paths as $path) {
        if (!file_exists($path)) continue;

        if (CMS_TYPE === 'BITRIX' && function_exists('ParseFileContent')) {
            $content = file_get_contents($path);
            $arPageSlice = ParseFileContent($content);

            // В Битриксе заголовок окна <title> (браузера) хранится в свойствах (title)
            $titleCode = 'title';
            if (class_exists('COption')) {
                $titleCode = COption::GetOptionString('seo', 'property_window_title', 'title');
            }

            $title = $arPageSlice["PROPERTIES"][$titleCode] ?? '';
            if (empty($title)) {
                // Если свойство title не задано в свойствах, берем SetTitle (h1)
                $title = $arPageSlice["TITLE"] ?? '';
            }

            return [
                'title' => $title,
                'description' => $arPageSlice["PROPERTIES"]["description"] ?? ''
            ];
        }

        // Альтернативный разбор для не-Битрикс систем
        $content     = file_get_contents($path);
        $title       = '';
        $description = '';
        if (preg_match('/SetPageProperty\s*\(\s*[\'"]title[\'"]\s*,\s*[\'"](.+?)[\'"]\s*\)/u', $content, $m))
            $title = $m[1];
        elseif (preg_match('/SetTitle\s*\(\s*[\'"](.+?)[\'"]\s*\)/u', $content, $m))
            $title = $m[1];

        if (preg_match('/SetPageProperty\s*\(\s*[\'"]description[\'"]\s*,\s*[\'"](.+?)[\'"]\s*\)/u', $content, $m))
            $description = $m[1];

        if ($title || $description)
            return ['title' => $title, 'description' => $description];
    }
    return ['title' => '', 'description' => ''];
}

function getMetaFromHttp($url)
{
    $host = $_SERVER['HTTP_HOST'];
    $cleanHost = preg_replace('/:\d+$/', '', $host); // отрезаем порт, если есть

    if (strpos($url, '/') !== 0) {
        $url = '/' . $url;
    }

    $reqId = substr(md5($url . microtime()), 0, 6);
    if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
        error_log("[http $reqId] START url=" . $url . " host=" . $cleanHost);
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $fullUrl = $scheme . '://' . $host . $url;
    $html = '';
    $error = '';

    // Метод 1: cURL (наиболее надежный способ)
    if (function_exists('curl_init')) {
        if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
            error_log("[http $reqId] Trying cURL with URL: " . $fullUrl);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; SEO-helpfile-checker/1.0)');

        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode < 400) {
                $html = $response;
                if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
                    error_log("[http $reqId] cURL success, HTTP " . $statusCode);
                }
            } else {
                $error = 'HTTP ' . $statusCode;
            }
        } else {
            $error = 'cURL error: ' . curl_error($ch);
        }
        curl_close($ch);
    }

    // Метод 2: file_get_contents (если cURL отключен)
    if ($html === '' && ini_get('allow_url_fopen')) {
        if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
            error_log("[http $reqId] Trying file_get_contents with URL: " . $fullUrl);
        }
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 4,
                'header' => "User-Agent: Mozilla/5.0 (compatible; SEO-helpfile-checker/1.0)\r\n",
                'follow_location' => 1,
                'max_redirects' => 3,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        $old_err = error_reporting(0);
        $response = @file_get_contents($fullUrl, false, $ctx);
        error_reporting($old_err);

        if ($response !== false) {
            $statusCode = 200;
            if (isset($http_response_header) && is_array($http_response_header)) {
                if (preg_match('#HTTP/\S+\s+(\d+)#', $http_response_header[0], $m)) {
                    $statusCode = (int)$m[1];
                }
            }
            if ($statusCode < 400) {
                $html = $response;
                $error = '';
                if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
                    error_log("[http $reqId] file_get_contents success, HTTP " . $statusCode);
                }
            } else {
                $error = 'HTTP ' . $statusCode;
            }
        } else {
            $error = 'file_get_contents failed';
        }
    }

    // Метод 3: Ручные сокеты (оригинальный метод с исправленным IP)
    if ($html === '') {
        if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
            error_log("[http $reqId] Trying manual sockets");
        }

        // Сначала пробуем подключиться к внешнему IP домена, а затем к локальному
        $targets = [$cleanHost, '127.0.0.1'];

        foreach ($targets as $targetIp) {
            $schemeVal = $scheme;
            $pathVal   = $url;
            $maxHops   = 3;
            $connected = false;

            for ($hop = 0; $hop < $maxHops; $hop++) {
                $transport = ($schemeVal === 'https') ? 'tls' : 'tcp';
                $port      = ($schemeVal === 'https') ? 443 : 80;

                $ctx = stream_context_create([
                    'ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                        'peer_name'        => $cleanHost,
                        'SNI_enabled'      => true,
                    ],
                ]);

                $fp = @stream_socket_client(
                    $transport . '://' . $targetIp . ':' . $port,
                    $errno,
                    $errstr,
                    3,
                    STREAM_CLIENT_CONNECT,
                    $ctx
                );

                if (!$fp) {
                    $error = 'Сокет (' . $targetIp . '): ' . $errstr . ' (' . $errno . ')';
                    break;
                }

                $connected = true;
                stream_set_timeout($fp, 3);

                $req  = "GET " . $pathVal . " HTTP/1.0\r\n";
                $req .= "Host: " . $cleanHost . "\r\n";
                $req .= "User-Agent: Mozilla/5.0 (compatible; SEO-helpfile-checker/1.0)\r\n";
                $req .= "Connection: close\r\n\r\n";
                fwrite($fp, $req);

                $response = stream_get_contents($fp);
                $metaData = stream_get_meta_data($fp);
                fclose($fp);

                if (!empty($metaData['timed_out'])) {
                    $error = 'Таймаут сокета на ' . $pathVal;
                    break;
                }
                if ($response === '' || $response === false) {
                    $error = 'Пустой ответ сокета от ' . $pathVal;
                    break;
                }

                $parts   = explode("\r\n\r\n", $response, 2);
                $headers = $parts[0];
                $body    = isset($parts[1]) ? $parts[1] : '';

                $statusCode = 0;
                if (preg_match('#^HTTP/\S+\s+(\d+)#', $headers, $m)) {
                    $statusCode = (int)$m[1];
                }

                if (in_array($statusCode, [301, 302, 303, 307, 308], true)) {
                    if (preg_match('/^Location:\s*(.+)$/im', $headers, $lm)) {
                        $location = trim($lm[1]);
                        $lp = parse_url($location);

                        if (!empty($lp['scheme'])) {
                            $schemeVal = strtolower($lp['scheme']);
                        }
                        $newPath = isset($lp['path']) ? $lp['path'] : '/';
                        if (!empty($lp['query'])) {
                            $newPath .= '?' . $lp['query'];
                        }
                        $pathVal = $newPath !== '' ? $newPath : '/';
                        continue;
                    }
                    $error = 'Редирект без Location (HTTP ' . $statusCode . ')';
                    break;
                }

                if ($statusCode >= 400) {
                    $error = 'HTTP ' . $statusCode;
                    break;
                }

                $html  = $body;
                $error = '';
                break;
            }

            if ($html !== '') {
                break;
            }
        }
    }

    if ($html === '' && $error === '') {
        $error = 'Не удалось получить страницу';
    }

    if ($html !== '') {
        $title = $description = '';
        if (preg_match('/<title[^>]*>(.*?)<\/title>/isu', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"]([^\'"]*)[\'"][^>]*>/isu', $html, $m)) {
            $description = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        } elseif (preg_match('/<meta[^>]+content=[\'"]([^\'"]*)[\'"][^>]+name=[\'"]description[\'"][^>]*>/isu', $html, $m)) {
            $description = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
            $snippet = substr($html, 0, 600);
            $snippet = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $snippet);
            $snippet = preg_replace('/\s+/', ' ', $snippet);
            error_log("[http $reqId] PARSED title=" . var_export($title, true));
            error_log("[http $reqId] PARSED desc=" . var_export($description, true));
        }

        return ['title' => $title, 'description' => $description, 'error' => ''];
    }

    if (defined('HELPFILE_DEBUG') && HELPFILE_DEBUG) {
        error_log("[http $reqId] FAIL error=" . $error);
    }

    return ['title' => '', 'description' => '', 'error' => $error];
}
function findPhysicalFilePath($url)
{
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $trimmed = rtrim($url, '/');
    $paths = [
        $docRoot . $trimmed . '/index.php',
        $docRoot . $url . 'index.php',
        $docRoot . $trimmed . '.php',
    ];
    foreach ($paths as $p)
        if (file_exists($p)) return $p;
    return null;
}

function checkPageType($url, $iblocks = [])
{
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $result  = [
        'url' => $url,
        'type' => 'unknown',
        'id' => null,
        'name' => null,
        'iblock_id' => null,
        'iblock' => null,
        'title' => '',
        'description' => ''
    ];
    if (defined('HELPFILE_DEBUG')) {
        error_log(
            '[checkPageType] ENTER url=' . var_export($url, true)
                . ' | trimmed=' . var_export($trimmed, true)
        );
    }
    $trimmed   = rtrim($url, '/');

    // Определение главной страницы без ложных совпадений инфоблоков
    if ($trimmed === '' || $url === '/' || $url === '') {
        $meta = getMetaFromFile('/');
        return array_merge($result, [
            'type' => 'physical_file',
            'name' => '/index.php',
            'title' => $meta['title'],
            'description' => $meta['description']
        ]);
    }

    $physicalFile = null;
    foreach ([$docRoot . $trimmed . '.php', $docRoot . $trimmed . '/index.php', $docRoot . $url . 'index.php'] as $path) {
        if (file_exists($path)) {
            $physicalFile = str_replace($docRoot, '', $path);
            break;
        }
    }

    if (CMS_TYPE === 'BITRIX') {
        $segments = array_values(array_filter(explode('/', trim($url, '/'))));
        $code = !empty($segments) ? end($segments) : false;
        if (defined('HELPFILE_DEBUG')) {
            error_log(
                '[checkPageType] url=' . var_export($url, true)
                    . ' | trimmed=' . var_export($trimmed, true)
                    . ' | segments=' . json_encode($segments)
                    . ' | code=' . var_export($code, true)
            );
        }

        if ($code !== false && $code !== '') {
            $isNumeric = ctype_digit((string)$code);
            $filterSection = $isNumeric ? ['ID' => $code] : ['CODE' => $code];
            $filterElement = $isNumeric ? ['ID' => $code] : ['CODE' => $code];
            foreach ($iblocks as $ib) {
                $rs = CIBlockSection::GetList([], array_merge(['IBLOCK_ID' => $ib['ID'], 'ACTIVE' => 'Y'], $filterSection), false, ['ID', 'NAME', 'CODE']);
                if ($section = $rs->Fetch()) {
                    $meta = getSectionIpropMeta($section['ID'], $ib['ID']);
                    return array_merge($result, [
                        'type' => 'iblock_section',
                        'id' => $section['ID'],
                        'name' => $section['NAME'],
                        'iblock_id' => $ib['ID'],
                        'iblock' => '[' . $ib['ID'] . '] ' . $ib['NAME'],
                        'title' => $meta['title'],
                        'description' => $meta['description']
                    ]);
                }
                $rsEl = CIBlockElement::GetList(
                    [],
                    array_merge(['IBLOCK_ID' => $ib['ID'], 'ACTIVE' => 'Y'], $filterElement),
                    false,
                    ['nTopCount' => 1],
                    ['ID', 'NAME', 'CODE', 'IBLOCK_ID']
                );
                if ($el = $rsEl->Fetch()) {
                    $meta = getElementIpropMeta($el['ID'], $ib['ID']);
                    return array_merge($result, [
                        'type' => 'iblock_element',
                        'id' => $el['ID'],
                        'name' => $el['NAME'],
                        'iblock_id' => $ib['ID'],
                        'iblock' => '[' . $ib['ID'] . '] ' . $ib['NAME'],
                        'title' => $meta['title'],
                        'description' => $meta['description']
                    ]);
                }
            }
        }
    } elseif (CMS_TYPE === 'WORDPRESS') {
        $wpMatch = wp_find_by_url($url);
        if ($wpMatch) {
            $subType = $wpMatch['post_type'] ?? $wpMatch['taxonomy'] ?? '';
            $meta = getWPMeta($wpMatch['id'], $wpMatch['type'], $subType);
            return array_merge($result, [
                'type' => $wpMatch['type'],
                'id' => $wpMatch['id'],
                'name' => $wpMatch['name'],
                'iblock_id' => $subType,
                'iblock' => strtoupper($subType),
                'title' => $meta['title'],
                'description' => $meta['description']
            ]);
        }
    }

    if ($physicalFile) {
        $meta = getMetaFromFile($url);
        return array_merge($result, [
            'type' => 'physical_file',
            'name' => $physicalFile,
            'title' => $meta['title'],
            'description' => $meta['description']
        ]);
    }
    return $result;
}

// Загрузка структуры Bitrix инфоблоков, если активен Битрикс
$iblocks = [];
if (CMS_TYPE === 'BITRIX') {
    $rsIblocks = CIBlock::GetList([], ['ACTIVE' => 'Y']);
    while ($ib = $rsIblocks->Fetch()) $iblocks[] = $ib;
}