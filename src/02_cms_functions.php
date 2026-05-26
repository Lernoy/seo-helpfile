<?php
// ===================== ИНИЦИАЛИЗАЦИЯ CMS =====================
if (CMS_TYPE === 'BITRIX') {
    require_once($docRoot . '/bitrix/modules/main/include/prolog_before.php');
    CModule::IncludeModule('iblock');
    if (file_exists($docRoot . "/bitrix/modules/main/admin_tools.php")) {
        require_once($docRoot . "/bitrix/modules/main/admin_tools.php");
    }
} elseif (CMS_TYPE === 'WORDPRESS') {
    require_once($docRoot . '/wp-load.php');
}

// ===================== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ БИТРИКС =====================

function getElementIpropMeta($elementId, $iblockId)
{
    $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iblockId, $elementId);
    $values = $ipropValues->getValues();
    return [
        'title'       => $values['ELEMENT_META_TITLE'] ?? '',
        'description' => $values['ELEMENT_META_DESCRIPTION'] ?? '',
    ];
}

function getSectionIpropMeta($sectionId, $iblockId)
{
    $ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($iblockId, $sectionId);
    $values = $ipropValues->getValues();
    return [
        'title'       => $values['SECTION_META_TITLE'] ?? '',
        'description' => $values['SECTION_META_DESCRIPTION'] ?? '',
    ];
}

// ===================== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ WORDPRESS =====================

function getAIOSEOMeta($id, $type)
{
    global $wpdb;
    $title = '';
    $desc = '';

    if ($type === 'wp_post') {
        $table = $wpdb->prefix . 'aioseo_posts';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $row = $wpdb->get_row($wpdb->prepare("SELECT title, description FROM $table WHERE post_id = %d", $id));
            if ($row) {
                $title = $row->title;
                $desc = $row->description;
            }
        }
    } elseif ($type === 'wp_term') {
        $table = $wpdb->prefix . 'aioseo_terms';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $row = $wpdb->get_row($wpdb->prepare("SELECT title, description FROM $table WHERE term_id = %d", $id));
            if ($row) {
                $title = $row->title;
                $desc = $row->description;
            }
        }
    }
    return ['title' => $title, 'description' => $desc];
}

function updateAIOSEOMeta($id, $type, $title, $description)
{
    global $wpdb;
    if ($type === 'wp_post') {
        $table = $wpdb->prefix . 'aioseo_posts';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE post_id = %d", $id));
            if ($exists) {
                $wpdb->update($table, ['title' => $title, 'description' => $description], ['post_id' => $id]);
            } else {
                $wpdb->insert($table, ['post_id' => $id, 'title' => $title, 'description' => $description]);
            }
            return true;
        }
    } elseif ($type === 'wp_term') {
        $table = $wpdb->prefix . 'aioseo_terms';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE term_id = %d", $id));
            if ($exists) {
                $wpdb->update($table, ['title' => $title, 'description' => $description], ['term_id' => $id]);
            } else {
                $wpdb->insert($table, ['term_id' => $id, 'title' => $title, 'description' => $description]);
            }
            return true;
        }
    }
    return false;
}

function wp_find_by_url($url)
{
    $parsed = parse_url($url);
    $path = trim($parsed['path'] ?? '/', '/');

    if ($path === '') {
        $front_id = get_option('page_on_front');
        if ($front_id) {
            return [
                'type' => 'wp_post',
                'id' => $front_id,
                'name' => 'Главная страница (Статическая)',
                'post_type' => 'page'
            ];
        }
        return [
            'type' => 'wp_frontpage',
            'id' => 0,
            'name' => 'Главная страница (Блог)',
            'post_type' => 'frontpage'
        ];
    }

    $segments = array_filter(explode('/', $path));
    if (empty($segments)) return null;

    $slug = end($segments);

    // Поиск записей/страниц/CPT по ярлыку (slug)
    $posts = get_posts([
        'name'        => $slug,
        'post_type'   => 'any',
        'post_status' => 'any',
        'posts_per_page' => 1
    ]);
    if (!empty($posts)) {
        $post = $posts[0];
        return [
            'type' => 'wp_post',
            'id' => $post->ID,
            'name' => $post->post_title,
            'post_type' => $post->post_type
        ];
    }

    // Поиск категорий, меток или пользовательских таксономий
    $taxonomies = get_taxonomies();
    foreach ($taxonomies as $tax) {
        $term = get_term_by('slug', $slug, $tax);
        if ($term && !is_wp_error($term)) {
            return [
                'type' => 'wp_term',
                'id' => $term->term_id,
                'name' => $term->name,
                'taxonomy' => $tax
            ];
        }
    }

    // Попытка найти по числовому ID, если последний сегмент — число
    if (ctype_digit($slug)) {
        $post = get_post((int)$slug);
        if ($post) {
            return [
                'type' => 'wp_post',
                'id' => $post->ID,
                'name' => $post->post_title,
                'post_type' => $post->post_type
            ];
        }
    }

    return null;
}

function getWPMeta($id, $type, $subType = '')
{
    $title = '';
    $desc = '';

    if ($type === 'wp_post') {
        // 1. All in One SEO (AIOSEO)
        $aioseo = getAIOSEOMeta($id, 'wp_post');
        if (!empty($aioseo['title'])) $title = $aioseo['title'];
        if (!empty($aioseo['description'])) $desc = $aioseo['description'];

        // 2. RankMath
        if (!$title) $title = get_post_meta($id, 'rank_math_title', true);
        if (!$desc) $desc = get_post_meta($id, 'rank_math_description', true);

        // 3. Yoast SEO
        if (!$title) $title = get_post_meta($id, '_yoast_wpseo_title', true);
        if (!$desc) $desc = get_post_meta($id, '_yoast_wpseo_metadesc', true);

        // Базовый заголовок, если метатеги отсутствуют
        if (!$title) {
            $post = get_post($id);
            if ($post) $title = $post->post_title;
        }
    } elseif ($type === 'wp_term') {
        // 1. All in One SEO (AIOSEO)
        $aioseo = getAIOSEOMeta($id, 'wp_term');
        if (!empty($aioseo['title'])) $title = $aioseo['title'];
        if (!empty($aioseo['description'])) $desc = $aioseo['description'];

        // 2. RankMath
        if (!$title) $title = get_term_meta($id, 'rank_math_title', true);
        if (!$desc) $desc = get_term_meta($id, 'rank_math_description', true);

        // 3. Yoast SEO
        if (!$title) $title = get_term_meta($id, '_yoast_wpseo_title', true);
        if (!$desc) $desc = get_term_meta($id, '_yoast_wpseo_metadesc', true);

        // Старый формат хранения таксономий в Yoast (в опциях)
        if (!$title || !$desc) {
            $tax_meta = get_option('wpseo_taxonomy_meta');
            if (is_array($tax_meta) && isset($tax_meta[$subType][$id])) {
                if (!$title) $title = $tax_meta[$subType][$id]['wpseo_title'] ?? '';
                if (!$desc) $desc = $tax_meta[$subType][$id]['wpseo_desc'] ?? '';
            }
        }

        if (!$title) {
            $term = get_term($id, $subType);
            if ($term && !is_wp_error($term)) $title = $term->name;
        }
    } elseif ($type === 'wp_frontpage') {
        $title = get_option('rank_math_frontpage_title');
        if (!$title) $title = get_option('blogname');
        $desc = get_option('rank_math_frontpage_description');
        if (!$desc) $desc = get_option('blogdescription');
    }

    return ['title' => $title, 'description' => $desc];
}

function updateWPMeta($id, $type, $subType, $title, $description)
{
    if ($type === 'wp_post') {
        updateAIOSEOMeta($id, 'wp_post', $title, $description);

        update_post_meta($id, 'rank_math_title', $title);
        update_post_meta($id, 'rank_math_description', $description);

        update_post_meta($id, '_yoast_wpseo_title', $title);
        update_post_meta($id, '_yoast_wpseo_metadesc', $description);

        update_post_meta($id, '_aioseo_title', $title);
        update_post_meta($id, '_aioseo_description', $description);
        return true;
    } elseif ($type === 'wp_term') {
        updateAIOSEOMeta($id, 'wp_term', $title, $description);

        update_term_meta($id, 'rank_math_title', $title);
        update_term_meta($id, 'rank_math_description', $description);

        update_term_meta($id, '_yoast_wpseo_title', $title);
        update_term_meta($id, '_yoast_wpseo_metadesc', $description);

        update_term_meta($id, '_aioseo_title', $title);
        update_term_meta($id, '_aioseo_description', $description);

        // Запись в опцию Yoast (для обратной совместимости)
        $tax_meta = get_option('wpseo_taxonomy_meta');
        if (!is_array($tax_meta)) $tax_meta = [];
        if (!isset($tax_meta[$subType])) $tax_meta[$subType] = [];
        if (!isset($tax_meta[$subType][$id])) $tax_meta[$subType][$id] = [];
        $tax_meta[$subType][$id]['wpseo_title'] = $title;
        $tax_meta[$subType][$id]['wpseo_desc'] = $description;
        update_option('wpseo_taxonomy_meta', $tax_meta);
        return true;
    } elseif ($type === 'wp_frontpage') {
        update_option('blogname', $title);
        update_option('blogdescription', $description);
        update_option('rank_math_frontpage_title', $title);
        update_option('rank_math_frontpage_description', $description);
        return true;
    }
    return false;
}