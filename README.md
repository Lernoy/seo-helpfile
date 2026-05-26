# SEO Meta Editor & DOCX Converter

Утилита для редактирования SEO-метатегов на Bitrix / WordPress + конвертер DOCX → HTML.  
Деплоится одним файлом `helpfile.php`, собирается из модульных исходников.

## Структура

| Файл | Содержимое |
|------|-----------|
| `src/01_init_auth.php` | Константы, определение CMS, авторизация, форма входа |
| `src/02_cms_functions.php` | Инициализация CMS, функции Bitrix, функции WordPress |
| `src/03_functions_files.php` | `getMetaFromFile`, `getMetaFromHttp`, `checkPageType`, загрузка инфоблоков |
| `src/04_ajax_docx.php` | AJAX-обработчик конвертации DOCX → HTML |
| `src/05_ajax_seo.php` | AJAX-обработчики `load_urls` и `update_meta` |
| `src/06_html_head.php` | HTML `<head>`, весь CSS |
| `src/07_html_body.php` | HTML-шаблон панелей (SEO Editor + DOCX Converter) |
| `src/08_js.php` | JavaScript |

## Сборка одного файла

```bash
php build.php
```

Создаёт `helpfile.php` в корне проекта. Его и загружать на сервер.

## Смена пароля

Файл `src/01_init_auth.php`:

```php
define('HELPFILE_PASSWORD', 'Qazwsxed35');
```
