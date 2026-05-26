<?php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>SEO Meta Editor & DOCX Converter</title>
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
            font-size: 13px;
            background: #eef0f3;
            color: #2b2f3a;
            min-height: 100vh;
        }

        /* ---- TOPBAR ---- */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #d8dde6;
            height: 48px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .topbar-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            color: #333;
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
            color: #6b7280;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .nav-tab:hover {
            color: #333;
            background: #f1f5f9;
        }

        .nav-tab.active {
            color: #4a90d9;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .topbar-sep {
            flex: 1;
        }

        .topbar-meta {
            font-size: 11px;
            color: #9aa0ab;
            margin-right: 15px;
        }

        .btn-logout {
            background: none;
            border: 1px solid #d0d5dd;
            border-radius: 3px;
            padding: 4px 10px;
            font-size: 12px;
            color: #6b7280;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-logout:hover {
            border-color: #c0392b;
            color: #c0392b;
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

        .service-panel {
            display: none;
        }

        .service-panel.active {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ---- CARD ---- */
        .card {
            background: #fff;
            border: 1px solid #d8dde6;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.35s ease both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-head {
            padding: 14px 18px;
            border-bottom: 1px solid #eef0f3;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-head h2 {
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        .card-body {
            padding: 16px 18px;
        }

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
            border: 1px solid #d0d5dd;
            border-radius: 3px;
            font-size: 12px;
            font-family: "Courier New", monospace;
            color: #333;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            line-height: 1.6;
            width: 100%;
        }

        .url-textarea:focus {
            border-color: #4a90d9;
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
        }

        .url-hint {
            font-size: 11px;
            color: #9aa0ab;
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

        .btn:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background: #4a90d9;
            color: #fff;
            border-color: #3a7bc8;
        }

        .btn-primary:hover {
            background: #357abd;
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
            background: #fff;
            color: #555;
            border-color: #d0d5dd;
        }

        .btn-default:hover {
            background: #f5f7fa;
            border-color: #b0b7c3;
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
            color: #6b7280;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-file {
            background: #27ae60;
        }

        .dot-section {
            background: #4a90d9;
        }

        .dot-element {
            background: #f39c12;
        }

        .dot-unknown {
            background: #e74c3c;
        }

        /* ---- LOG BAR & PROGRESS ---- */
        .log-bar {
            min-height: 32px;
            padding: 6px 12px;
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 3px;
            font-size: 12px;
            color: #795548;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideDown 0.25s ease;
        }

        .log-bar.visible {
            display: flex;
        }

        .log-bar.error {
            background: #fff0f0;
            border-color: #fca5a5;
            color: #c0392b;
        }

        .progress-wrap {
            height: 3px;
            background: #eef0f3;
            border-radius: 0;
            overflow: hidden;
            display: none;
        }

        .progress-wrap.visible {
            display: block;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4a90d9, #27ae60);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* ---- TABLE ---- */
        .table-wrap {
            overflow-x: auto;
            border-top: 1px solid #eef0f3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        colgroup col.c-num {
            width: 40px;
        }

        colgroup col.c-url {
            width: 16%;
        }

        colgroup col.c-type {
            width: 110px;
        }

        colgroup col.c-name {
            width: 11%;
        }

        colgroup col.c-ib {
            width: 9%;
        }

        colgroup col.c-title {
            width: 22%;
        }

        colgroup col.c-desc {
            width: 24%;
        }

        colgroup col.c-act {
            width: 90px;
        }

        th {
            background: #f5f7fa;
            border-bottom: 2px solid #d8dde6;
            border-right: 1px solid #e8ecf0;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th:last-child {
            border-right: none;
        }

        td {
            border-bottom: 1px solid #eef0f3;
            border-right: 1px solid #f0f2f5;
            padding: 7px 10px;
            vertical-align: top;
            word-break: break-word;
        }

        td:last-child {
            border-right: none;
        }

        td.td-num {
            color: #aaa;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
        }

        td.td-url {
            font-size: 11px;
            font-family: "Courier New", monospace;
            color: #4a90d9;
        }

        td.td-act {
            vertical-align: middle;
            text-align: center;
        }

        /* ---- ROW TYPES ---- */
        tr.row-physical_file,
        tr.row-wp_frontpage {
            background: #f0fdf4;
        }

        tr.row-iblock_section,
        tr.row-wp_post {
            background: #f0f7ff;
        }

        tr.row-iblock_element,
        tr.row-wp_term {
            background: #fffbf0;
        }

        tr.row-unknown {
            background: #fff5f5;
        }

        tr {
            transition: background-color 0.35s ease;
        }

        tr.row-saving {
            background: #fffde7 !important;
        }

        tr.row-ok {
            background: #e8f5e9 !important;
            animation: rowPulse 0.5s ease;
        }

        tr.row-error {
            background: #ffebee !important;
        }

        @keyframes rowPulse {
            0% {
                background-color: #a5d6a7;
            }

            100% {
                background-color: #e8f5e9;
            }
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

        .badge-file {
            background: #dcfce7;
            color: #166534;
        }

        .badge-section {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-element {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-unknown {
            background: #fee2e2;
            color: #991b1b;
        }

        .editable {
            border: 1px dashed #d0d5dd;
            border-radius: 2px;
            padding: 5px 7px;
            min-height: 38px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            line-height: 1.45;
            font-size: 12px;
            background: #fff;
        }

        .editable[contenteditable="true"]:hover {
            border-color: #b0b7c3;
            background: rgba(255, 255, 255, 0.7);
        }

        .editable[contenteditable="true"]:focus {
            border-color: #4a90d9;
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
            background: #fff;
            border-style: solid;
        }

        .field-wrap {
            position: relative;
        }

        .char-count {
            position: absolute;
            bottom: -14px;
            right: 0;
            font-size: 10px;
            color: #b0b7c3;
        }

        .char-count.warn {
            color: #f39c12;
        }

        .char-count.over {
            color: #e74c3c;
            font-weight: 700;
        }

        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #d8dde6;
            border-top-color: #4a90d9;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9aa0ab;
        }

        .empty-state svg {
            margin-bottom: 16px;
            opacity: 0.35;
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 18px;
            padding: 0 6px;
            background: #4a90d9;
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
            background: #fef3c7;
            padding: 0 2px;
            border-radius: 2px;
        }

        .live-meta-error {
            font-size: 10px;
            color: #dc2626;
            margin-top: 3px;
            line-height: 1.35;
        }

        /* ── СТИЛИ КОНВЕРТЕРА DOCX ── */
        .upload-form {
            background: #fff;
            border: 1px dashed #d0d5dd;
            border-radius: 4px;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            margin-bottom: 1.5rem;
        }

        .upload-form:hover {
            border-color: #4a90d9;
            background: #f5f9ff;
        }

        .upload-form input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-icon {
            font-size: 26px;
            color: #9aa0ab;
            margin-bottom: 10px
        }

        .upload-label {
            font-size: 13px;
            color: #6b7280
        }

        .upload-label strong {
            color: #2b2f3a;
            font-weight: 600
        }

        .upload-hint {
            font-size: 11px;
            color: #9aa0ab;
            margin-top: 4px;
            font-family: "Courier New", monospace;
        }

        .upload-form .selected-name {
            margin-top: 12px;
            font-size: 11px;
            font-family: "Courier New", monospace;
            color: #4a90d9;
            display: none;
        }

        .btn-submit {
            display: none;
            margin: 0 auto;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 8px 24px;
            background: #4a90d9;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background .1s;
        }

        .btn-submit:hover {
            background: #357abd;
        }

        .docx-tabs {
            display: flex;
            border-bottom: 1px solid #d8dde6;
        }

        .docx-tab {
            font-size: 11px;
            font-family: inherit;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 9px 16px;
            cursor: pointer;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: color .1s, border-color .1s;
        }

        .docx-tab.active {
            color: #4a90d9;
            border-bottom-color: #4a90d9;
        }

        .docx-pane {
            display: none;
            background: #fff;
            border: 1px solid #d8dde6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }

        .docx-pane.active {
            display: block
        }

        .h1-field-wrap {
            margin-bottom: 1.25rem
        }

        .h1-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .h1-field {
            width: 100%;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #d8dde6;
            border-radius: 4px;
            color: #2b2f3a;
            font-family: "Courier New", monospace;
            font-size: 13px;
            resize: none;
            overflow: hidden;
            cursor: pointer;
            transition: border-color .15s;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .h1-field:focus {
            outline: none;
            border-color: #4a90d9;
        }

        .code-wrap {
            position: relative;
        }

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

        .code-block:focus {
            outline: none
        }

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
        .copy-btn.ok {
            background: #4a90d9;
            color: #fff;
            border-color: #4a90d9;
        }

        .img-dir {
            font-size: 10px;
            color: #6b7280;
            padding: 10px 14px;
            border-top: 1px solid #eef0f3;
            background: #fafafa;
        }

        .img-dir span {
            color: #4a90d9;
            font-weight: 600;
        }

        .imgs-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px;
            max-height: 400px;
            overflow-y: auto;
        }

        .img-card {
            background: #fff;
            border: 1px solid #d8dde6;
            border-radius: 4px;
            overflow: hidden;
            width: 160px;
        }

        .img-card img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            display: block
        }

        .img-card-meta {
            padding: 6px 8px;
            font-size: 10px;
            color: #6b7280;
        }

        .img-card-name {
            color: #2b2f3a;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .no-imgs {
            padding: 2rem;
            text-align: center;
            font-size: 11px;
            color: #9aa0ab;
        }

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

        .update-badge:hover {
            background: #fde68a;
        }

        /* ---- SERVER INFO ---- */
        .srvinfo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            padding: 16px;
        }

        .srvinfo-card {
            border: 1px solid #e5e9f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .srvinfo-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e9f0;
            font-size: 11px;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .srvinfo-table {
            width: 100%;
            border-collapse: collapse;
        }

        .srvinfo-table tr:not(:last-child) td {
            border-bottom: 1px solid #f1f5f9;
        }

        .srvinfo-table td {
            padding: 7px 14px;
            font-size: 11px;
            vertical-align: middle;
        }

        .srvinfo-table td:first-child {
            color: #6b7280;
            white-space: nowrap;
            width: 46%;
        }

        .srvinfo-table td:last-child {
            color: #1a1a2e;
            font-weight: 500;
            word-break: break-all;
        }

        .si-ok   { color: #166534; }
        .si-warn { color: #92400e; }
        .si-err  { color: #c0392b; }

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
        .si-dot-off  { background: #d1d5db; }

        .srvinfo-bar-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .srvinfo-bar {
            flex: 1;
            height: 5px;
            background: #e5e9f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .srvinfo-bar-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .3s;
        }

        .srvinfo-bar-fill.ok   { background: #22c55e; }
        .srvinfo-bar-fill.warn { background: #f59e0b; }
        .srvinfo-bar-fill.err  { background: #ef4444; }
    </style>
</head>