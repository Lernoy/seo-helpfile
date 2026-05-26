<?php
// ===================== ОБРАБОТКА КОНВЕРТАЦИИ DOCX =====================
$result_html = '';
$image_dir   = '';
$docx_error  = '';
$h1_text     = '';
$initial_tab = 'meta-editor';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['docx'])) {
    $initial_tab = 'docx-converter';
    $file = $_FILES['docx'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $docx_error = 'Ошибка загрузки файла (код ' . $file['error'] . ')';
    } elseif (!preg_match('/\.docx$/i', $file['name'])) {
        $docx_error = 'Нужен файл .docx';
    } else {
        $dir_name   = 'image-' . date('Ymd_His');
        $dir_disk   = __DIR__ . '/image/' . $dir_name;
        $dir_web    = '/image/' . $dir_name;

        if (!is_dir(__DIR__ . '/image')) {
            mkdir(__DIR__ . '/image', 0755, true);
        }
        mkdir($dir_disk, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            $docx_error = 'Не удалось открыть DOCX';
        } else {
            $doc_xml  = $zip->getFromName('word/document.xml');
            $rels_xml = $zip->getFromName('word/_rels/document.xml.rels');

            $rels_dom = new DOMDocument();
            $rels_dom->loadXML($rels_xml);

            $rid_map = [];
            $hyperlink_rid_map = [];

            foreach ($rels_dom->getElementsByTagName('Relationship') as $rel) {
                $rId = $rel->getAttribute('Id');
                $target = $rel->getAttribute('Target');
                $type = $rel->getAttribute('Type');

                if (strpos($type, 'image') !== false) {
                    $rid_map[$rId] = $target;
                } elseif (strpos($type, 'hyperlink') !== false) {
                    $hyperlink_rid_map[$rId] = $target;
                }
            }

            $max_side    = 1920;
            $jpg_quality = 82;
            $use_gd = extension_loaded('gd');

            $rid_to_web = [];
            foreach ($rid_map as $rid => $target) {
                $fname    = basename($target);
                $zip_path = 'word/' . $target;
                $data     = $zip->getFromName($zip_path);
                if ($data === false) continue;

                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

                if ($use_gd && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                    $src = @imagecreatefromstring($data);
                    if ($src !== false) {
                        $w = imagesx($src);
                        $h = imagesy($src);

                        $has_alpha = false;
                        if ($ext === 'png') {
                            $color_type = (strlen($data) > 25) ? ord($data[25]) : 0;
                            $has_alpha = in_array($color_type, [4, 6]);
                            if (!$has_alpha) {
                                imagesavealpha($src, true);
                                foreach ([$w / 2, 0, $w - 1] as $px) {
                                    foreach ([$h / 2, 0, $h - 1] as $py) {
                                        $rgba = imagecolorat($src, (int)$px, (int)$py);
                                        if ((($rgba >> 24) & 0x7F) > 0) {
                                            $has_alpha = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }

                        if ($w > $max_side || $h > $max_side) {
                            if ($w >= $h) {
                                $new_w = $max_side;
                                $new_h = (int)round($h * $max_side / $w);
                            } else {
                                $new_h = $max_side;
                                $new_w = (int)round($w * $max_side / $h);
                            }
                            $dst = imagecreatetruecolor($new_w, $new_h);
                            if ($has_alpha) {
                                imagealphablending($dst, false);
                                imagesavealpha($dst, true);
                                $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                                imagefilledrectangle($dst, 0, 0, $new_w, $new_h, $trans);
                            } else {
                                $white = imagecolorallocate($dst, 255, 255, 255);
                                imagefilledrectangle($dst, 0, 0, $new_w, $new_h, $white);
                            }
                            imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                            imagedestroy($src);
                            $src = $dst;
                        } else {
                            if ($ext === 'png' && !$has_alpha) {
                                $dst = imagecreatetruecolor($w, $h);
                                $white = imagecolorallocate($dst, 255, 255, 255);
                                imagefilledrectangle($dst, 0, 0, $w, $h, $white);
                                imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
                                imagedestroy($src);
                                $src = $dst;
                            }
                        }

                        ob_start();
                        if ($has_alpha) {
                            imagepng($src, null, 9);
                            $out_ext = 'png';
                        } else {
                            imagejpeg($src, null, $jpg_quality);
                            $out_ext = 'jpg';
                        }
                        $data = ob_get_clean();
                        imagedestroy($src);

                        if ($out_ext !== $ext) {
                            $fname = pathinfo($fname, PATHINFO_FILENAME) . '.' . $out_ext;
                        }
                    }
                }

                $disk_path = $dir_disk . '/' . $fname;
                file_put_contents($disk_path, $data);
                $rid_to_web[$rid] = $dir_web . '/' . $fname;
            }
            $zip->close();

            libxml_use_internal_errors(true);
            $doc_dom = new DOMDocument();
            $doc_dom->loadXML($doc_xml);
            libxml_clear_errors();

            $NS_W  = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
            $NS_R  = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
            $NS_A  = 'http://schemas.openxmlformats.org/drawingml/2006/main';

            $xpath = new DOMXPath($doc_dom);
            $xpath->registerNamespace('w', $NS_W);
            $xpath->registerNamespace('r', $NS_R);
            $xpath->registerNamespace('a', $NS_A);

            $get_img_rid = function (DOMElement $para) use ($xpath): ?string {
                $blips = $xpath->query('.//a:blip', $para);
                if ($blips && $blips->length > 0) {
                    $rid = $blips->item(0)->getAttributeNS(
                        'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                        'embed'
                    );
                    if (!$rid) $rid = $blips->item(0)->getAttributeNS(
                        'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                        'link'
                    );
                    return $rid ?: null;
                }
                return null;
            };

            $get_heading = function (DOMElement $para) use ($xpath): int {
                $ps = $xpath->query('w:pPr/w:pStyle', $para);
                if (!$ps || !$ps->length) return 0;
                $v = $ps->item(0)->getAttributeNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'val'
                );
                $map = [
                    'Heading1' => 1,
                    'Heading2' => 2,
                    'Heading3' => 3,
                    'Heading4' => 4,
                    'heading1' => 1,
                    'heading2' => 2,
                    'heading3' => 3,
                    'heading4' => 4,
                ];
                return $map[$v] ?? 0;
            };

            $is_list = function (DOMElement $para) use ($xpath): bool {
                return $xpath->query('w:pPr/w:numPr', $para)->length > 0;
            };

            $run_html = function (DOMElement $run) use ($xpath): string {
                $texts = $xpath->query('w:t', $run);
                if (!$texts || !$texts->length) return '';
                $text = '';
                foreach ($texts as $t) $text .= $t->nodeValue;
                if ($text === '') return '';
                $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $rPr = $xpath->query('w:rPr', $run)->item(0);
                if ($rPr) {
                    if ($xpath->query('w:b',      $rPr)->length) $text = "<strong>$text</strong>";
                    if ($xpath->query('w:i',      $rPr)->length) $text = "<em>$text</em>";
                    if ($xpath->query('w:u',      $rPr)->length) $text = "<u>$text</u>";
                    if ($xpath->query('w:strike', $rPr)->length) $text = "<s>$text</s>";
                }
                return $text;
            };

            $para_html = function (DOMElement $para) use ($xpath, $run_html, $hyperlink_rid_map): string {
                $html = '';
                foreach ($para->childNodes as $node) {
                    if (!($node instanceof DOMElement)) continue;
                    if ($node->localName === 'r') {
                        $html .= $run_html($node);
                    } elseif ($node->localName === 'hyperlink') {
                        $rid = $node->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                        if (!$rid) {
                            $rid = $node->getAttribute('r:id');
                        }
                        if (!$rid) {
                            $rid = $node->getAttribute('id');
                        }

                        $url = '#';
                        if ($rid && isset($hyperlink_rid_map[$rid])) {
                            $url = htmlspecialchars($hyperlink_rid_map[$rid], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }

                        $inner = '';
                        foreach ($node->childNodes as $ch) {
                            if ($ch instanceof DOMElement && $ch->localName === 'r') {
                                $inner .= $run_html($ch);
                            }
                        }
                        if ($inner) {
                            $html .= "<a href=\"$url\">$inner</a>";
                        }
                    }
                }
                return $html;
            };

            $lines   = [];
            $in_list = false;

            foreach ($xpath->query('//w:body/w:p') as $para) {
                $rid     = $get_img_rid($para);
                $heading = $get_heading($para);
                $is_li   = $is_list($para);

                if ($rid !== null) {
                    if ($in_list) {
                        $lines[] = '</ul>';
                        $in_list = false;
                    }
                    if (isset($rid_to_web[$rid])) {
                        $src = htmlspecialchars($rid_to_web[$rid], ENT_QUOTES);
                        $lines[] = "<img src=\"$src\" alt=\"\" loading=\"lazy\">";
                    }
                    $txt = $para_html($para);
                    if (trim(strip_tags($txt))) $lines[] = "<p>$txt</p>";
                    continue;
                }

                if ($heading > 0) {
                    if ($in_list) {
                        $lines[] = '</ul>';
                        $in_list = false;
                    }
                    if ($heading === 1) {
                        $txt = $para_html($para);
                        if (trim(strip_tags($txt)) && !$h1_text) {
                            $h1_text = strip_tags($txt);
                        }
                        continue;
                    }
                    $txt = $para_html($para);
                    if (trim(strip_tags($txt))) $lines[] = "<h$heading>$txt</h$heading>";
                    continue;
                }

                if ($is_li) {
                    if (!$in_list) {
                        $lines[] = '<ul>';
                        $in_list = true;
                    }
                    $txt = $para_html($para);
                    if (trim(strip_tags($txt))) $lines[] = "\t<li>$txt</li>";
                    continue;
                }

                if ($in_list) {
                    $lines[] = '</ul>';
                    $in_list = false;
                }
                $txt = $para_html($para);
                if (trim(strip_tags($txt))) $lines[] = "<p>$txt</p>";
            }

            if ($in_list) $lines[] = '</ul>';

            $result_html = implode("\n", $lines);
            $image_dir   = count($rid_to_web) ? $dir_web . '/' : '';
        }
    }
}