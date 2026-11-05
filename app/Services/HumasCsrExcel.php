<?php

namespace App\Services;

use App\Models\HumasCsr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use ZipArchive;

class HumasCsrExcel
{
    public function make(Collection $records, int $year, ?int $month = null): string
    {
        $path = tempnam(sys_get_temp_dir(), 'humas-csr-');
        if ($path === false) {
            throw new \RuntimeException('Tidak dapat membuat file Excel sementara.');
        }
        $zip = new ZipArchive;
        try {
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Tidak dapat membuat arsip Excel.');
            }
            $months = $month ? [$month] : range(1, 12);
            $sheets = '';
            $relationships = '';
            $overrides = '';
            foreach ($months as $index => $number) {
                $id = $index + 1;
                $title = mb_strtoupper(Carbon::create($year, $number, 1)->locale('id')->translatedFormat('F'));
                $rows = $records->filter(fn ($record) => $record->tanggal_proses->year === $year && $record->tanggal_proses->month === $number)->values();
                $zip->addFromString('xl/worksheets/sheet'.$id.'.xml', $this->sheet($rows, $title));
                $sheets .= '<sheet name="'.$this->xml($title).'" sheetId="'.$id.'" r:id="rId'.$id.'"/>';
                $relationships .=
                    '<Relationship Id="rId'.
                    $id.
                    '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.
                    $id.
                    '.xml"/>';
                $overrides .=
                    '<Override PartName="/xl/worksheets/sheet'.
                    $id.
                    '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            }
            $relationships .= '<Relationship Id="theme" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>';
            $relationships .=
                '<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.
                    '<Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'.$overrides.
                    '</Types>',
            );
            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="workbook" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            );
            $zip->addFromString(
                'xl/workbook.xml',
                '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.
                    $sheets.
                    '</sheets><calcPr calcId="191029" fullCalcOnLoad="1"/></workbook>',
            );
            $zip->addFromString(
                'xl/_rels/workbook.xml.rels',
                '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
                    $relationships.
                    '</Relationships>',
            );
            $zip->addFromString('xl/styles.xml', $this->styles());
            $zip->addFromString('xl/theme/theme1.xml', file_get_contents(resource_path('templates/humas-csr/theme.xml')));
            if (! $zip->close()) {
                throw new \RuntimeException('File Excel gagal diselesaikan.');
            }

            return $path;
        } catch (\Throwable $error) {
            try {
                $zip->close();
            } catch (\Throwable $ignored) {
                /* Zip may not have opened. */
            }
            @unlink($path);
            throw $error;
        }
    }

    private function xml($value): string
    {
        return htmlspecialchars(
            preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $value),
            ENT_XML1 | ENT_QUOTES,
            'UTF-8',
        );
    }

    private function text(string $cell, $value, int $style = 2): string
    {
        // User input is always an inline string, including values beginning with =, +, -, or @.
        return '<c r="'.
            $cell.
            '" s="'.
            $style.
            '" t="inlineStr"><is><t xml:space="preserve">'.
            $this->xml($value).
            '</t></is></c>';
    }

    private function template(): array
    {
        return json_decode(file_get_contents(resource_path('templates/humas-csr/template.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function attributes(array $attributes): string
    {
        $xml = '';
        foreach ($attributes as $name => $value) {
            $xml .= ' '.$name.'="'.$this->xml($value).'"';
        }

        return $xml;
    }

    private function sheet(Collection $records, string $month): string
    {
        $template = $this->template();
        $rows = '';
        foreach ($template['headers'] as $header) {
            $rows .= '<row'.$this->attributes($header['attributes']).'>';
            foreach ($header['cells'] as $cell) {
                $rows .= $this->text($cell['ref'], str_replace('{MONTH}', $month, $cell['value']), $cell['style']);
            }
            $rows .= '</row>';
        }
        $row = 5;
        foreach ($records as $index => $record) {
            $values = [
                $index + 1, $record->pemohon, $record->nomor_surat ?: '-', $record->nama_kegiatan,
                null, $record->keluar ?: '-', $record->nomor_voucher ?: '-', $record->status_label,
                $record->kategori?->nama ?? '-', $record->luaran_label, $record->nominal, $record->keterangan ?: '',
            ];
            $rows .= '<row r="'.$row.'" ht="'.$this->rowHeight($values, $template).'" customHeight="1">';
            foreach ($values as $column => $value) {
                $letter = chr(65 + $column);
                $cell = $letter.$row;
                $style = $template['data_styles'][$letter];
                if ($column === 7 && array_key_exists($record->status, HumasCsr::STATUSES)) {
                    $style = $template['base_style_count'] + array_search($record->status, array_keys(HumasCsr::STATUSES), true);
                } elseif ($column === 9 && array_key_exists($record->luaran, HumasCsr::LUARAN)) {
                    $style = $template['base_style_count'] + count(HumasCsr::STATUSES) + array_search($record->luaran, array_keys(HumasCsr::LUARAN), true);
                }
                if ($column === 4) {
                    $serial = (int) Carbon::create(1899, 12, 30)->startOfDay()->diffInDays($record->tanggal_proses->copy()->startOfDay());
                    if ($record->tanggal_proses->lt(Carbon::create(1900, 3, 1))) {
                        $serial--;
                    }
                    $rows .= '<c r="'.$cell.'" s="'.$style.'"><v>'.$serial.'</v></c>';
                } elseif (in_array($column, [0, 10], true)) {
                    $rows .= '<c r="'.$cell.'" s="'.$style.'"><v>'.$this->xml($value).'</v></c>';
                } else {
                    $rows .= $this->text($cell, $value, $style);
                }
            }
            $rows .= '</row>';
            $row++;
        }
        if ($records->isEmpty()) {
            $rows .= '<row r="5" ht="'.$template['minimum_data_row_height'].'" customHeight="1">';
            foreach ($template['data_styles'] as $letter => $style) {
                $rows .= $this->text($letter.'5', '', $style);
            }
            $rows .= '</row>';
        }
        $last = max(5, $row - 1);
        $merges = '';
        foreach ($template['merges'] as $merge) {
            $merges .= '<mergeCell ref="'.$merge.'"/>';
        }
        $cols = '';
        foreach ($template['columns'] as $column) {
            $cols .= '<col'.$this->attributes($column).'/>';
        }
        $validationLast = max(131, $last);
        $validations = $this->validation('H5:H'.$validationLast, array_values(HumasCsr::STATUSES));
        $validations .= $this->validation('J5:J'.$validationLast, array_values(HumasCsr::LUARAN));
        $validations .= $this->validation('I5:I'.$validationLast, HumasCsr::CATEGORIES);

        return '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:L'.$last.'"/><sheetViews><sheetView workbookViewId="0" showGridLines="0"/></sheetViews><sheetFormatPr'
            .$this->attributes($template['sheet_format']).'/><cols>'.$cols.'</cols><sheetData>'.$rows
            .'</sheetData><mergeCells count="'.count($template['merges']).'">'.$merges.'</mergeCells>'
            .$this->conditionalFormats($validationLast).'<dataValidations count="3">'.$validations.'</dataValidations>'
            .'<pageMargins left="0.25" right="0.25" top="0.4" bottom="0.4" header="0.2" footer="0.2"/>'
            .'<pageSetup paperSize="8" orientation="landscape" fitToWidth="1" fitToHeight="0"/></worksheet>';
    }

    private function rowHeight(array $values, array $template): float
    {
        $height = $template['minimum_data_row_height'];
        foreach ($values as $index => $value) {
            $width = (float) $template['sheet_format']['defaultColWidth'];
            foreach ($template['columns'] as $column) {
                if ($index + 1 >= $column['min'] && $index + 1 <= $column['max']) {
                    $width = (float) $column['width'];
                }
            }
            $lines = 0;
            foreach (preg_split('/\R/u', (string) $value) as $line) {
                $lines += max(1, (int) ceil(mb_strwidth($line) / max(1, $width - 2)));
            }
            $height = max($height, $lines * 12.75 + 6);
        }

        // Excel supports a maximum row height of 409.5 points; cell values are never shortened.
        return min(409.5, $height);
    }

    private function validation(string $range, array $options): string
    {
        $list = implode(',', $options);
        // Excel inline validation lists are limited to 255 characters and cannot escape commas in individual options.
        if (mb_strlen($list) > 253 || count($options) !== count(explode(',', $list)) || str_contains($list, '"')) {
            throw new \LogicException('Pilihan dropdown Excel melebihi batas daftar inline.');
        }

        return '<dataValidation type="list" allowBlank="1" showErrorMessage="1" errorStyle="stop" sqref="'.$range
            .'"><formula1>'.$this->xml('"'.$list.'"').'</formula1></dataValidation>';
    }

    private function conditionalFormats(int $last): string
    {
        $xml = '';
        $priority = 1;
        $dxf = 0;
        foreach (['H' => HumasCsr::STATUSES, 'J' => HumasCsr::LUARAN] as $column => $options) {
            $xml .= '<conditionalFormatting sqref="'.$column.'5:'.$column.$last.'">';
            foreach ($options as $label) {
                $xml .= '<cfRule type="cellIs" operator="equal" priority="'.$priority++.'" dxfId="'.$dxf++
                    .'"><formula>'.$this->xml('"'.$label.'"').'</formula></cfRule>';
            }
            $xml .= '</conditionalFormatting>';
        }

        return $xml;
    }

    private function styles(): string
    {
        $document = new \DOMDocument;
        $document->load(resource_path('templates/humas-csr/styles.xml'), LIBXML_NONET);
        $namespace = $document->documentElement->namespaceURI;
        $dxfs = $document->getElementsByTagNameNS($namespace, 'dxfs')->item(0);
        if (! $dxfs) {
            $dxfs = $document->createElementNS($namespace, 'dxfs');
            $document->documentElement->appendChild($dxfs);
        }
        while ($dxfs->firstChild) {
            $dxfs->removeChild($dxfs->firstChild);
        }
        $colors = array_merge(array_values(HumasCsr::STATUS_COLORS), array_values(HumasCsr::LUARAN_COLORS));
        $fonts = $document->getElementsByTagNameNS($namespace, 'fonts')->item(0);
        $fills = $document->getElementsByTagNameNS($namespace, 'fills')->item(0);
        $cellStyles = $document->getElementsByTagNameNS($namespace, 'cellXfs')->item(0);
        $dxfs->setAttribute('count', (string) count($colors));
        foreach ($colors as [$background, $foreground]) {
            $foreground = strtoupper(substr($foreground, 1));
            $background = strtoupper(substr($background, 1));
            $font = $fonts->childNodes->item(1)->cloneNode(true);
            while ($font->getElementsByTagNameNS($namespace, 'color')->length) {
                $font->removeChild($font->getElementsByTagNameNS($namespace, 'color')->item(0));
            }
            $fontColor = $document->createElementNS($namespace, 'color');
            $fontColor->setAttribute('rgb', 'FF'.$foreground);
            $font->appendChild($fontColor);
            $fontId = $fonts->childNodes->length;
            $fonts->appendChild($font);
            $fill = $document->createElementNS($namespace, 'fill');
            $pattern = $document->createElementNS($namespace, 'patternFill');
            $pattern->setAttribute('patternType', 'solid');
            $fillColor = $document->createElementNS($namespace, 'fgColor');
            $fillColor->setAttribute('rgb', 'FF'.$background);
            $pattern->appendChild($fillColor);
            $fill->appendChild($pattern);
            $fillId = $fills->childNodes->length;
            $fills->appendChild($fill);
            $style = $cellStyles->childNodes->item(20)->cloneNode(true);
            $style->setAttribute('fontId', (string) $fontId);
            $style->setAttribute('fillId', (string) $fillId);
            $style->setAttribute('applyFont', '1');
            $style->setAttribute('applyFill', '1');
            $cellStyles->appendChild($style);
            $fragment = $document->createDocumentFragment();
            $fragment->appendXML('<dxf xmlns="'.$namespace.'"><font><color rgb="FF'.$foreground
                .'"/></font><fill><patternFill patternType="solid"><fgColor rgb="FF'.$background
                .'"/><bgColor indexed="64"/></patternFill></fill></dxf>');
            $dxfs->appendChild($fragment);
        }
        foreach ([$fonts, $fills, $cellStyles] as $collection) {
            $collection->setAttribute('count', (string) $collection->childNodes->length);
        }

        return $document->saveXML();
    }
}
