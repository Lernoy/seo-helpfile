<?php
// ===================== AJAX: ЗАГРУЗКА СТРОК (SEO META) =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'load_urls') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    session_write_close();
    $raw  = trim($_POST['urls'] ?? '');
    $checkHttp = isset($_POST['check_http']) && $_POST['check_http'] === 'Y';
    $urls = array_values(array_unique(array_filter(array_map('trim', explode("\n", $raw)))));
    $rows = [];
    foreach ($urls as $rawUrl) {
        $path = $rawUrl;
        if (preg_match('#^https?://#i', $rawUrl)) {
            $parsed = parse_url($rawUrl);
            $path   = ($parsed['path'] ?? '/');
            if (!empty($parsed['query'])) $path .= '?' . $parsed['query'];
        }
        $r = checkPageType($path, $iblocks);

        if ($checkHttp) {
            $httpMeta = getMetaFromHttp($path);
            $r['http_title'] = $httpMeta['title'] ?? '';
            $r['http_description'] = $httpMeta['description'] ?? '';
            $r['http_error'] = $httpMeta['error'] ?? '';
        } else {
            $r['http_title'] = '';
            $r['http_description'] = '';
            $r['http_error'] = '';
        }

        $rows[] = $r;
    }
    echo json_encode(['success' => true, 'rows' => $rows]);
    exit;
}

// ===================== AJAX: СОХРАНЕНИЕ (SEO META) =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_meta') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    session_write_close();
    $id          = $_POST['id'] ?? '';
    $iblockId    = $_POST['iblock_id'] ?? '';
    $type        = $_POST['type'] ?? '';
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url         = $_POST['url'] ?? '';
    $checkHttp   = isset($_POST['check_http']) && $_POST['check_http'] === 'Y';

    $realMeta = ['title' => '', 'description' => ''];

    if ($type === 'iblock_element') {
        $id = intval($id);
        $iblockId = intval($iblockId);
        if (empty($id) || empty($iblockId)) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID']);
            exit;
        }
        $ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($iblockId, $id);
        $ipropTemplates->set(['ELEMENT_META_TITLE' => $title, 'ELEMENT_META_DESCRIPTION' => $description]);
        $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iblockId, $id);
        $ipropValues->clearValues();
        $realMeta = getElementIpropMeta($id, $iblockId);
    } elseif ($type === 'iblock_section') {
        $id = intval($id);
        $iblockId = intval($iblockId);
        if (empty($id) || empty($iblockId)) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID']);
            exit;
        }
        $ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($iblockId, $id);
        $ipropTemplates->set(['SECTION_META_TITLE' => $title, 'SECTION_META_DESCRIPTION' => $description]);
        $ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($iblockId, $id);
        $ipropValues->clearValues();
        $realMeta = getSectionIpropMeta($id, $iblockId);
    } elseif ($type === 'wp_post' || $type === 'wp_term' || $type === 'wp_frontpage') {
        $id = intval($id);
        $success = updateWPMeta($id, $type, $iblockId, $title, $description);
        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Не удалось сохранить изменения в WP']);
            exit;
        }
        $realMeta = getWPMeta($id, $type, $iblockId);
    } elseif ($type === 'physical_file') {
        $filePath = findPhysicalFilePath($url);
        if (!$filePath) {
            echo json_encode(['success' => false, 'message' => 'Файл не найден: ' . $url]);
            exit;
        }
        if (!is_writable($filePath)) {
            echo json_encode(['success' => false, 'message' => 'Нет прав на запись']);
            exit;
        }

        if (CMS_TYPE === 'BITRIX') {
            global $APPLICATION;
            $fileContent = file_get_contents($filePath);

            $titleCode = 'title';
            if (class_exists('COption')) {
                $titleCode = COption::GetOptionString('seo', 'property_window_title', 'title');
            }

            if (function_exists('SetPrologProperty')) {
                // Записываем заголовок вкладки браузера <title> в свойство "title"
                $fileContent = SetPrologProperty($fileContent, $titleCode, $title);
                // Записываем description
                $fileContent = SetPrologProperty($fileContent, 'description', $description);
            }

            $success = false;
            if (is_object($APPLICATION) && method_exists($APPLICATION, 'SaveFileContent')) {
                $success = $APPLICATION->SaveFileContent($filePath, $fileContent);
            } else {
                $success = (file_put_contents($filePath, $fileContent) !== false);
            }

            if ($success === false) {
                echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла через API Битрикс']);
                exit;
            }
            $realMeta = getMetaFromFile($url);
        } else {
            // WordPress или внешние физические файлы
            $content   = file_get_contents($filePath);
            $safeTitle = str_replace(["\\", "'"], ["\\\\", "\\'"], $title);
            $safeDesc  = str_replace(["\\", "'"], ["\\\\", "\\'"], $description);
            if (preg_match('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]title[\'"]/u', $content))
                $content = preg_replace('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]title[\'"]\s*,\s*[\'"].*?[\'"]\s*\)\s*;/u', "\$APPLICATION->SetPageProperty('title', '" . $safeTitle . "');", $content);
            if (preg_match('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]description[\'"]/u', $content))
                $content = preg_replace('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]description[\'"]\s*,\s*[\'"].*?[\'"]\s*\)\s*;/u', "\$APPLICATION->SetPageProperty('description', '" . $safeDesc . "');", $content);

            $hasPropTitle = (bool) preg_match('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]title[\'"]/u', $content);
            $hasPropDesc  = (bool) preg_match('/\$APPLICATION\s*->\s*SetPageProperty\s*\(\s*[\'"]description[\'"]/u', $content);

            if (!$hasPropTitle || !$hasPropDesc) {
                $ins = '';
                if (!$hasPropTitle) $ins .= "\n\$APPLICATION->SetPageProperty('title', '" . $safeTitle . "');";
                if (!$hasPropDesc)  $ins .= "\n\$APPLICATION->SetPageProperty('description', '" . $safeDesc . "');";
                $hp = '/(require(?:_once)?\s*\(?\s*[\'"][^\'"]*header\.php[\'"]\s*\)?\s*;)/u';
                if (preg_match($hp, $content))
                    $content = preg_replace($hp, '$1' . $ins, $content, 1);
                else
                    $content = preg_replace('/^(<\?(?:php)?)\s*/u', '$1' . "\n" . $ins . "\n", $content, 1);
            }
            if (file_put_contents($filePath, $content) === false) {
                echo json_encode(['success' => false, 'message' => 'Ошибка записи']);
                exit;
            }
            $realMeta = getMetaFromFile($url);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Тип не поддерживается']);
        exit;
    }

    $httpMeta = ['title' => '', 'description' => '', 'error' => ''];
    if ($checkHttp) {
        $httpMeta = getMetaFromHttp($url);
    }

    echo json_encode([
        'success' => true,
        'http_title' => $httpMeta['title'] ?? '',
        'http_description' => $httpMeta['description'] ?? '',
        'http_error' => $httpMeta['error'] ?? ''
    ]);
    exit;
}