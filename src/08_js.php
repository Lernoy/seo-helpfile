    <!-- СКРИПТЫ СИСТЕМЫ -->
    <script>
        window.activeTabOnLoad = '<?= $initial_tab ?>';

        // ─── THEME TOGGLE ───
        (function () {
            function applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('hf-theme', theme);
                var moon = document.getElementById('theme-ico-moon');
                var sun  = document.getElementById('theme-ico-sun');
                if (moon && sun) {
                    moon.style.display = theme === 'dark' ? '' : 'none';
                    sun.style.display  = theme === 'dark' ? 'none' : '';
                }
            }
            // Синхронизируем иконку с текущей темой (атрибут уже мог быть установлен anti-flash скриптом)
            applyTheme(document.documentElement.getAttribute('data-theme') || 'light');

            var btn = document.getElementById('btn-theme');
            if (btn) {
                btn.addEventListener('click', function () {
                    var current = document.documentElement.getAttribute('data-theme') || 'light';
                    applyTheme(current === 'dark' ? 'light' : 'dark');
                });
            }
        })();

        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.target;

                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.service-panel').forEach(p => p.classList.remove('active'));

                tab.classList.add('active');
                const targetPanel = document.getElementById('panel-' + target);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });

        if (window.activeTabOnLoad) {
            const initialTabButton = document.querySelector(`.nav-tab[data-target="${window.activeTabOnLoad}"]`);
            if (initialTabButton) {
                initialTabButton.click();
            }
        }

        const fileInput = document.getElementById('fileInput');
        const selectedNameEl = document.getElementById('selectedName');
        const submitBtn = document.getElementById('submitBtn');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files[0]) {
                    selectedNameEl.textContent = this.files[0].name;
                    selectedNameEl.style.display = 'block';
                    submitBtn.style.display = 'block';
                }
            });
        }

        const copyBtn = document.getElementById('copyBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const ta = document.getElementById('codeBlock');
                navigator.clipboard.writeText(ta.value).then(() => {
                    this.textContent = 'Скопировано!';
                    this.classList.add('ok');
                    setTimeout(() => {
                        this.textContent = 'Скопировать';
                        this.classList.remove('ok');
                    }, 1500);
                });
            });
        }

        document.querySelectorAll('.docx-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.docx-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.docx-pane').forEach(p => p.classList.remove('active'));
                tab.classList.add('active');

                const pane = document.getElementById('pane-' + tab.dataset.subtab);
                if (pane) pane.classList.add('active');
            });
        });

        // ─── LOGIC SEO META EDITOR ───
        (function() {
            'use strict';

            function esc(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function setLog(msg, isError) {
                var bar = document.getElementById('log-bar');
                document.getElementById('log-text').textContent = msg;
                bar.className = 'log-bar visible' + (isError ? ' error' : '');
            }

            function clearLog() {
                var bar = document.getElementById('log-bar');
                if (bar) bar.className = 'log-bar';
            }

            function setProgress(pct) {
                var wrap = document.getElementById('progress-wrap');
                var bar = document.getElementById('progress-bar');
                if (!wrap || !bar) return;
                if (pct === null) {
                    wrap.className = 'progress-wrap';
                } else {
                    wrap.className = 'progress-wrap visible';
                    bar.style.width = pct + '%';
                }
            }

            function updateTopbarStats() {
                var rows = document.querySelectorAll('#meta-tbody tr[data-type]');
                var dirty = document.querySelectorAll('#meta-tbody tr[data-dirty="true"]').length;
                var el = document.getElementById('topbar-stats');
                if (!el) return;
                if (!rows.length) {
                    el.textContent = '';
                    return;
                }
                el.textContent = 'Строк: ' + rows.length + (dirty ? ' · Изменено: ' + dirty : '');
            }

            function updateSaveAllBtn() {
                var dirty = document.querySelectorAll('#meta-tbody tr[data-dirty="true"]').length;
                var btn = document.getElementById('btn-save-all');
                var label = document.getElementById('save-all-label');
                if (!btn || !label) return;
                btn.disabled = !dirty;
                label.textContent = dirty ? 'Сохранить изменённые (' + dirty + ')' : 'Сохранить изменённые';
                updateTopbarStats();
            }

            function typeBadge(type) {
                var map = {
                    physical_file: ['badge-file', 'Файл'],
                    iblock_section: ['badge-section', 'Раздел'],
                    iblock_element: ['badge-element', 'Элемент'],
                    wp_post: ['badge-section', 'WP Запись'],
                    wp_term: ['badge-element', 'WP Термин'],
                    wp_frontpage: ['badge-file', 'WP Главная'],
                    unknown: ['badge-unknown', '?'],
                };
                var d = map[type] || map.unknown;
                return '<span class="badge ' + d[0] + '">' + d[1] + '</span>';
            }

            function renderRow(r, idx) {
                var editable = (
                    r.type === 'iblock_section' ||
                    r.type === 'iblock_element' ||
                    r.type === 'physical_file' ||
                    r.type === 'wp_post' ||
                    r.type === 'wp_term' ||
                    r.type === 'wp_frontpage'
                );
                var ce = editable ? 'true' : 'false';
                var tr = document.createElement('tr');
                tr.className = 'row-' + r.type;
                tr.setAttribute('data-url', r.url);
                tr.setAttribute('data-type', r.type);
                tr.setAttribute('data-id', r.id || '');
                tr.setAttribute('data-iblock-id', r.iblock_id || '');
                tr.style.animationDelay = (idx * 0.03) + 's';
                tr.style.animation = 'fadeIn 0.3s ease both';

                var titleWarningHtml = '';
                if (r.http_error) {
                    titleWarningHtml = '<div class="live-meta-error">Ошибка HTTP проверки: ' + esc(r.http_error) + '</div>';
                } else if (r.http_title && r.title.trim() !== r.http_title.trim()) {
                    titleWarningHtml = '<div class="live-meta-warning" title="Значение отличается от тега <title> на странице.">На сайте: <span class="live-value">' + esc(r.http_title) + '</span></div>';
                }

                var descWarningHtml = '';
                if (!r.http_error && r.http_description && r.description.trim() !== r.http_description.trim()) {
                    descWarningHtml = '<div class="live-meta-warning" title="Значение отличается от тега <meta name=\'description\'> на странице.">На сайте: <span class="live-value">' + esc(r.http_description) + '</span></div>';
                }

                tr.innerHTML =
                    '<td class="td-num">' + (idx + 1) + '</td>' +
                    '<td class="td-url"><a href="' + esc(r.url) + '" target="_blank" style="color:#4a90d9;text-decoration:none" title="Открыть страницу">' + esc(r.url) + '</a></td>' +
                    '<td>' + typeBadge(r.type) + '</td>' +
                    '<td style="font-size:11px">' + esc(r.name || '—') + '</td>' +
                    '<td style="font-size:11px;color:#6b7280">' + esc(r.iblock || '—') + '</td>' +
                    '<td><div class="field-wrap">' +
                    '<div class="editable data-field" data-meta="title" contenteditable="' + ce + '">' + esc(r.title || '') + '</div>' +
                    titleWarningHtml +
                    '</div></td>' +
                    '<td><div class="field-wrap">' +
                    '<div class="editable data-field" data-meta="description" contenteditable="' + ce + '">' + esc(r.description || '') + '</div>' +
                    descWarningHtml +
                    '</div></td>' +
                    '<td class="td-act">' +
                    (editable ?
                        '<button class="btn btn-primary btn-save-row" style="padding:5px 10px;font-size:11px">Сохр.</button>' :
                        '<span style="color:#c0c7d0;font-size:11px">—</span>') +
                    '</td>';

                return tr;
            }

            var btnLoad = document.getElementById('btn-load');
            if (btnLoad) {
                btnLoad.addEventListener('click', function() {
                    var raw = document.getElementById('url-input').value.trim();
                    if (!raw) {
                        setLog('Введите хотя бы один URL', true);
                        return;
                    }
                    clearLog();

                    // Разбор и дедупликация списка
                    var urls = raw.split(/\r?\n/).map(function(s) {
                        return s.trim();
                    }).filter(Boolean);
                    urls = urls.filter(function(u, i) {
                        return urls.indexOf(u) === i;
                    });

                    var btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span> Загрузка...';

                    var chkHttp = document.getElementById('chk-http');
                    var checkHttp = (chkHttp && chkHttp.checked) ? 'Y' : 'N';

                    var tbody = document.getElementById('meta-tbody');
                    tbody.innerHTML = '';
                    document.getElementById('meta-table').style.display = '';
                    document.getElementById('empty-state').style.display = 'none';

                    // Сразу рисуем строки-заглушки со спиннером — порядок сохраняется
                    var placeholders = urls.map(function(u, i) {
                        var tr = document.createElement('tr');
                        tr.className = 'row-loading';
                        tr.innerHTML =
                            '<td class="td-num">' + (i + 1) + '</td>' +
                            '<td class="td-url">' + esc(u) + '</td>' +
                            '<td colspan="5" style="color:#9aa0ab"><span class="spinner"></span> загрузка…</td>' +
                            '<td class="td-act"><span style="color:#c0c7d0;font-size:11px">—</span></td>';
                        tbody.appendChild(tr);
                        return tr;
                    });

                    var CONCURRENCY = 4; // параллельных запросов; снизь до 2, если сервер слабый
                    var nextIdx = 0;
                    var doneCnt = 0;

                    setProgress(0);

                    function fillError(i, msg) {
                        var tr = placeholders[i];
                        tr.className = 'row-unknown';
                        tr.innerHTML =
                            '<td class="td-num">' + (i + 1) + '</td>' +
                            '<td class="td-url">' + esc(urls[i]) + '</td>' +
                            '<td colspan="5" style="color:#c0392b;font-size:11px">' + esc(msg) + '</td>' +
                            '<td class="td-act"><span style="color:#c0c7d0;font-size:11px">—</span></td>';
                    }

                    function worker() {
                        if (nextIdx >= urls.length) return Promise.resolve();
                        var i = nextIdx++;

                        var fd = new FormData();
                        fd.append('action', 'load_urls');
                        fd.append('urls', urls[i]);
                        fd.append('check_http', checkHttp);

                        return fetch(window.location.pathname, {
                                method: 'POST',
                                body: fd
                            })
                            .then(function(r) {
                                return r.json();
                            })
                            .then(function(data) {
                                if (data && data.success && data.rows && data.rows[0]) {
                                    var newTr = renderRow(data.rows[0], i);
                                    tbody.replaceChild(newTr, placeholders[i]);
                                    placeholders[i] = newTr;
                                } else {
                                    fillError(i, 'Ошибка обработки');
                                }
                            })
                            .catch(function() {
                                fillError(i, 'Сетевая ошибка');
                            })
                            .then(function() {
                                doneCnt++;
                                setProgress(Math.round((doneCnt / urls.length) * 100));
                                return worker(); // берём следующий URL из очереди
                            });
                    }

                    var pool = [];
                    for (var k = 0; k < Math.min(CONCURRENCY, urls.length); k++) {
                        pool.push(worker());
                    }

                    Promise.all(pool).then(function() {
                        btn.disabled = false;
                        btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Загрузить';
                        setProgress(null);

                        var cnt = document.getElementById('rows-count');
                        if (cnt) {
                            cnt.style.display = '';
                            cnt.innerHTML = '<span class="count-badge">' + urls.length + '</span>';
                        }
                        updateSaveAllBtn();
                        setLog('Загружено ' + urls.length + ' строк', false);
                    });
                });
            }


            var btnClearInput = document.getElementById('btn-clear-input');
            if (btnClearInput) {
                btnClearInput.addEventListener('click', function() {
                    document.getElementById('url-input').value = '';
                });
            }

            var metaTable = document.getElementById('meta-table');
            if (metaTable) {
                metaTable.addEventListener('input', function(e) {
                    var field = e.target;
                    if (!field.classList.contains('data-field')) return;
                    var len = field.innerText.trim().length;
                    var meta = field.getAttribute('data-meta');
                    var max = meta === 'title' ? 60 : 160;
                    var warn = meta === 'title' ? 50 : 140;
                    var cnt = field.closest('.field-wrap').querySelector('.char-count');
                    if (cnt) {
                        cnt.textContent = len;
                        cnt.className = 'char-count' + (len > max ? ' over' : len > warn ? ' warn' : '');
                    }
                    markChanged(field.closest('tr'));
                });

                metaTable.addEventListener('paste', function(e) {
                    var target = e.target;
                    if (!target.classList.contains('data-field') || target.getAttribute('contenteditable') === 'false') return;
                    e.preventDefault();

                    var cd = e.clipboardData || window.clipboardData;
                    var text = (cd.getData('text/plain') || cd.getData('text') || '').trim();
                    if (!text) return;

                    var rawLines = text.split(/\r?\n/).map(function(line) {
                        return line.trim();
                    }).filter(function(line) {
                        return line !== '';
                    });

                    // Алгоритм автоматического распознавания чередующихся строк (Title / Description)
                    var isAlternating = false;
                    if (rawLines.length % 2 === 0 && rawLines.length >= 2) {
                        var oddLength = 0; // Заголовки (индексы 0, 2, 4...)
                        var evenLength = 0; // Описания (индексы 1, 3, 5...)
                        for (var k = 0; k < rawLines.length; k++) {
                            if (k % 2 === 0) {
                                oddLength += rawLines[k].length;
                            } else {
                                evenLength += rawLines[k].length;
                            }
                        }
                        var avgOdd = oddLength / (rawLines.length / 2);
                        var avgEven = evenLength / (rawLines.length / 2);

                        // Если четные строки (описания) в среднем существенно длиннее нечетных (заголовков)
                        if ((avgEven > avgOdd * 1.5 && avgEven > 40) || Math.abs(avgOdd - avgEven) > 25) {
                            isAlternating = true;
                        }
                    }

                    var rows = [];
                    if (text.indexOf('\t') !== -1) {
                        // Вариант 1: Данные скопированы из Excel/Google Таблиц
                        rows = text.split(/\r?\n/).map(function(r) {
                            return r.split('\t');
                        }).filter(function(r) {
                            return r.some(function(c) {
                                return c.trim() !== '';
                            });
                        });
                    } else if (isAlternating) {
                        // Вариант 2: Чередующиеся строки (Title, затем Description)
                        for (var i = 0; i < rawLines.length; i += 2) {
                            rows.push([rawLines[i], rawLines[i + 1]]);
                        }
                    } else {
                        // Вариант 3: Обычный плоский список значений для одной активной колонки
                        rows = rawLines.map(function(line) {
                            return [line];
                        });
                    }

                    var metaType = target.getAttribute('data-meta');
                    var curTr = target.closest('tr');
                    var allTrs = Array.from(curTr.parentNode.querySelectorAll('tr'));
                    var startIdx = allTrs.indexOf(curTr);

                    rows.forEach(function(rowData, i) {
                        var tr = allTrs[startIdx + i];
                        if (!tr) return;
                        if (rowData.length >= 2) {
                            var tf = tr.querySelector('.data-field[data-meta="title"]');
                            var df = tr.querySelector('.data-field[data-meta="description"]');
                            if (tf && tf.getAttribute('contenteditable') === 'true') {
                                tf.innerText = rowData[0].trim();
                                updateCharCount(tf);
                                markChanged(tr);
                            }
                            if (df && df.getAttribute('contenteditable') === 'true') {
                                df.innerText = rowData[1].trim();
                                updateCharCount(df);
                                markChanged(tr);
                            }
                        } else if (rowData[0] && rowData[0].trim()) {
                            var f = tr.querySelector('.data-field[data-meta="' + metaType + '"]');
                            if (f && f.getAttribute('contenteditable') === 'true') {
                                f.innerText = rowData[0].trim();
                                updateCharCount(f);
                                markChanged(tr);
                            }
                        }
                    });
                });

                metaTable.addEventListener('click', function(e) {
                    if (e.target.classList.contains('btn-save-row') || e.target.closest('.btn-save-row')) {
                        var btn = e.target.closest('.btn-save-row') || e.target;
                        var tr = btn.closest('tr');
                        saveRow(tr);
                    }
                });
            }

            function updateCharCount(field) {
                var len = field.innerText.trim().length;
                var meta = field.getAttribute('data-meta');
                var max = meta === 'title' ? 60 : 160;
                var warn = meta === 'title' ? 50 : 140;
                var cnt = field.closest('.field-wrap').querySelector('.char-count');
                if (!cnt) return;
                cnt.textContent = len;
                cnt.className = 'char-count' + (len > max ? ' over' : len > warn ? ' warn' : '');
            }

            function markChanged(tr) {
                tr.classList.remove('row-ok', 'row-error');
                tr.setAttribute('data-dirty', 'true');
                updateSaveAllBtn();
            }

            function saveRow(tr) {
                return new Promise(function(resolve) {
                    var type = tr.getAttribute('data-type');
                    if (type === 'unknown') return resolve();

                    var origClass = tr.className;
                    tr.className = origClass.replace(/row-ok|row-error|row-saving/g, '').trim() + ' row-saving';

                    var chkHttp = document.getElementById('chk-http');

                    var fd = new FormData();
                    fd.append('action', 'update_meta');
                    fd.append('id', tr.getAttribute('data-id'));
                    fd.append('iblock_id', tr.getAttribute('data-iblock-id'));
                    fd.append('type', type);
                    fd.append('url', tr.getAttribute('data-url'));
                    fd.append('title', tr.querySelector('.data-field[data-meta="title"]').innerText.trim());
                    fd.append('description', tr.querySelector('.data-field[data-meta="description"]').innerText.trim());
                    fd.append('check_http', (chkHttp && chkHttp.checked) ? 'Y' : 'N');

                    fetch(window.location.pathname, {
                            method: 'POST',
                            body: fd
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            var base = 'row-' + type;
                            if (data.success) {
                                tr.className = base + ' row-ok';
                                tr.removeAttribute('data-dirty');

                                var titleWrap = tr.querySelector('.data-field[data-meta="title"]').closest('.field-wrap');
                                var descWrap = tr.querySelector('.data-field[data-meta="description"]').closest('.field-wrap');

                                var oldTitleWarn = titleWrap.querySelector('.live-meta-warning, .live-meta-error');
                                if (oldTitleWarn) oldTitleWarn.remove();
                                var oldDescWarn = descWrap.querySelector('.live-meta-warning, .live-meta-error');
                                if (oldDescWarn) oldDescWarn.remove();

                                var newTitle = tr.querySelector('.data-field[data-meta="title"]').innerText.trim();
                                var newDesc = tr.querySelector('.data-field[data-meta="description"]').innerText.trim();

                                if (data.http_error) {
                                    var errDiv = document.createElement('div');
                                    errDiv.className = 'live-meta-error';
                                    errDiv.textContent = 'Ошибка HTTP проверки: ' + data.http_error;
                                    titleWrap.appendChild(errDiv);
                                } else {
                                    if (data.http_title && newTitle !== data.http_title.trim()) {
                                        var warnDiv = document.createElement('div');
                                        warnDiv.className = 'live-meta-warning';
                                        warnDiv.innerHTML = 'На сайте: <span class="live-value">' + esc(data.http_title) + '</span>';
                                        titleWrap.appendChild(warnDiv);
                                    }
                                    if (data.http_description && newDesc !== data.http_description.trim()) {
                                        var warnDiv = document.createElement('div');
                                        warnDiv.className = 'live-meta-warning';
                                        warnDiv.innerHTML = 'На сайте: <span class="live-value">' + esc(data.http_description) + '</span>';
                                        descWrap.appendChild(warnDiv);
                                    }
                                }
                            } else {
                                tr.className = base + ' row-error';
                                setLog('Ошибка на ' + tr.getAttribute('data-url') + ': ' + data.message, true);
                            }
                            updateSaveAllBtn();
                            resolve();
                        })
                        .catch(function() {
                            tr.className = 'row-' + type + ' row-error';
                            setLog('Сетевая ошибка при сохранении ' + tr.getAttribute('data-url'), true);
                            updateSaveAllBtn();
                            resolve();
                        });
                });
            }

            var btnSaveAll = document.getElementById('btn-save-all');
            if (btnSaveAll) {
                btnSaveAll.addEventListener('click', async function() {
                    var dirty = Array.from(document.querySelectorAll('#meta-tbody tr[data-dirty="true"]'));
                    if (!dirty.length) return;
                    clearLog();
                    this.disabled = true;
                    setProgress(0);

                    for (var i = 0; i < dirty.length; i++) {
                        document.getElementById('save-all-label').textContent = 'Сохранение ' + (i + 1) + ' / ' + dirty.length + '...';
                        setProgress(Math.round(((i + 1) / dirty.length) * 100));
                        await saveRow(dirty[i]);
                    }

                    setProgress(null);
                    this.disabled = false;
                    updateSaveAllBtn();
                    setLog('Готово. Сохранено: ' + dirty.length + ' строк.', false);
                });
            }

        })();

        // ─── UPDATE CHECKER ───
        (function () {
            var build = '<?= HELPFILE_BUILD ?>';
            if (build === 'dev') return; // локальная сборка — не проверяем
            fetch('https://raw.githubusercontent.com/Lernoy/seo-helpfile/master/VERSION?_=' + Date.now(), {
                cache: 'no-store'
            })
            .then(function (r) { return r.ok ? r.text() : null; })
            .then(function (text) {
                if (!text) return;
                var latest = text.trim();
                if (latest && latest !== build) {
                    var badge = document.getElementById('update-badge');
                    if (badge) badge.style.display = 'flex';
                }
            })
            .catch(function () {});
        })();
    </script>
</body>

</html>