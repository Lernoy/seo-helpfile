
<body>

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#4a90d9" />
                <path d="M6 17V10l6-4 6 4v7h-4v-4H10v4H6z" fill="white" />
            </svg>
            SEO Meta Editor
        </div>
        <div style="margin-left: 10px;">
            <span class="badge badge-file" style="text-transform: uppercase;">
                CMS: <?= CMS_TYPE ?>
            </span>
        </div>

        <div class="topbar-nav">
            <button class="nav-tab" data-target="meta-editor">Редактор метатегов</button>
            <button class="nav-tab" data-target="docx-converter">Конвертер DOCX → HTML</button>
            <button class="nav-tab" data-target="server-info">Сервер</button>
        </div>

        <div class="topbar-sep"></div>
        <span class="topbar-meta" id="topbar-stats"></span>
        <a id="update-badge" class="update-badge" href="/deploy.php" target="_blank" style="display:none">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
            </svg>
            Доступно обновление
        </a>
        <button class="btn-theme" id="btn-theme" title="Тёмная / светлая тема">
            <svg id="theme-ico-moon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg id="theme-ico-sun" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1"  x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22"   x2="5.64"  y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1"  y1="12" x2="3"  y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
                <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
            </svg>
        </button>
        <form method="POST" style="margin:0">
            <input type="hidden" name="helpfile_logout" value="1">
            <button type="submit" class="btn-logout">Выйти</button>
        </form>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="main">

        <!-- СЕКЦИЯ 1: SEO META EDITOR -->
        <div id="panel-meta-editor" class="service-panel">

            <!-- URL INPUT CARD -->
            <div class="card">
                <div class="card-head">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4a90d9" stroke-width="2" stroke-linecap="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                    <h2>URL для анализа</h2>
                </div>
                <div class="card-body">
                    <div class="url-panel">
                        <div style="flex:1">
                            <textarea class="url-textarea" id="url-input" placeholder="Вставьте URL — по одному на строку. Поддерживаются полные URL (https://site.ru/path/) и пути (/path/).&#10;&#10;https://market.filikrovlya.ru/&#10;/services/"></textarea>
                            <div class="url-hint">По одному URL на строку. Полные адреса (https://...) и относительные пути (/path/) — оба формата поддерживаются.</div>

                            <!-- Чекбокс HTTP-проверки -->
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; color: #555; margin-top: 10px; cursor: pointer; user-select: none;">
                                <input type="checkbox" id="chk-http" style="cursor: pointer;" checked>
                                Сверять с живым выводом страницы по HTTP (может замедлить загрузку или блокироваться сервером)
                            </label>
                        </div>
                        <div class="url-actions">
                            <button class="btn btn-primary" id="btn-load">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="23 4 23 10 17 10" />
                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                                </svg>
                                Загрузить
                            </button>
                            <button class="btn btn-default" id="btn-clear-input">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Очистить
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-head">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4a90d9" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                        <line x1="3" y1="15" x2="21" y2="15" />
                        <line x1="9" y1="3" x2="9" y2="21" />
                    </svg>
                    <h2>Метатеги</h2>
                    <span id="rows-count" style="display:none"></span>
                    <div class="toolbar-right">
                        <div class="legend">
                            <span class="legend-item"><span class="legend-dot dot-file"></span>Файл / WP Главная</span>
                            <span class="legend-item"><span class="legend-dot dot-section"></span>Раздел / WP Запись</span>
                            <span class="legend-item"><span class="legend-dot dot-element"></span>Элемент / WP Рубрика</span>
                            <span class="legend-item"><span class="legend-dot dot-unknown"></span>Неизвестно</span>
                        </div>
                        <button class="btn btn-success" id="btn-save-all" disabled>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            <span id="save-all-label">Сохранить изменённые</span>
                        </button>
                    </div>
                </div>

                <!-- Progress -->
                <div class="progress-wrap" id="progress-wrap">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>

                <!-- Log -->
                <div class="log-bar" id="log-bar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span id="log-text"></span>
                </div>

                <!-- Table -->
                <div class="table-wrap" id="table-wrap">
                    <div class="empty-state" id="empty-state">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#9aa0ab" stroke-width="1.5" stroke-linecap="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <line x1="3" y1="9" x2="21" y2="9" />
                            <line x1="9" y1="3" x2="9" y2="21" />
                        </svg>
                        <p>Таблица пуста</p>
                        <small>Вставьте URL выше и нажмите «Загрузить»</small>
                    </div>
                    <table id="meta-table" style="display:none">
                        <colgroup>
                            <col class="c-num">
                            <col class="c-url">
                            <col class="c-type">
                            <col class="c-name">
                            <col class="c-ib">
                            <col class="c-title">
                            <col class="c-desc">
                            <col class="c-act">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>URL</th>
                                <th>Тип страницы</th>
                                <th>Название сущности</th>
                                <th>Тип / Инфоблок</th>
                                <th>Title <span style="font-weight:400;text-transform:none;letter-spacing:0">(≤60 симв.)</span></th>
                                <th>Description <span style="font-weight:400;text-transform:none;letter-spacing:0">(≤160 симв.)</span></th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody id="meta-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- СЕКЦИЯ 2: DOCX CONVERTER -->
        <div id="panel-docx-converter" class="service-panel">
            <div class="card">
                <div class="card-head">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4a90d9" stroke-width="2" stroke-linecap="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <h2>Конвертер документов DOCX в HTML</h2>
                </div>
                <div class="card-body">
                    <?php if ($docx_error): ?>
                        <div class="error" style="margin-bottom: 1.5rem; padding: 10px 14px; background: #fff0f0; border: 1px solid #fca5a5; border-radius: 4px; color: #c0392b; font-size: 11px;">
                            <?= htmlspecialchars($docx_error) ?>
                        </div>
                    <?php endif; ?>

                    <form class="upload-form" method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="file" name="docx" id="fileInput" accept=".docx">
                        <div class="upload-icon">📄</div>
                        <div class="upload-label"><strong>Выбрать файл .docx</strong> или перетащить его сюда</div>
                        <div class="upload-hint">Картинки будут извлечены и сохранены в директорию скрипта автоматически</div>
                        <div class="selected-name" id="selectedName"></div>
                    </form>
                    <button class="btn-submit" id="submitBtn" type="submit" form="uploadForm">Конвертировать →</button>

                    <?php if ($result_html): ?>
                        <?php
                        $lines_count = substr_count($result_html, "\n") + 1;
                        $img_count   = substr_count($result_html, '<img ');
                        ?>

                        <div style="margin-top: 1.5rem">
                            <div class="result-meta" style="font-size: 11px; color: #6b7280; margin-bottom: 12px;">
                                Найдено тегов: <span style="color: #4a90d9; font-weight: 600;"><?= $lines_count ?></span> ·
                                Изображений: <span style="color: #4a90d9; font-weight: 600;"><?= $img_count ?></span>
                                <?php if ($image_dir): ?> · Папка картинок: <span style="color: #4a90d9; font-weight: 600;"><?= htmlspecialchars($image_dir) ?></span><?php endif; ?>
                            </div>

                            <?php if ($h1_text): ?>
                                <div class="h1-field-wrap">
                                    <div class="h1-label">Обнаруженный заголовок H1</div>
                                    <textarea class="h1-field" id="h1Field" rows="1" readonly onclick="this.select()"><?= htmlspecialchars($h1_text) ?></textarea>
                                </div>
                            <?php endif; ?>

                            <div class="docx-tabs">
                                <div class="docx-tab active" data-subtab="html">HTML-код</div>
                                <?php if ($img_count > 0): ?>
                                    <div class="docx-tab" data-subtab="images">Изображения (<?= $img_count ?>)</div>
                                <?php endif; ?>
                            </div>

                            <div class="docx-pane active" id="pane-html">
                                <div class="code-wrap">
                                    <button class="copy-btn" id="copyBtn">Скопировать</button>
                                    <textarea class="code-block" id="codeBlock" readonly><?= htmlspecialchars($result_html) ?></textarea>
                                </div>
                                <?php if ($image_dir): ?>
                                    <div class="img-dir">Изображения сохранены в <span><?= htmlspecialchars($image_dir) ?></span></div>
                                <?php endif; ?>
                            </div>

                            <?php if ($img_count > 0): ?>
                                <div class="docx-pane" id="pane-images">
                                    <?php
                                    preg_match_all('/src="([^"]+)"/', $result_html, $matches);
                                    $img_paths = $matches[1] ?? [];
                                    ?>
                                    <?php if ($img_paths): ?>
                                        <div class="imgs-grid">
                                            <?php foreach ($img_paths as $src): ?>
                                                <div class="img-card">
                                                    <img src="<?= htmlspecialchars($src) ?>" alt="">
                                                    <div class="img-card-meta">
                                                        <div class="img-card-name"><?= htmlspecialchars(basename($src)) ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-imgs">Картинок не найдено</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- СЕКЦИЯ 3: SERVER INFO -->
        <div id="panel-server-info" class="service-panel">
            <div class="card">
                <div class="card-head">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4a90d9" stroke-width="2" stroke-linecap="round">
                        <rect x="2" y="2" width="20" height="8" rx="2"/>
                        <rect x="2" y="14" width="20" height="8" rx="2"/>
                        <line x1="6" y1="6" x2="6.01" y2="6"/>
                        <line x1="6" y1="18" x2="6.01" y2="18"/>
                    </svg>
                    <h2>Информация о сервере</h2>
                </div>
                <?php
                function hf_fmt_bytes($bytes) {
                    if ($bytes === false || $bytes === null) return '—';
                    $units = ['Б','КБ','МБ','ГБ','ТБ'];
                    $i = 0;
                    while ($bytes >= 1024 && $i < 4) { $bytes /= 1024; $i++; }
                    return round($bytes, 1) . ' ' . $units[$i];
                }

                $diskRoot  = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
                $diskFree  = @disk_free_space($diskRoot);
                $diskTotal = @disk_total_space($diskRoot);
                $diskUsedPct = ($diskTotal && $diskFree !== false)
                    ? (int)round((1 - $diskFree / $diskTotal) * 100)
                    : null;
                $diskBarClass = ($diskUsedPct === null) ? 'ok'
                    : ($diskUsedPct >= 90 ? 'err' : ($diskUsedPct >= 75 ? 'warn' : 'ok'));

                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || ($_SERVER['SERVER_PORT'] ?? '') == '443';

                $exts = [
                    'curl'     => 'cURL',
                    'zip'      => 'ZipArchive',
                    'mbstring' => 'mbstring',
                    'json'     => 'json',
                    'openssl'  => 'OpenSSL',
                    'pdo'      => 'PDO',
                    'gd'       => 'GD',
                    'imagick'  => 'Imagick',
                    'dom'      => 'DOM',
                    'xml'      => 'XML',
                ];

                $opcacheOn = extension_loaded('Zend OPcache') && ini_get('opcache.enable');
                $aufo      = (bool) ini_get('allow_url_fopen');
                $writable  = is_writable(__DIR__);
                $tz        = date_default_timezone_get();
                ?>
                <div class="srvinfo-grid">

                    <!-- Среда -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Среда
                        </div>
                        <table class="srvinfo-table">
                            <tr><td>PHP</td><td><?= PHP_VERSION ?></td></tr>
                            <tr><td>SAPI</td><td><?= PHP_SAPI ?></td></tr>
                            <tr><td>ОС</td><td><?= htmlspecialchars(PHP_OS_FAMILY . ' / ' . php_uname('r')) ?></td></tr>
                            <tr><td>Архитектура</td><td><?= PHP_INT_SIZE * 8 ?>-bit</td></tr>
                            <tr><td>Веб-сервер</td><td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '—') ?></td></tr>
                            <tr><td>CMS</td><td><?= CMS_TYPE ?></td></tr>
                        </table>
                    </div>

                    <!-- PHP конфигурация -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            PHP конфигурация
                        </div>
                        <table class="srvinfo-table">
                            <tr><td>memory_limit</td><td><?= ini_get('memory_limit') ?></td></tr>
                            <tr><td>upload_max_filesize</td><td><?= ini_get('upload_max_filesize') ?></td></tr>
                            <tr><td>post_max_size</td><td><?= ini_get('post_max_size') ?></td></tr>
                            <tr><td>max_execution_time</td><td><?= ini_get('max_execution_time') ?> с</td></tr>
                            <tr><td>max_file_uploads</td><td><?= ini_get('max_file_uploads') ?></td></tr>
                            <tr><td>display_errors</td><td><?= ini_get('display_errors') ? 'On' : 'Off' ?></td></tr>
                            <tr>
                                <td>OPcache</td>
                                <td>
                                    <span class="si-dot <?= $opcacheOn ? 'si-dot-ok' : 'si-dot-off' ?>"></span>
                                    <span class="<?= $opcacheOn ? 'si-ok' : '' ?>"><?= $opcacheOn ? 'включён' : 'выключен' ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Расширения -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            Расширения
                        </div>
                        <table class="srvinfo-table">
                            <?php foreach ($exts as $ext => $label):
                                $ok = extension_loaded($ext);
                            ?>
                            <tr>
                                <td><?= $label ?></td>
                                <td>
                                    <span class="si-dot <?= $ok ? 'si-dot-ok' : 'si-dot-off' ?>"></span>
                                    <span class="<?= $ok ? 'si-ok' : '' ?>"><?= $ok ? 'да' : 'нет' ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td>allow_url_fopen</td>
                                <td>
                                    <span class="si-dot <?= $aufo ? 'si-dot-ok' : 'si-dot-warn' ?>"></span>
                                    <span class="<?= $aufo ? 'si-ok' : 'si-warn' ?>"><?= $aufo ? 'включён' : 'выключен' ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Файловая система -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            Файловая система
                        </div>
                        <table class="srvinfo-table">
                            <tr><td>DOCUMENT_ROOT</td><td><?= htmlspecialchars($diskRoot) ?></td></tr>
                            <tr><td>Папка скрипта</td><td><?= htmlspecialchars(__DIR__) ?></td></tr>
                            <tr>
                                <td>Запись в папку</td>
                                <td>
                                    <span class="si-dot <?= $writable ? 'si-dot-ok' : 'si-dot-err' ?>"></span>
                                    <span class="<?= $writable ? 'si-ok' : 'si-err' ?>"><?= $writable ? 'разрешена' : 'запрещена' ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Свободно / Всего</td>
                                <td><?= hf_fmt_bytes($diskFree) ?> / <?= hf_fmt_bytes($diskTotal) ?></td>
                            </tr>
                            <?php if ($diskUsedPct !== null): ?>
                            <tr>
                                <td>Занято</td>
                                <td>
                                    <div class="srvinfo-bar-wrap">
                                        <span class="<?= $diskBarClass === 'err' ? 'si-err' : ($diskBarClass === 'warn' ? 'si-warn' : '') ?>"><?= $diskUsedPct ?>%</span>
                                        <div class="srvinfo-bar">
                                            <div class="srvinfo-bar-fill <?= $diskBarClass ?>" style="width:<?= $diskUsedPct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Сеть -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="5" width="22" height="14" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            Сеть
                        </div>
                        <table class="srvinfo-table">
                            <tr><td>Хост</td><td><?= htmlspecialchars($_SERVER['SERVER_NAME'] ?? '—') ?></td></tr>
                            <tr><td>Порт</td><td><?= htmlspecialchars($_SERVER['SERVER_PORT'] ?? '—') ?></td></tr>
                            <tr>
                                <td>HTTPS</td>
                                <td>
                                    <span class="si-dot <?= $isHttps ? 'si-dot-ok' : 'si-dot-warn' ?>"></span>
                                    <span class="<?= $isHttps ? 'si-ok' : 'si-warn' ?>"><?= $isHttps ? 'да' : 'нет' ?></span>
                                </td>
                            </tr>
                            <tr><td>IP клиента</td><td><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '—') ?></td></tr>
                            <tr><td>IP сервера</td><td><?= htmlspecialchars($_SERVER['SERVER_ADDR'] ?? '—') ?></td></tr>
                        </table>
                    </div>

                    <!-- Время -->
                    <div class="srvinfo-card">
                        <div class="srvinfo-card-head">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Время сервера
                        </div>
                        <table class="srvinfo-table">
                            <tr><td>Дата и время</td><td><?= date('d.m.Y H:i:s') ?></td></tr>
                            <tr><td>Часовой пояс</td><td><?= htmlspecialchars($tz) ?></td></tr>
                            <tr><td>UTC offset</td><td><?= date('P') ?></td></tr>
                            <?php
                            $uptime = '';
                            if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/uptime')) {
                                $up = (float)explode(' ', file_get_contents('/proc/uptime'))[0];
                                $d = (int)($up / 86400); $h = (int)(($up % 86400) / 3600); $m = (int)(($up % 3600) / 60);
                                $uptime = ($d ? "{$d}д " : '') . ($h ? "{$h}ч " : '') . "{$m}м";
                            }
                            ?>
                            <?php if ($uptime): ?><tr><td>Uptime</td><td><?= htmlspecialchars($uptime) ?></td></tr><?php endif; ?>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
