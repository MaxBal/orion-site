<?php

class OrionContractPdfFont {
    private $data;
    private $tables = [];
    private $cffData = '';
    private $postscriptName = 'OrionContractFont';
    private $customStrings = [];
    private $glyphToSid = [];
    private $segments = [];
    private $widths = [];
    private $unitsPerEm = 1000;
    private $bbox = [-500, -250, 1200, 1000];
    private $ascent = 800;
    private $descent = -200;
    private $usedByCode = [];
    private $codeByCodepoint = [];

    public function __construct($path, $fallbackName) {
        $this->data = @file_get_contents($path);
        if ($this->data === false || substr($this->data, 0, 4) !== 'OTTO') {
            throw new RuntimeException('Contract PDF font is unavailable: ' . basename($path));
        }
        $this->parseTables();
        $this->parseMetrics();
        $this->parseCmap();
        $this->parseCff($fallbackName);
    }

    private function u8($data, $offset) {
        return ord($data[$offset]);
    }

    private function u16($data, $offset) {
        return unpack('n', substr($data, $offset, 2))[1];
    }

    private function s16($data, $offset) {
        $value = $this->u16($data, $offset);
        return $value >= 0x8000 ? $value - 0x10000 : $value;
    }

    private function u32($data, $offset) {
        return unpack('N', substr($data, $offset, 4))[1];
    }

    private function parseTables() {
        $count = $this->u16($this->data, 4);
        for ($i = 0; $i < $count; $i++) {
            $entry = 12 + ($i * 16);
            $tag = substr($this->data, $entry, 4);
            $this->tables[$tag] = [
                'offset' => $this->u32($this->data, $entry + 8),
                'length' => $this->u32($this->data, $entry + 12),
            ];
        }
        foreach (['CFF ', 'cmap', 'head', 'hhea', 'hmtx', 'maxp'] as $required) {
            if (!isset($this->tables[$required])) {
                throw new RuntimeException('Unsupported contract PDF font table: ' . $required);
            }
        }
    }

    private function parseMetrics() {
        $head = $this->tables['head']['offset'];
        $this->unitsPerEm = max(1, $this->u16($this->data, $head + 18));
        $this->bbox = [
            $this->scaleMetric($this->s16($this->data, $head + 36)),
            $this->scaleMetric($this->s16($this->data, $head + 38)),
            $this->scaleMetric($this->s16($this->data, $head + 40)),
            $this->scaleMetric($this->s16($this->data, $head + 42)),
        ];
        $hhea = $this->tables['hhea']['offset'];
        $this->ascent = $this->scaleMetric($this->s16($this->data, $hhea + 4));
        $this->descent = $this->scaleMetric($this->s16($this->data, $hhea + 6));
        $metric_count = $this->u16($this->data, $hhea + 34);
        $glyph_count = $this->u16($this->data, $this->tables['maxp']['offset'] + 4);
        $hmtx = $this->tables['hmtx']['offset'];
        $last_width = 500;
        for ($gid = 0; $gid < $glyph_count; $gid++) {
            if ($gid < $metric_count) {
                $last_width = $this->scaleMetric($this->u16($this->data, $hmtx + ($gid * 4)));
            }
            $this->widths[$gid] = $last_width;
        }
    }

    private function scaleMetric($value) {
        return intval(round((intval($value) * 1000) / max(1, $this->unitsPerEm)));
    }

    private function parseCmap() {
        $base = $this->tables['cmap']['offset'];
        $count = $this->u16($this->data, $base + 2);
        $chosen = null;
        $chosen_priority = -1;
        for ($i = 0; $i < $count; $i++) {
            $entry = $base + 4 + ($i * 8);
            $platform = $this->u16($this->data, $entry);
            $encoding = $this->u16($this->data, $entry + 2);
            $offset = $base + $this->u32($this->data, $entry + 4);
            if ($this->u16($this->data, $offset) !== 4) {
                continue;
            }
            $priority = ($platform === 3 && $encoding === 1) ? 3 : ($platform === 0 ? 2 : 1);
            if ($priority > $chosen_priority) {
                $chosen = $offset;
                $chosen_priority = $priority;
            }
        }
        if ($chosen === null) {
            throw new RuntimeException('Contract PDF font has no Unicode cmap.');
        }
        $segment_count = intval($this->u16($this->data, $chosen + 6) / 2);
        $end_offset = $chosen + 14;
        $start_offset = $end_offset + ($segment_count * 2) + 2;
        $delta_offset = $start_offset + ($segment_count * 2);
        $range_offset = $delta_offset + ($segment_count * 2);
        for ($i = 0; $i < $segment_count; $i++) {
            $this->segments[] = [
                'start' => $this->u16($this->data, $start_offset + ($i * 2)),
                'end' => $this->u16($this->data, $end_offset + ($i * 2)),
                'delta' => $this->s16($this->data, $delta_offset + ($i * 2)),
                'range' => $this->u16($this->data, $range_offset + ($i * 2)),
                'range_pos' => $range_offset + ($i * 2),
            ];
        }
    }

    private function cffIndex($offset) {
        $count = $this->u16($this->cffData, $offset);
        $offset += 2;
        if ($count === 0) {
            return [[], $offset];
        }
        $off_size = $this->u8($this->cffData, $offset);
        $offset++;
        $offsets = [];
        for ($i = 0; $i <= $count; $i++) {
            $offsets[] = $this->readVariableUInt($this->cffData, $offset + ($i * $off_size), $off_size);
        }
        $base = $offset + (($count + 1) * $off_size);
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $start = $base + $offsets[$i] - 1;
            $length = $offsets[$i + 1] - $offsets[$i];
            $items[] = substr($this->cffData, $start, $length);
        }
        return [$items, $base + $offsets[$count] - 1];
    }

    private function readVariableUInt($data, $offset, $length) {
        $value = 0;
        for ($i = 0; $i < $length; $i++) {
            $value = ($value << 8) | $this->u8($data, $offset + $i);
        }
        return $value;
    }

    private function parseCffDict($bytes) {
        $stack = [];
        $dict = [];
        $length = strlen($bytes);
        for ($i = 0; $i < $length;) {
            $byte = ord($bytes[$i++]);
            if ($byte <= 21) {
                $operator = (string)$byte;
                if ($byte === 12) {
                    $operator .= '.' . ord($bytes[$i++]);
                }
                $dict[$operator] = $stack;
                $stack = [];
            } elseif ($byte >= 32 && $byte <= 246) {
                $stack[] = $byte - 139;
            } elseif ($byte >= 247 && $byte <= 250) {
                $stack[] = (($byte - 247) * 256) + ord($bytes[$i++]) + 108;
            } elseif ($byte >= 251 && $byte <= 254) {
                $stack[] = -(($byte - 251) * 256) - ord($bytes[$i++]) - 108;
            } elseif ($byte === 28) {
                $value = unpack('n', substr($bytes, $i, 2))[1];
                $stack[] = $value >= 0x8000 ? $value - 0x10000 : $value;
                $i += 2;
            } elseif ($byte === 29) {
                $value = unpack('N', substr($bytes, $i, 4))[1];
                if ($value >= 0x80000000) {
                    $value -= 0x100000000;
                }
                $stack[] = $value;
                $i += 4;
            } elseif ($byte === 30) {
                while ($i < $length) {
                    $pair = ord($bytes[$i++]);
                    if (($pair >> 4) === 15 || ($pair & 15) === 15) {
                        break;
                    }
                }
                $stack[] = 0;
            }
        }
        return $dict;
    }

    private function parseCff($fallbackName) {
        $table = $this->tables['CFF '];
        $this->cffData = substr($this->data, $table['offset'], $table['length']);
        $header_size = $this->u8($this->cffData, 2);
        [$names, $offset] = $this->cffIndex($header_size);
        [$top_dicts, $offset] = $this->cffIndex($offset);
        [$this->customStrings, $offset] = $this->cffIndex($offset);
        [, $offset] = $this->cffIndex($offset);
        $this->postscriptName = !empty($names[0]) ? preg_replace('/[^A-Za-z0-9_.-]/', '', $names[0]) : $fallbackName;
        $top = $this->parseCffDict($top_dicts[0] ?? '');
        $charset_offset = intval($top['15'][0] ?? 0);
        $charstrings_offset = intval($top['17'][0] ?? 0);
        if ($charset_offset <= 2 || $charstrings_offset <= 0) {
            throw new RuntimeException('Contract PDF font uses an unsupported CFF charset.');
        }
        [$charstrings] = $this->cffIndex($charstrings_offset);
        $glyph_count = count($charstrings);
        $this->glyphToSid = [0];
        $cursor = $charset_offset;
        $format = $this->u8($this->cffData, $cursor++);
        if ($format === 0) {
            while (count($this->glyphToSid) < $glyph_count) {
                $this->glyphToSid[] = $this->u16($this->cffData, $cursor);
                $cursor += 2;
            }
        } elseif ($format === 1 || $format === 2) {
            while (count($this->glyphToSid) < $glyph_count) {
                $first = $this->u16($this->cffData, $cursor);
                $cursor += 2;
                if ($format === 1) {
                    $left = $this->u8($this->cffData, $cursor++);
                } else {
                    $left = $this->u16($this->cffData, $cursor);
                    $cursor += 2;
                }
                for ($i = 0; $i <= $left && count($this->glyphToSid) < $glyph_count; $i++) {
                    $this->glyphToSid[] = $first + $i;
                }
            }
        } else {
            throw new RuntimeException('Unsupported CFF charset format.');
        }
    }

    private function glyphForCodepoint($codepoint) {
        foreach ($this->segments as $segment) {
            if ($codepoint < $segment['start'] || $codepoint > $segment['end']) {
                continue;
            }
            if ($segment['range'] === 0) {
                return ($codepoint + $segment['delta']) & 0xFFFF;
            }
            $address = $segment['range_pos'] + $segment['range'] + (2 * ($codepoint - $segment['start']));
            $glyph = $this->u16($this->data, $address);
            return $glyph === 0 ? 0 : (($glyph + $segment['delta']) & 0xFFFF);
        }
        return 0;
    }

    private function standardGlyphNames() {
        return array_merge(
            ['.notdef', 'space', 'exclam', 'quotedbl', 'numbersign', 'dollar', 'percent', 'ampersand', 'quoteright', 'parenleft', 'parenright', 'asterisk', 'plus', 'comma', 'hyphen', 'period', 'slash', 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'colon', 'semicolon', 'less', 'equal', 'greater', 'question', 'at'],
            range('A', 'Z'),
            ['bracketleft', 'backslash', 'bracketright', 'asciicircum', 'underscore', 'quoteleft'],
            range('a', 'z'),
            ['braceleft', 'bar', 'braceright', 'asciitilde']
        );
    }

    private function glyphName($glyph, $codepoint) {
        $sid = intval($this->glyphToSid[$glyph] ?? 0);
        if ($sid >= 391 && isset($this->customStrings[$sid - 391])) {
            return preg_replace('/[^A-Za-z0-9_.-]/', '', $this->customStrings[$sid - 391]);
        }
        $standard = $this->standardGlyphNames();
        if (isset($standard[$sid])) {
            return $standard[$sid];
        }
        return $this->glyphName($this->glyphForCodepoint(63), 63);
    }

    private function chars($text) {
        return preg_split('//u', (string)$text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    public function encode($text) {
        $encoded = '';
        foreach ($this->chars($text) as $character) {
            $codepoint = function_exists('mb_ord') ? mb_ord($character, 'UTF-8') : ord($character);
            $glyph = $this->glyphForCodepoint($codepoint);
            if ($glyph === 0 && $codepoint !== 0) {
                $codepoint = 63;
                $glyph = $this->glyphForCodepoint($codepoint);
            }
            if (!isset($this->codeByCodepoint[$codepoint])) {
                $code = 32 + count($this->usedByCode);
                if ($code > 255) {
                    throw new RuntimeException('Contract PDF contains too many distinct glyphs.');
                }
                $this->codeByCodepoint[$codepoint] = $code;
                $this->usedByCode[$code] = [
                    'codepoint' => $codepoint,
                    'glyph' => $glyph,
                    'name' => $this->glyphName($glyph, $codepoint),
                    'width' => intval($this->widths[$glyph] ?? 500),
                ];
            }
            $encoded .= chr($this->codeByCodepoint[$codepoint]);
        }
        return strtoupper(bin2hex($encoded));
    }

    public function textWidth($text, $size) {
        $width = 0;
        foreach ($this->chars($text) as $character) {
            $codepoint = function_exists('mb_ord') ? mb_ord($character, 'UTF-8') : ord($character);
            $glyph = $this->glyphForCodepoint($codepoint);
            $width += intval($this->widths[$glyph] ?? 500);
        }
        return ($width / 1000) * floatval($size);
    }

    private function unicodeHex($codepoint) {
        $codepoint = intval($codepoint);
        if ($codepoint <= 0xFFFF) {
            return sprintf('%04X', $codepoint);
        }
        $codepoint -= 0x10000;
        return sprintf('%04X%04X', 0xD800 + ($codepoint >> 10), 0xDC00 + ($codepoint & 0x3FF));
    }

    private function toUnicodeCmap() {
        $entries = [];
        foreach ($this->usedByCode as $code => $meta) {
            $entries[] = sprintf('<%02X> <%s>', $code, $this->unicodeHex($meta['codepoint']));
        }
        $blocks = '';
        foreach (array_chunk($entries, 100) as $chunk) {
            $blocks .= count($chunk) . " beginbfchar\n" . implode("\n", $chunk) . "\nendbfchar\n";
        }
        return "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n"
            . "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n"
            . "/CMapName /OrionContractUCS def\n/CMapType 2 def\n"
            . "1 begincodespacerange\n<00> <FF>\nendcodespacerange\n"
            . $blocks
            . "endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend";
    }

    public function addToPdf($pdf) {
        if (empty($this->usedByCode)) {
            $this->encode('?');
        }
        ksort($this->usedByCode);
        $font_file = $pdf->addStream($this->cffData, '/Subtype /Type1C');
        $bbox = implode(' ', array_map('intval', $this->bbox));
        $descriptor = $pdf->add("<< /Type /FontDescriptor /FontName /{$this->postscriptName} /Flags 32 /FontBBox [{$bbox}] /ItalicAngle 0 /Ascent {$this->ascent} /Descent {$this->descent} /CapHeight {$this->ascent} /StemV 80 /FontFile3 {$font_file} 0 R >>");
        $to_unicode = $pdf->addStream($this->toUnicodeCmap());
        $first = min(array_keys($this->usedByCode));
        $last = max(array_keys($this->usedByCode));
        $widths = [];
        $differences = [];
        for ($code = $first; $code <= $last; $code++) {
            $meta = $this->usedByCode[$code] ?? ['width' => 500, 'name' => '.notdef'];
            $widths[] = intval($meta['width']);
            $differences[] = '/' . $meta['name'];
        }
        return $pdf->add("<< /Type /Font /Subtype /Type1 /BaseFont /{$this->postscriptName} /FirstChar {$first} /LastChar {$last} /Widths [" . implode(' ', $widths) . "] /FontDescriptor {$descriptor} 0 R /Encoding << /Type /Encoding /Differences [{$first} " . implode(' ', $differences) . "] >> /ToUnicode {$to_unicode} 0 R >>");
    }
}

class OrionContractPdfBuilder {
    private $objects = [];

    public function reserve() {
        $this->objects[] = null;
        return count($this->objects);
    }

    public function set($id, $content) {
        $this->objects[intval($id) - 1] = (string)$content;
    }

    public function add($content) {
        $id = $this->reserve();
        $this->set($id, $content);
        return $id;
    }

    public function addStream($data, $extra_dictionary = '') {
        $compressed = gzcompress((string)$data, 9);
        return $this->add('<< /Length ' . strlen($compressed) . ' /Filter /FlateDecode ' . $extra_dictionary . ">>\nstream\n" . $compressed . "\nendstream");
    }

    public function build($root_id, $info_id = null) {
        $output = "%PDF-1.7\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($this->objects as $index => $object) {
            if ($object === null) {
                throw new RuntimeException('Unresolved PDF object.');
            }
            $offsets[] = strlen($output);
            $number = $index + 1;
            $output .= "{$number} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($output);
        $output .= 'xref' . "\n0 " . (count($this->objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i < count($offsets); $i++) {
            $output .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $trailer = '<< /Size ' . (count($this->objects) + 1) . " /Root {$root_id} 0 R";
        if ($info_id) {
            $trailer .= " /Info {$info_id} 0 R";
        }
        $output .= "trailer\n{$trailer} >>\nstartxref\n{$xref}\n%%EOF\n";
        return $output;
    }
}

class OrionContractPdfCanvas {
    private $regular;
    private $bold;
    private $pages = [];
    private $page = -1;

    public function __construct($regular, $bold) {
        $this->regular = $regular;
        $this->bold = $bold;
    }

    public function newPage() {
        $this->pages[] = '';
        $this->page = count($this->pages) - 1;
    }

    private function out($command) {
        $this->pages[$this->page] .= $command . "\n";
    }

    private function color($hex) {
        $hex = ltrim((string)$hex, '#');
        if (strlen($hex) !== 6) {
            $hex = '111111';
        }
        return implode(' ', [
            round(hexdec(substr($hex, 0, 2)) / 255, 4),
            round(hexdec(substr($hex, 2, 2)) / 255, 4),
            round(hexdec(substr($hex, 4, 2)) / 255, 4),
        ]);
    }

    public function fillRect($x, $y, $width, $height, $color) {
        $this->out($this->color($color) . ' rg ' . $this->n($x) . ' ' . $this->n($y) . ' ' . $this->n($width) . ' ' . $this->n($height) . ' re f');
    }

    public function strokeRect($x, $y, $width, $height, $color, $line_width = 1) {
        $this->out($this->color($color) . ' RG ' . $this->n($line_width) . ' w ' . $this->n($x) . ' ' . $this->n($y) . ' ' . $this->n($width) . ' ' . $this->n($height) . ' re S');
    }

    public function line($x1, $y1, $x2, $y2, $color, $line_width = 1) {
        $this->out($this->color($color) . ' RG ' . $this->n($line_width) . ' w ' . $this->n($x1) . ' ' . $this->n($y1) . ' m ' . $this->n($x2) . ' ' . $this->n($y2) . ' l S');
    }

    private function n($number) {
        return rtrim(rtrim(number_format(floatval($number), 3, '.', ''), '0'), '.');
    }

    private function font($key) {
        return $key === 'B' ? $this->bold : $this->regular;
    }

    public function text($x, $y, $text, $size = 10, $font_key = 'R', $color = '17191f', $align = 'left') {
        $font = $this->font($font_key);
        if ($align !== 'left') {
            $width = $font->textWidth($text, $size);
            $x -= $align === 'center' ? ($width / 2) : $width;
        }
        $encoded = $font->encode($text);
        $resource = $font_key === 'B' ? 'FB' : 'FR';
        $this->out('BT /' . $resource . ' ' . $this->n($size) . ' Tf ' . $this->color($color) . ' rg 1 0 0 1 ' . $this->n($x) . ' ' . $this->n($y) . ' Tm <' . $encoded . '> Tj ET');
    }

    public function wrappedLines($text, $size, $font_key, $max_width) {
        $font = $this->font($font_key);
        $paragraphs = preg_split('/\R/u', trim((string)$text)) ?: [];
        $lines = [];
        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $current = '';
            foreach ($words as $word) {
                $candidate = $current === '' ? $word : $current . ' ' . $word;
                if ($current !== '' && $font->textWidth($candidate, $size) > $max_width) {
                    $lines[] = $current;
                    $current = $word;
                } else {
                    $current = $candidate;
                }
            }
            if ($current !== '') {
                $lines[] = $current;
            }
        }
        return $lines;
    }

    public function paragraph($x, $y, $text, $width, $size = 9.5, $leading = 14, $color = '454851') {
        foreach ($this->wrappedLines($text, $size, 'R', $width) as $line) {
            $this->text($x, $y, $line, $size, 'R', $color);
            $y -= $leading;
        }
        return $y;
    }

    public function section($number, $title, $body, $y) {
        $this->fillRect(44, $y - 4, 24, 24, 'e0a24a');
        $this->text(56, $y + 3, str_pad((string)$number, 2, '0', STR_PAD_LEFT), 8, 'B', '17191f', 'center');
        $this->text(79, $y + 2, $title, 12.5, 'B', '17191f');
        $y -= 20;
        $y = $this->paragraph(79, $y, $body, 468, 9.2, 13.5, '4b4e57');
        return $y - 16;
    }

    public function pages() {
        return $this->pages;
    }
}

function contract_pdf_date($value, $lang) {
    $formatted = contract_format_kyiv_datetime(
        $value,
        $lang === 'en' ? 'Y-m-d H:i' : 'd.m.Y H:i'
    );
    if ($formatted === '-') {
        return $formatted;
    }
    return $formatted . ($lang === 'uk' ? ' за Києвом' : ($lang === 'ru' ? ' по Киеву' : ' Kyiv time'));
}

function contract_pdf_document($contract, $lang) {
    $lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'uk';
    $role = contract_role_label($contract['staff_role'] ?? '', $lang);
    $username = trim((string)($contract['username'] ?? '')) ?: ('Account #' . intval($contract['account_id'] ?? 0));
    $signer = trim((string)($contract['signer_name'] ?? '')) ?: $username;
    $approver = trim((string)($contract['offered_by_name'] ?? '')) ?: 'Project Orion';
    $number = (string)($contract['contract_number'] ?? 'ORI-UNKNOWN');
    $hash = strtoupper(substr((string)($contract['signature_hash'] ?? ''), 0, 16));
    $status = contract_status_label($contract['status'] ?? '', $lang);

    $documents = [
        'uk' => [
            'language' => 'УКРАЇНСЬКА ВЕРСІЯ',
            'title' => 'КОНТРАКТ УЧАСНИКА КОМАНДИ',
            'subtitle' => 'Гарантована семиденна роль у команді Project Orion',
            'facts' => ['УЧАСНИК' => $username, 'РОЛЬ' => $role, 'СТРОК' => '7 календарних днів'],
            'intro' => "{$signer} самостійно обрав і подав цей тип контракту. Глава проєкту ({$approver}) прийняв його; контракт набрав чинності у вказаний час початку та діє сім календарних днів.",
            'sections' => [
                ['Предмет контракту', "Проєкт надає Учаснику роль {$role} та пов'язані з нею дозволи. Доступ використовується виключно для завдань Проєкту, у межах наданих повноважень та внутрішніх правил."],
                ['Обов’язки учасника', 'Учасник добросовісно виконує погоджені завдання, поважає користувачів і команду, не зловживає правами, не передає доступ третім особам та повідомляє адміністратора про помилки або інциденти безпеки.'],
                ['Дані та конфіденційність', 'Службова інформація, персональні дані користувачів, внутрішні матеріали та технічні доступи не можуть публікуватися або використовуватися поза роботою над Проєктом без окремого дозволу.'],
                ['Строк і право на поновлення', 'Контракт діє 7 календарних днів від часу прийняття главою Проєкту. День початку є першим календарним днем. Із початку п’ятого календарного дня Учасник може подати одну заявку на поновлення цієї самої ролі. Раніше система заявку не приймає. Неприйнята заявка не подовжує чинний строк.'],
                ['Рішення щодо поновлення', 'Глава Проєкту приймає або відхиляє заявку. Прийняте до завершення чинного строку поновлення створює пов’язаний контракт: він починається точно після завершення поточного та діє нові 7 календарних днів. Якщо рішення прийнято після завершення, новий строк починається в час рішення. Новий контракт отримує окремий номер і PDF-запис.'],
                ['Активація, розірвання та час', 'Початкове прийняття одразу активує обрану роль і дозволи. Адміністрація зобов’язується не розривати контракт без обґрунтованої та зафіксованої причини. Глава може достроково розірвати активний або скасувати запланований контракт лише за наявності такої причини; роль знімається негайно, а пов’язане заплановане поновлення скасовується. Причина, дата та час розірвання публікуються в реєстрі. Усі дати й час указуються за київським часом. Записи Project Orion не є кваліфікованим електронним підписом.'],
            ],
            'signature_title' => 'ЗАПИС ПРО ПРИЙНЯТТЯ',
            'signed_by' => 'Учасник', 'signed_at' => 'Прийнято главою', 'starts' => 'Початок', 'expires' => 'Завершення', 'status' => 'Статус', 'verify' => 'Код рішення',
            'termination_record' => 'Зафіксована публічна підстава дострокового розірвання',
            'footer' => 'Публічний реєстр контрактів Project Orion - усі часи за Києвом',
        ],
        'ru' => [
            'language' => 'РУССКАЯ ВЕРСИЯ',
            'title' => 'КОНТРАКТ УЧАСТНИКА КОМАНДЫ',
            'subtitle' => 'Гарантированная семидневная роль в команде Project Orion',
            'facts' => ['УЧАСТНИК' => $username, 'РОЛЬ' => $role, 'СРОК' => '7 календарных дней'],
            'intro' => "{$signer} самостоятельно выбрал и подал этот тип контракта. Глава проекта ({$approver}) принял его; контракт вступил в силу в указанное время начала и действует семь календарных дней.",
            'sections' => [
                ['Предмет контракта', "Проект предоставляет Участнику роль {$role} и связанные с ней разрешения. Доступ используется только для задач Проекта, в пределах выданных полномочий и внутренних правил."],
                ['Обязанности участника', 'Участник добросовестно выполняет согласованные задачи, уважает пользователей и команду, не злоупотребляет правами, не передаёт доступ третьим лицам и сообщает администратору об ошибках или инцидентах безопасности.'],
                ['Данные и конфиденциальность', 'Служебная информация, персональные данные пользователей, внутренние материалы и технические доступы не могут публиковаться или использоваться вне работы над Проектом без отдельного разрешения.'],
                ['Срок и право на продление', 'Контракт действует 7 календарных дней с момента принятия главой Проекта. День начала считается первым календарным днём. С начала пятого календарного дня Участник может подать одну заявку на продление той же роли. Раньше система заявку не принимает. Непринятая заявка не продлевает текущий срок.'],
                ['Решение о продлении', 'Глава Проекта принимает или отклоняет заявку. Принятое до окончания текущего срока продление создаёт связанный контракт: он начинается точно после завершения текущего и действует новые 7 календарных дней. Если решение принято после завершения, новый срок начинается во время решения. Новый контракт получает отдельный номер и PDF-запись.'],
                ['Активация, расторжение и время', 'Первичное принятие сразу активирует выбранную роль и разрешения. Администрация обязуется не расторгать контракт без обоснованной и зафиксированной причины. Глава может досрочно расторгнуть активный или отменить запланированный контракт только при наличии такой причины; роль снимается немедленно, а связанное запланированное продление отменяется. Причина, дата и время расторжения публикуются в реестре. Все даты и время указываются по киевскому времени. Записи Project Orion не являются квалифицированной электронной подписью.'],
            ],
            'signature_title' => 'ЗАПИСЬ О ПРИНЯТИИ',
            'signed_by' => 'Участник', 'signed_at' => 'Принято главой', 'starts' => 'Начало', 'expires' => 'Завершение', 'status' => 'Статус', 'verify' => 'Код решения',
            'termination_record' => 'Зафиксированное публичное основание досрочного расторжения',
            'footer' => 'Публичный реестр контрактов Project Orion - всё время по Киеву',
        ],
        'en' => [
            'language' => 'ENGLISH VERSION',
            'title' => 'TEAM MEMBER AGREEMENT',
            'subtitle' => 'Guaranteed seven-day role on the Project Orion team',
            'facts' => ['MEMBER' => $username, 'ROLE' => $role, 'TERM' => '7 calendar days'],
            'intro' => "{$signer} selected and submitted this contract type. The Project lead ({$approver}) accepted it; the contract took effect at the stated start time and continues for seven calendar days.",
            'sections' => [
                ['Scope', "The Project grants the Member the {$role} role and its associated permissions. Access may be used only for Project tasks, within the granted authority and internal rules."],
                ['Member duties', 'The Member will perform agreed tasks in good faith, treat users and team members respectfully, avoid misuse of privileges, keep access credentials private, and report errors or security incidents to an administrator.'],
                ['Data and confidentiality', 'Operational information, user personal data, internal materials, and technical access details must not be published or used outside Project work without separate authorization.'],
                ['Term and renewal right', 'The contract runs for 7 calendar days from acceptance by the Project lead. The start day counts as calendar day one. From the start of calendar day five, the Member may submit one renewal application for the same role. The system rejects earlier requests. A pending application does not extend the current term.'],
                ['Renewal decision', 'The Project lead accepts or rejects the application. A renewal accepted before the current term ends creates a linked contract that starts exactly when the current contract expires and runs for another 7 calendar days. If accepted after expiry, the new term starts at the decision time. Every renewed contract receives a separate number and PDF record.'],
                ['Activation, termination, and time', 'Initial acceptance immediately activates the selected role and permissions. The Administration undertakes not to terminate a contract without a justified and recorded reason. The lead may terminate an active contract early or cancel a scheduled contract only when such a reason exists; the role is removed immediately and any linked scheduled renewal is cancelled. The registry publishes the termination reason, date, and time. All dates and times use Kyiv time. Project Orion records are not qualified electronic signatures.'],
            ],
            'signature_title' => 'ACCEPTANCE RECORD',
            'signed_by' => 'Member', 'signed_at' => 'Accepted by lead', 'starts' => 'Starts', 'expires' => 'Expires', 'status' => 'Status', 'verify' => 'Decision code',
            'termination_record' => 'Recorded public basis for early termination',
            'footer' => 'Project Orion public contract registry - all times use Kyiv time',
        ],
    ];
    $document = $documents[$lang];
    if (($contract['status'] ?? '') === 'terminated' && trim((string)($contract['termination_reason'] ?? '')) !== '') {
        $termination_time = contract_pdf_date($contract['terminated_at'] ?? '', $lang);
        $document['sections'][5][1] .= ' ' . $document['termination_record'] . ': '
            . trim((string)$contract['termination_reason'])
            . ($termination_time !== '-' ? ' (' . $termination_time . ')' : '');
    }
    $document['number'] = $number;
    $document['signer'] = $signer;
    $document['signed_value'] = contract_pdf_date($contract['signed_at'] ?? '', $lang);
    $document['starts_value'] = contract_pdf_date($contract['starts_at'] ?? '', $lang);
    $document['expires_value'] = contract_pdf_date($contract['expires_at'] ?? '', $lang);
    $document['status_value'] = $status;
    $document['verify_value'] = $hash ?: 'UNAVAILABLE';
    return $document;
}

function contract_pdf_render($contract, $lang = 'uk') {
    $document = contract_pdf_document($contract, $lang);
    $regular = new OrionContractPdfFont(__DIR__ . '/../fonts/e-Ukraine-Regular.otf', 'OrionRegular');
    $bold = new OrionContractPdfFont(__DIR__ . '/../fonts/e-UkraineHead-Bold.otf', 'OrionBold');
    $canvas = new OrionContractPdfCanvas($regular, $bold);

    $canvas->newPage();
    $canvas->fillRect(0, 680, 595.28, 161.89, '17191f');
    $canvas->fillRect(0, 680, 9, 161.89, 'e0a24a');
    $canvas->text(44, 808, 'PROJECT ORION / CONTRACTS', 8.5, 'B', 'e0a24a');
    $canvas->text(551, 808, '01 / 02', 8, 'R', 'aeb2bd', 'right');
    $canvas->text(44, 764, $document['title'], 22, 'B', 'ffffff');
    $canvas->text(44, 739, $document['subtitle'], 10.5, 'R', 'c9ccd3');
    $canvas->fillRect(44, 700, 126, 22, 'e0a24a');
    $canvas->text(107, 707, $document['language'], 7.2, 'B', '17191f', 'center');
    $canvas->text(551, 707, $document['number'], 10, 'B', 'ffffff', 'right');

    $fact_x = 44;
    $fact_width = 161;
    $fact_index = 0;
    foreach ($document['facts'] as $label => $value) {
        $x = $fact_x + ($fact_index * 173);
        $canvas->fillRect($x, 606, $fact_width, 50, 'f5f2ec');
        $canvas->strokeRect($x, 606, $fact_width, 50, 'ded9cf', .6);
        $canvas->text($x + 12, 638, $label, 6.8, 'B', '9b6b28');
        $canvas->text($x + 12, 619, $value, 10.2, 'B', '17191f');
        $fact_index++;
    }
    $canvas->text(44, 580, 'AGREEMENT / ' . $document['number'], 7.2, 'B', '9b6b28');
    $y = $canvas->paragraph(44, 558, $document['intro'], 507, 9.6, 14.5, '454851') - 15;
    for ($i = 0; $i < 3; $i++) {
        $section = $document['sections'][$i];
        $y = $canvas->section($i + 1, $section[0], $section[1], $y);
    }
    $canvas->line(44, 45, 551, 45, 'ded9cf', .6);
    $canvas->text(44, 27, $document['footer'], 7, 'R', '777b84');
    $canvas->text(551, 27, $document['number'], 7, 'B', '777b84', 'right');

    $canvas->newPage();
    $canvas->fillRect(0, 770, 595.28, 71.89, '17191f');
    $canvas->fillRect(0, 770, 9, 71.89, 'e0a24a');
    $canvas->text(44, 807, 'PROJECT ORION', 9, 'B', 'e0a24a');
    $canvas->text(44, 786, $document['number'], 8.5, 'R', 'd5d7dc');
    $canvas->text(551, 798, '02 / 02', 8, 'R', 'aeb2bd', 'right');
    $y = 736;
    for ($i = 3; $i < 6; $i++) {
        $section = $document['sections'][$i];
        $y = $canvas->section($i + 1, $section[0], $section[1], $y);
    }

    $card_top = min($y + 5, 360);
    $card_bottom = 90;
    $canvas->fillRect(44, $card_bottom, 507, $card_top - $card_bottom, 'f5f2ec');
    $canvas->strokeRect(44, $card_bottom, 507, $card_top - $card_bottom, 'd8d2c7', .8);
    $canvas->fillRect(44, $card_top - 42, 507, 42, 'e0a24a');
    $canvas->text(60, $card_top - 26, $document['signature_title'], 10, 'B', '17191f');
    $rows = [
        [$document['signed_by'], $document['signer']],
        [$document['signed_at'], $document['signed_value']],
        [$document['starts'], $document['starts_value']],
        [$document['expires'], $document['expires_value']],
        [$document['status'], $document['status_value']],
        [$document['verify'], $document['verify_value']],
    ];
    $row_y = $card_top - 68;
    foreach ($rows as $index => $row) {
        $canvas->text(60, $row_y, $row[0], 7.4, 'B', '8e6a37');
        $canvas->text(195, $row_y, $row[1], 9.2, $index === 5 ? 'B' : 'R', '17191f');
        if ($index < count($rows) - 1) {
            $canvas->line(60, $row_y - 11, 535, $row_y - 11, 'ded9cf', .5);
        }
        $row_y -= 29;
    }
    $canvas->line(44, 45, 551, 45, 'ded9cf', .6);
    $canvas->text(44, 27, $document['footer'], 7, 'R', '777b84');
    $canvas->text(551, 27, $document['number'], 7, 'B', '777b84', 'right');

    $pdf = new OrionContractPdfBuilder();
    $regular_ref = $regular->addToPdf($pdf);
    $bold_ref = $bold->addToPdf($pdf);
    $pages_ref = $pdf->reserve();
    $page_refs = [];
    foreach ($canvas->pages() as $content) {
        $content_ref = $pdf->addStream($content);
        $page_refs[] = $pdf->add("<< /Type /Page /Parent {$pages_ref} 0 R /MediaBox [0 0 595.28 841.89] /Resources << /ProcSet [/PDF /Text] /Font << /FR {$regular_ref} 0 R /FB {$bold_ref} 0 R >> >> /Contents {$content_ref} 0 R >>");
    }
    $kids = implode(' ', array_map(function($ref) { return $ref . ' 0 R'; }, $page_refs));
    $pdf->set($pages_ref, '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($page_refs) . ' >>');
    $catalog = $pdf->add("<< /Type /Catalog /Pages {$pages_ref} 0 R /PageLayout /OneColumn >>");
    $info = $pdf->add('<< /Title (Project Orion Staff Contract) /Author (Project Orion) /Subject (Public staff contract) /Creator (Project Orion contract system) >>');
    return $pdf->build($catalog, $info);
}
