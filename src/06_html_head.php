<?php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>SEO Meta Editor & DOCX Converter</title>
    <script>
        (function () { var t = localStorage.getItem('hf-theme'); if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); }());
    </script>
    <style>
        /* ---- CSS VARIABLES ---- */
        :root {
            --bg-page:          #eef0f3;
            --bg-surface:       #ffffff;
            --bg-elevated:      #f5f7fa;
            --bg-raised:        #f8fafc;
            --text-primary:     #2b2f3a;
            --text-heading:     #333333;
            --text-secondary:   #6b7280;
            --text-muted:       #9aa0ab;
            --border:           #d8dde6;
            --border-light:     #eef0f3;
            --border-medium:    #d0d5dd;
            --border-table:     #f0f2f5;
            --shadow:           rgba(0, 0, 0, 0.05);
            --topbar-shadow:    rgba(0, 0, 0, 0.06);
            --accent:           #4a90d9;
            --accent-hover:     #357abd;
            --accent-bg:        #eff6ff;
            --accent-border:    #bfdbfe;
            --row-file-bg:      #f0fdf4;
            --row-section-bg:   #f0f7ff;
            --row-element-bg:   #fffbf0;
            --row-unknown-bg:   #fff5f5;
            --row-saving-bg:    #fffde7;
            --row-ok-bg:        #e8f5e9;
            --row-error-bg:     #ffebee;
            --log-bg:           #fff8e1;
            --log-border:       #ffe082;
            --log-color:        #795548;
            --log-err-bg:       #fff0f0;
            --log-err-border:   #fca5a5;
            --log-err-color:    #c0392b;
            --badge-file-bg:    #dcfce7;
            --badge-file-color: #166534;
            --badge-sec-bg:     #dbeafe;
            --badge-sec-color:  #1e40af;
            --badge-el-bg:      #fef3c7;
            --badge-el-color:   #92400e;
            --badge-unk-bg:     #fee2e2;
            --badge-unk-color:  #991b1b;
            --editable-bg:      #ffffff;
            --upload-bg:        #ffffff;
            --upload-hover:     #f5f9ff;
            --imgdir-bg:        #fafafa;
            --livewarn-bg:      #fef3c7;
            --progress-bg:      #eef0f3;
        }

        [data-theme="dark"] {
            --bg-page:          #0f1117;
            --bg-surface:       #1a1e2e;
            --bg-elevated:      #222738;
            --bg-raised:        #1e2335;
            --text-primary:     #dde3ed;
            --text-heading:     #c8d0e0;
            --text-secondary:   #8a95a8;
            --text-muted:       #5a6478;
            --border:           #2d3348;
            --border-light:     #212638;
            --border-medium:    #374151;
            --border-table:     #252a3a;
            --shadow:           rgba(0, 0, 0, 0.35);
            --topbar-shadow:    rgba(0, 0, 0, 0.4);
            --accent-bg:        #162a45;
            --accent-border:    #2563eb;
            --row-file-bg:      #0c1e15;
            --row-section-bg:   #0c1b2e;
            --row-element-bg:   #1e1708;
            --row-unknown-bg:   #1e0c0c;
            --row-saving-bg:    #1a1708;
            --row-ok-bg:        #0c1e15;
            --row-error-bg:     #1e0c0c;
            --log-bg:           #1a1708;
            --log-border:       #78350f;
            --log-color:        #fbbf24;
            --log-err-bg:       #1e0c0c;
            --log-err-border:   #7f1d1d;
            --log-err-color:    #f87171;
            --badge-file-bg:    #052e16;
            --badge-file-color: #86efac;
            --badge-sec-bg:     #162a45;
            --badge-sec-color:  #93c5fd;
            --badge-el-bg:      #3a1e06;
            --badge-el-color:   #fcd34d;
            --badge-unk-bg:     #3a0a0a;
            --badge-unk-color:  #fca5a5;
            --editable-bg:      #1a1e2e;
            --upload-bg:        #1a1e2e;
            --upload-hover:     #1a2a40;
            --imgdir-bg:        #151929;
            --livewarn-bg:      #3a1e06;
            --progress-bg:      #212638;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            background: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background .25s, color .25s;
        }

        /* ---- TOPBAR ---- */
        .topbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            height: 48px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 1px 4px var(--topbar-shadow);
            transition: background .25s, border-color .25s;
        }

        .topbar-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text-heading);
            text-decoration: none;
        }

        .topbar-nav {
            display: flex;
            gap: 4px;
            margin-left: 20px;
        }

        .nav-tab {
            background: none;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .nav-tab:hover {
            color: var(--text-heading);
            background: var(--bg-elevated);
        }

        .nav-tab.active {
            color: var(--accent);
            background: var(--accent-bg);
            border-color: var(--accent-border);
        }

        .topbar-sep { flex: 1; }

        .topbar-meta {
            font-size: 11px;
            color: var(--text-muted);
            margin-right: 15px;
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--border-medium);
            border-radius: 3px;
            padding: 4px 10px;
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-logout:hover {
            border-color: #c0392b;
            color: #c0392b;
        }

        /* ---- THEME TOGGLE ---- */
        .btn-theme {
            background: none;
            border: 1px solid var(--border-medium);
            border-radius: 3px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            flex-shrink: 0;
            transition: border-color .15s, color .15s, background .15s;
        }

        .btn-theme:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-bg);
        }

        /* ---- MAIN LAYOUT ---- */
        .main {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .service-panel { display: none; }

        .service-panel.active {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ---- CARD ---- */
        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            box-shadow: 0 1px 3px var(--shadow);
            animation: fadeIn 0.35s ease both;
            transition: background .25s, border-color .25s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-head {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-head h2 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .card-body { padding: 16px 18px; }

        /* ---- URL INPUT PANEL (SEO) ---- */
        .url-panel {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .url-textarea {
            flex: 1;
            height: 100px;
            padding: 8px 10px;
            border: 1px solid var(--border-medium);
            border-radius: 3px;
            font-size: 12px;
            font-family: "Courier New", monospace;
            color: var(--text-primary);
            background: var(--editable-bg);
            resize: vertical;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            line-height: 1.6;
            width: 100%;
        }

        .url-textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
        }

        .url-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
            line-height: 1.5;
        }

        .url-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 2px;
        }

        /* ---- BUTTONS ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background 0.15s, border-color 0.15s, transform 0.1s, box-shadow 0.15s;
            white-space: nowrap;
            outline: none;
        }

        .btn:active { transform: scale(0.97); }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border-color: #3a7bc8;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            box-shadow: 0 2px 8px rgba(74, 144, 217, 0.3);
        }

        .btn-success {
            background: #27ae60;
            color: #fff;
            border-color: #219a52;
        }

        .btn-success:hover {
            background: #219a52;
            box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        }

        .btn-default {
            background: var(--bg-surface);
            color: var(--text-secondary);
            border-color: var(--border-medium);
        }

        .btn-default:hover {
            background: var(--bg-elevated);
            border-color: var(--text-muted);
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ---- TOOLBAR ---- */
        .toolbar-right {
            margin-left: auto;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .legend {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--text-secondary);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-file    { background: #27ae60; }
        .dot-section { background: #4a90d9; }
        .dot-element { background: #f39c12; }
        .dot-unknown { background: #e74c3c; }

        /* ---- LOG BAR & PROGRESS ---- */
        .log-bar {
            min-height: 32px;
            padding: 6px 12px;
            background: var(--log-bg);
            border: 1px solid var(--log-border);
            border-radius: 3px;
            font-size: 12px;
            color: var(--log-color);
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideDown 0.25s ease;
        }

        .log-bar.visible { display: flex; }

        .log-bar.error {
            background: var(--log-err-bg);
            border-color: var(--log-err-border);
            color: var(--log-err-color);
        }

        .progress-wrap {
            height: 3px;
            background: var(--progress-bg);
            border-radius: 0;
            overflow: hidden;
            display: none;
        }

        .progress-wrap.visible { display: block; }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4a90d9, #27ae60);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* ---- TABLE ---- */
        .table-wrap {
            overflow-x: auto;
            border-top: 1px solid var(--border-light);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        colgroup col.c-num  { width: 40px; }
        colgroup col.c-url  { width: 16%; }
        colgroup col.c-type { width: 110px; }
        colgroup col.c-name { width: 11%; }
        colgroup col.c-ib   { width: 9%; }
        colgroup col.c-title{ width: 22%; }
        colgroup col.c-desc { width: 24%; }
        colgroup col.c-act  { width: 90px; }

        th {
            background: var(--bg-elevated);
            border-bottom: 2px solid var(--border);
            border-right: 1px solid var(--border);
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th:last-child { border-right: none; }

        td {
            border-bottom: 1px solid var(--border-light);
            border-right: 1px solid var(--border-table);
            padding: 7px 10px;
            vertical-align: top;
            word-break: break-word;
            background: inherit;
        }

        td:last-child { border-right: none; }

        td.td-num {
            color: var(--text-muted);
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
        }

        td.td-url {
            font-size: 11px;
            font-family: "Courier New", monospace;
            color: var(--accent);
        }

        td.td-act {
            vertical-align: middle;
            text-align: center;
        }

        /* ---- ROW TYPES ---- */
        tr.row-physical_file,
        tr.row-wp_frontpage  { background: var(--row-file-bg); }

        tr.row-iblock_section,
        tr.row-wp_post       { background: var(--row-section-bg); }

        tr.row-iblock_element,
        tr.row-wp_term       { background: var(--row-element-bg); }

        tr.row-unknown       { background: var(--row-unknown-bg); }

        tr { transition: background-color 0.35s ease; }

        tr.row-saving { background: var(--row-saving-bg) !important; }
        tr.row-ok     { background: var(--row-ok-bg) !important; animation: rowPulse 0.5s ease; }
        tr.row-error  { background: var(--row-error-bg) !important; }

        @keyframes rowPulse {
            0%   { background-color: #4caf50; }
            100% { background-color: var(--row-ok-bg); }
        }

        /* ---- BADGES & FIELDS ---- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-file    { background: var(--badge-file-bg); color: var(--badge-file-color); }
        .badge-section { background: var(--badge-sec-bg);  color: var(--badge-sec-color); }
        .badge-element { background: var(--badge-el-bg);   color: var(--badge-el-color); }
        .badge-unknown { background: var(--badge-unk-bg);  color: var(--badge-unk-color); }

        .editable {
            border: 1px dashed var(--border-medium);
            border-radius: 2px;
            padding: 5px 7px;
            min-height: 38px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            line-height: 1.45;
            font-size: 12px;
            background: var(--editable-bg);
            color: var(--text-primary);
        }

        .editable[contenteditable="true"]:hover {
            border-color: var(--text-muted);
        }

        .editable[contenteditable="true"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
            border-style: solid;
        }

        .field-wrap { position: relative; }

        .char-count {
            position: absolute;
            bottom: -14px;
            right: 0;
            font-size: 10px;
            color: var(--text-muted);
        }

        .char-count.warn { color: #f39c12; }
        .char-count.over { color: #e74c3c; font-weight: 700; }

        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state svg { margin-bottom: 16px; opacity: 0.35; }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 18px;
            padding: 0 6px;
            background: var(--accent);
            color: #fff;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 700;
        }

        .live-meta-warning {
            font-size: 10px;
            color: #d97706;
            margin-top: 3px;
            line-height: 1.35;
        }

        .live-meta-warning .live-value {
            font-weight: 500;
            background: var(--livewarn-bg);
            padding: 0 2px;
            border-radius: 2px;
        }

        .live-meta-error {
            font-size: 10px;
            color: #dc2626;
            margin-top: 3px;
            line-height: 1.35;
        }

        /* ---- DOCX CONVERTER ---- */
        .upload-form {
            background: var(--upload-bg);
            border: 1px dashed var(--border-medium);
            border-radius: 4px;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            margin-bottom: 1.5rem;
        }

        .upload-form:hover {
            border-color: var(--accent);
            background: var(--upload-hover);
        }

        .upload-form input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-icon { font-size: 26px; color: var(--text-muted); margin-bottom: 10px; }
        .upload-label { font-size: 13px; color: var(--text-secondary); }
        .upload-label strong { color: var(--text-primary); font-weight: 600; }

        .upload-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
            font-family: "Courier New", monospace;
        }

        .upload-form .selected-name {
            margin-top: 12px;
            font-size: 11px;
            font-family: "Courier New", monospace;
            color: var(--accent);
            display: none;
        }

        .btn-submit {
            display: none;
            margin: 0 auto;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 8px 24px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background .1s;
        }

        .btn-submit:hover { background: var(--accent-hover); }

        .docx-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
        }

        .docx-tab {
            font-size: 11px;
            font-family: inherit;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 9px 16px;
            cursor: pointer;
            color: var(--text-secondary);
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: color .1s, border-color .1s;
        }

        .docx-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        .docx-pane {
            display: none;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 4px 4px;
        }

        .docx-pane.active { display: block; }

        .h1-field-wrap { margin-bottom: 1.25rem; }

        .h1-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .h1-field {
            width: 100%;
            padding: 10px 14px;
            background: var(--editable-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-primary);
            font-family: "Courier New", monospace;
            font-size: 13px;
            resize: none;
            overflow: hidden;
            cursor: pointer;
            transition: border-color .15s;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .h1-field:focus { outline: none; border-color: var(--accent); }

        .code-wrap { position: relative; }

        .code-block {
            display: block;
            width: 100%;
            padding: 1.25rem;
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.65;
            color: #a3be8c;
            background: #2e3440;
            white-space: pre-wrap;
            word-break: break-all;
            overflow-x: hidden;
            max-height: 480px;
            overflow-y: auto;
            border: none;
            resize: none;
            box-sizing: border-box;
            height: 400px;
            border-radius: 0 0 4px 4px;
        }

        .code-block:focus { outline: none; }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-family: inherit;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .05em;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #d8dee9;
            padding: 4px 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: all .15s;
        }

        .copy-btn:hover,
        .copy-btn.ok { background: #4a90d9; color: #fff; border-color: #4a90d9; }

        .img-dir {
            font-size: 10px;
            color: var(--text-secondary);
            padding: 10px 14px;
            border-top: 1px solid var(--border-light);
            background: var(--imgdir-bg);
        }

        .img-dir span { color: var(--accent); font-weight: 600; }

        .imgs-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px;
            max-height: 400px;
            overflow-y: auto;
        }

        .img-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            width: 160px;
        }

        .img-card img { width: 100%; height: 110px; object-fit: cover; display: block; }
        .img-card-meta { padding: 6px 8px; font-size: 10px; color: var(--text-secondary); }

        .img-card-name {
            color: var(--text-primary);
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .no-imgs { padding: 2rem; text-align: center; font-size: 11px; color: var(--text-muted); }

        /* ---- UPDATE BADGE ---- */
        .update-badge {
            display: none;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            color: #92400e;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s;
        }

        .update-badge:hover { background: #fde68a; }

        [data-theme="dark"] .update-badge {
            background: #3a1e06;
            border-color: #92400e;
            color: #fcd34d;
        }

        [data-theme="dark"] .update-badge:hover { background: #4a2808; }

        /* ---- SERVER INFO ---- */
        .srvinfo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            padding: 16px;
        }

        .srvinfo-card {
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
        }

        .srvinfo-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            background: var(--bg-raised);
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .srvinfo-table { width: 100%; border-collapse: collapse; }

        .srvinfo-table tr:not(:last-child) td {
            border-bottom: 1px solid var(--border-light);
        }

        .srvinfo-table td {
            padding: 7px 14px;
            font-size: 11px;
            vertical-align: middle;
        }

        .srvinfo-table td:first-child { color: var(--text-secondary); white-space: nowrap; width: 46%; }
        .srvinfo-table td:last-child  { color: var(--text-primary); font-weight: 500; word-break: break-all; }

        .si-ok   { color: #22c55e; }
        .si-warn { color: #f59e0b; }
        .si-err  { color: #ef4444; }

        .si-dot {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }

        .si-dot-ok   { background: #22c55e; }
        .si-dot-warn { background: #f59e0b; }
        .si-dot-err  { background: #ef4444; }
        .si-dot-off  { background: #6b7280; }

        .srvinfo-bar-wrap { display: flex; align-items: center; gap: 6px; }

        .srvinfo-bar {
            flex: 1;
            height: 5px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }

        .srvinfo-bar-fill { height: 100%; border-radius: 3px; transition: width .3s; }
        .srvinfo-bar-fill.ok   { background: #22c55e; }
        .srvinfo-bar-fill.warn { background: #f59e0b; }
        .srvinfo-bar-fill.err  { background: #ef4444; }
    </style>
</head>
