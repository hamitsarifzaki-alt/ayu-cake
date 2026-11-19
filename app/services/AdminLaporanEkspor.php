<?php

/** Ekspor mandiri di PHP: PDF A4 lanskap dan workbook OOXML asli. */
class AdminLaporanEkspor
{
    private function rupiah(int $nilai): string
    {
        return 'Rp' . number_format($nilai, 0, ',', '.');
    }

    private function tanggal(string $nilai): string
    {
        return (new DateTimeImmutable($nilai))->format('d-m-Y');
    }

    private function periode(array $laporan): string
    {
        return $this->tanggal($laporan['mulai']) . ' s.d. ' . $this->tanggal($laporan['akhir']);
    }

    public function xlsx(array $laporan): string
    {
        $xml = static fn ($s): string => htmlspecialchars(preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', (string) $s), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $str = static fn (string $pos, string $value, int $style = 0): string => '<c r="' . $pos . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $xml($value) . '</t></is></c>';
        $num = static fn (string $pos, int $value, int $style = 3): string => '<c r="' . $pos . '" s="' . $style . '"><v>' . $value . '</v></c>';
        $formula = static fn (string $pos, string $f, int $value, int $style): string => '<c r="' . $pos . '" s="' . $style . '"><f>' . $xml($f) . '</f><v>' . $value . '</v></c>';
        $end = max(8, 7 + count($laporan['items']));
        $t = $laporan['total'];
        $rows = '<row r="1" ht="30" customHeight="1">' . $str('A1', 'Ayu Cake | Laporan Penjualan', 1) . '</row>'
            . '<row r="2" ht="26" customHeight="1">' . $str('A2', 'Periode ' . $this->periode($laporan) . ' | Berdasarkan tanggal pesanan selesai', 0) . '</row>'
            . '<row r="4" ht="24" customHeight="1">' . $str('A4', 'Penjualan produk (tanpa ongkir)', 2) . $str('D4', 'Pesanan selesai', 2) . $str('G4', 'Produk terjual (loyang)', 2) . '</row>'
            . '<row r="5" ht="28" customHeight="1">' . $formula('A5', 'SUM(F8:F' . $end . ')', $t['produk'], 4)
            . $num('D5', $t['pesanan'], 5) . $formula('G5', 'SUM(E8:E' . $end . ')', $t['loyang'], 5) . '</row>';
        $headers = ['Nomor pesanan', 'Pelanggan', 'Tanggal selesai', 'Jenis pesanan', 'Loyang', 'Nilai produk', 'Ongkir', 'Total'];
        $rows .= '<row r="7" ht="26" customHeight="1">';
        foreach ($headers as $i => $h) $rows .= $str(chr(65 + $i) . '7', $h, 2);
        $rows .= '</row>';
        foreach ($laporan['items'] as $i => $p) {
            $r = $i + 8;
            $serial = (int) (new DateTimeImmutable('1899-12-30'))->diff(new DateTimeImmutable($p['tanggal_selesai']))->format('%a');
            $rows .= '<row r="' . $r . '" ht="30" customHeight="1">' . $str('A' . $r, $p['nomor']) . $str('B' . $r, $p['nama'])
                . $num('C' . $r, $serial, 6) . $str('D' . $r, $p['jenis']) . $num('E' . $r, $p['loyang'])
                . $num('F' . $r, $p['subtotal'], 7) . $num('G' . $r, $p['ongkir'], 7)
                . $formula('H' . $r, 'F' . $r . '+G' . $r, $p['total'], 7) . '</row>';
        }
        if (!$laporan['items']) $rows .= '<row r="8" ht="30" customHeight="1">' . $str('A8', 'Tidak ada pesanan selesai pada periode ini.') . '</row>';
        $r = $end + 2;
        $rows .= '<row r="' . $r . '" ht="28" customHeight="1">' . $str('A' . $r, 'Total periode', 2)
            . $formula('E' . $r, 'SUM(E8:E' . $end . ')', $t['loyang'], 5)
            . $formula('F' . $r, 'SUM(F8:F' . $end . ')', $t['produk'], 4)
            . $formula('G' . $r, 'SUM(G8:G' . $end . ')', $t['ongkir'], 4)
            . $formula('H' . $r, 'SUM(H8:H' . $end . ')', $t['total'], 4) . '</row>';
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:H' . $r . '"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="22"/><cols><col min="1" max="1" width="28" customWidth="1"/><col min="2" max="2" width="23" customWidth="1"/><col min="3" max="3" width="20" customWidth="1"/><col min="4" max="4" width="22" customWidth="1"/><col min="5" max="5" width="10" customWidth="1"/><col min="6" max="8" width="19" customWidth="1"/></cols><sheetData>' . $rows . '</sheetData>'
            . '<autoFilter ref="A7:H' . $end . '"/><mergeCells count="7"><mergeCell ref="A1:H1"/><mergeCell ref="A2:H2"/><mergeCell ref="A4:C4"/><mergeCell ref="D4:F4"/><mergeCell ref="G4:H4"/><mergeCell ref="A5:C5"/><mergeCell ref="A' . $r . ':D' . $r . '"/></mergeCells>'
            . '<printOptions horizontalCentered="1"/><pageMargins left="0.25" right="0.25" top="0.4" bottom="0.4" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/></worksheet>';
        $styles = '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2"><numFmt numFmtId="164" formatCode="&quot;Rp&quot;#,##0"/><numFmt numFmtId="165" formatCode="dd mmm yyyy"/></numFmts>'
            . '<fonts count="3"><font><sz val="11"/><color rgb="FF3E2723"/><name val="Calibri"/></font><font><b/><sz val="20"/><color rgb="FFA04A15"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FF3E2723"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF0D8"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="8">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="164" fontId="2" fillId="2" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0"/>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        $files = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Penjualan" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">Penjualan!$7:$7</definedName></definedNames><calcPr calcId="191029" fullCalcOnLoad="1"/></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $styles, 'xl/worksheets/sheet1.xml' => $sheet,
        ];
        if (isset($laporan['produk_terjual'])) {
            $files['[Content_Types].xml'] = str_replace('</Types>', '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>', $files['[Content_Types].xml']);
            $files['xl/workbook.xml'] = str_replace('</sheets>', '<sheet name="Produk Terjual" sheetId="2" r:id="rId3"/></sheets>', $files['xl/workbook.xml']);
            $files['xl/_rels/workbook.xml.rels'] = str_replace('</Relationships>', '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/></Relationships>', $files['xl/_rels/workbook.xml.rels']);
            $files['xl/worksheets/sheet2.xml'] = $this->produkXlsx($laporan, $str, $num, $formula);
        }
        $path = tempnam(sys_get_temp_dir(), 'ayu_laporan_');
        if ($path === false) throw new RuntimeException('Folder sementara tidak tersedia.');
        try {
            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Arsip Excel tidak dapat dibuat.');
            foreach ($files as $name => $isi) {
                if (!$zip->addFromString($name, $isi)) throw new RuntimeException('Isi Excel tidak dapat ditulis.');
            }
            if (!$zip->close()) throw new RuntimeException('Arsip Excel tidak dapat disimpan.');
            $isi = file_get_contents($path);
            if ($isi === false) throw new RuntimeException('Arsip Excel tidak dapat dibaca.');
            return $isi;
        } finally {
            unlink($path);
        }
    }

    public function pdf(array $laporan): string
    {
        // Koordinat dalam point dari atas halaman; font standar PDF tidak perlu instalasi.
        $widths = [210, 105, 110, 50, 95, 80, 108];
        $pages = [];
        $chunks = array_chunk($laporan['items'], 7) ?: [[]];
        $produkChunks = isset($laporan['produk_terjual']) ? (array_chunk($laporan['produk_terjual'], 12) ?: [[]]) : [];
        $jumlahHalaman = count($chunks) + count($produkChunks);
        $t = $laporan['total'];
        foreach ($chunks as $index => $items) {
            $s = '';
            $this->rect($s, 0, 0, 842, 78, '#4f1e15');
            $this->text($s, 32, 33, 'Ayu Cake | Laporan Penjualan', 20, true, '#fff9f0');
            $this->text($s, 32, 57, 'Periode ' . $this->periode($laporan) . ' | Berdasarkan tanggal pesanan selesai', 10, false, '#fff9f0');
            $this->rect($s, 32, 96, 778, 66, '#fff0d8');
            foreach ([[46, 'Penjualan produk (tanpa ongkir)', $this->rupiah($t['produk'])], [335, 'Pesanan selesai', $t['pesanan'] . ' pesanan'], [590, 'Produk terjual', $t['loyang'] . ' loyang']] as [$x, $label, $value]) {
                $this->text($s, $x, 116, $label, 10);
                $this->text($s, $x, 143, $value, 17, true, '#a04a15');
            }
            $y = 183;
            $this->rect($s, 32, $y, 778, 30, '#fff0d8');
            $x = 32;
            foreach (['Pesanan / Pelanggan', 'Tanggal selesai', 'Jenis pesanan', 'Loyang', 'Nilai produk', 'Ongkir', 'Total'] as $i => $label) {
                $this->text($s, $x + 8, $y + 19, $label, 9, true);
                $x += $widths[$i];
            }
            $y += 30;
            foreach ($items as $p) {
                $x = 32;
                $values = [$p['nomor'], $this->tanggal($p['tanggal_selesai']), $p['jenis'], (string) $p['loyang'], $this->rupiah($p['subtotal']), $this->rupiah($p['ongkir']), $this->rupiah($p['total'])];
                foreach ($values as $i => $value) {
                    $this->text($s, $x + 8, $y + 17, $value, 9, $i === 0 || $i === 6, $i === 6 ? '#a04a15' : '#3e2723', $widths[$i] - 16);
                    $x += $widths[$i];
                }
                $this->text($s, 40, $y + 32, $p['nama'], 9, false, '#88766b', 194);
                $this->rect($s, 32, $y + 43, 778, .5, '#ead5be');
                $y += 44;
            }
            if (!$items) {
                $this->text($s, 46, $y + 25, 'Tidak ada pesanan selesai pada periode ini.', 11);
                $y += 50;
            }
            if ($index === count($chunks) - 1) {
                $this->rect($s, 32, $y + 10, 778, 31, '#fff0d8');
                $x = 32;
                foreach (['Total periode', '', '', (string) $t['loyang'], $this->rupiah($t['produk']), $this->rupiah($t['ongkir']), $this->rupiah($t['total'])] as $i => $v) {
                    $this->text($s, $x + 8, $y + 30, $v, 9, true, $i === 6 ? '#a04a15' : '#3e2723');
                    $x += $widths[$i];
                }
            }
            $this->text($s, 32, 578, 'Ayu Cake - Kue untuk setiap cerita.', 9, false, '#88766b');
            $this->text($s, 710, 578, 'Halaman ' . ($index + 1) . ' / ' . $jumlahHalaman, 9, false, '#88766b');
            $pages[] = $s;
        }
        foreach ($produkChunks as $index => $produk) {
            $s = '';
            $this->rect($s, 0, 0, 842, 78, '#4f1e15');
            $this->text($s, 32, 33, 'Ayu Cake | Rincian Produk Terjual', 20, true, '#fff9f0');
            $this->text($s, 32, 57, 'Periode ' . $this->periode($laporan) . ' | Berdasarkan tanggal pesanan selesai', 10, false, '#fff9f0');
            $this->text($s, 32, 110, 'Penjualan produk: ' . $this->rupiah($t['produk']) . ' | Terjual: ' . $t['loyang'] . ' loyang', 13, true, '#a04a15');
            $this->text($s, 32, 135, 'Ongkir: ' . $this->rupiah($t['ongkir']) . ' | Total produk dan ongkir: ' . $this->rupiah($t['total']), 11);
            $this->text($s, 32, 166, 'Harga pada pesanan; produk dengan harga berbeda ditampilkan terpisah.', 10, false, '#88766b');
            $w = [280, 140, 120, 90, 148];
            $y = 188;
            $x = 32;
            $this->rect($s, $x, $y, 778, 30, '#fff0d8');
            foreach (['Produk', 'Kategori', 'Harga / loyang', 'Terjual', 'Penjualan produk'] as $i => $label) {
                $this->text($s, $x + 8, $y + 19, $label, 10, true);
                $x += $w[$i];
            }
            $y += 30;
            foreach ($produk as $item) {
                $x = 32;
                foreach ([$item['nama'], $item['kategori'], $this->rupiah($item['harga']), $item['jumlah'] . ' loyang', $this->rupiah($item['subtotal'])] as $i => $value) {
                    $this->text($s, $x + 8, $y + 17, $value, 10, $i === 4, $i === 4 ? '#a04a15' : '#3e2723', $w[$i] - 16);
                    $x += $w[$i];
                }
                $this->rect($s, 32, $y + 23, 778, .5, '#ead5be');
                $y += 24;
            }
            if (!$produk) {
                $this->text($s, 40, $y + 25, 'Belum ada produk terjual pada periode ini.', 11);
                $y += 50;
            }
            if ($index === count($produkChunks) - 1) {
                $this->rect($s, 32, $y + 10, 778, 31, '#fff0d8');
                $this->text($s, 40, $y + 30, 'Total produk', 10, true);
                $this->text($s, 580, $y + 30, $t['loyang'] . ' loyang', 10, true);
                $this->text($s, 670, $y + 30, $this->rupiah($t['produk']), 10, true, '#a04a15');
            }
            $this->text($s, 32, 578, 'Ayu Cake - Kue untuk setiap cerita.', 9, false, '#88766b');
            $this->text($s, 710, 578, 'Halaman ' . (count($chunks) + $index + 1) . ' / ' . $jumlahHalaman, 9, false, '#88766b');
            $pages[] = $s;
        }
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>'];
        $kids = [];
        foreach ($pages as $i => $stream) {
            $id = 5 + $i * 2;
            $kids[] = $id . ' 0 R';
            $objects[$id] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . ($id + 1) . ' 0 R >>';
            $objects[$id + 1] = '<< /Length ' . strlen($stream) . ">>\nstream\n" . $stream . "endstream";
        }
        $objects[2] = '<< /Type /Pages /Count ' . count($pages) . ' /Kids [' . implode(' ', $kids) . '] >>';
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $obj) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$obj\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        return $pdf . 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
    }

    private function produkXlsx(array $laporan, callable $str, callable $num, callable $formula): string
    {
        $rows = '<row r="1" ht="30" customHeight="1">' . $str('A1', 'Ayu Cake | Rincian Produk Terjual', 1) . '</row>'
            . '<row r="2" ht="26" customHeight="1">' . $str('A2', 'Periode ' . $this->periode($laporan) . ' | Berdasarkan tanggal pesanan selesai') . '</row>'
            . '<row r="3" ht="26" customHeight="1">' . $str('A3', 'Harga pada pesanan; harga berbeda ditampilkan pada baris terpisah.') . '</row>';
        $rows .= '<row r="5" ht="26" customHeight="1">';
        foreach (['Produk', 'Kategori', 'Harga / loyang', 'Terjual (loyang)', 'Penjualan produk'] as $i => $label) $rows .= $str(chr(65 + $i) . '5', $label, 2);
        $rows .= '</row>';
        foreach ($laporan['produk_terjual'] as $i => $p) {
            $r = $i + 6;
            $rows .= '<row r="' . $r . '" ht="30" customHeight="1">' . $str('A' . $r, $p['nama']) . $str('B' . $r, $p['kategori'])
                . $num('C' . $r, $p['harga'], 7) . $num('D' . $r, $p['jumlah']) . $num('E' . $r, $p['subtotal'], 7) . '</row>';
        }
        $end = max(6, count($laporan['produk_terjual']) + 5);
        if (!$laporan['produk_terjual']) $rows .= '<row r="6" ht="30" customHeight="1">' . $str('A6', 'Belum ada produk terjual pada periode ini.') . '</row>';
        $r = $end + 2;
        $rows .= '<row r="' . $r . '" ht="28" customHeight="1">' . $str('A' . $r, 'Total produk', 2)
            . $formula('D' . $r, 'SUM(D6:D' . $end . ')', $laporan['total']['loyang'], 5)
            . $formula('E' . $r, 'SUM(E6:E' . $end . ')', $laporan['total']['produk'], 4) . '</row>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:E' . $r . '"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="22"/><cols><col min="1" max="1" width="44" customWidth="1"/><col min="2" max="2" width="24" customWidth="1"/><col min="3" max="5" width="24" customWidth="1"/></cols><sheetData>' . $rows . '</sheetData>'
            . '<autoFilter ref="A5:E' . $end . '"/><mergeCells count="4"><mergeCell ref="A1:E1"/><mergeCell ref="A2:E2"/><mergeCell ref="A3:E3"/><mergeCell ref="A' . $r . ':C' . $r . '"/></mergeCells>'
            . '<printOptions horizontalCentered="1"/><pageMargins left="0.25" right="0.25" top="0.4" bottom="0.4" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/></worksheet>';
    }

    private function color(string $hex): string
    {
        return implode(' ', array_map(static fn ($v) => sprintf('%.3F', hexdec($v) / 255), str_split(substr($hex, 1), 2)));
    }

    private function rect(string &$s, float $x, float $y, float $w, float $h, string $color): void
    {
        $s .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $this->color($color), $x, 595 - $y - $h, $w, $h);
    }

    private function text(string &$s, float $x, float $y, string $text, int $size = 10, bool $bold = false, string $color = '#3e2723', ?float $maxWidth = null): void
    {
        // Satu baris per sel; potong teks panjang tanpa menimpa kolom tetangga.
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);
        if ($maxWidth !== null) {
            $limit = (int) floor($maxWidth / ($size * .58));
            if (mb_strlen($text) > $limit) $text = mb_substr($text, 0, max(0, $limit - 3)) . '...';
        }
        $bytes = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        $bytes = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $bytes === false ? '?' : $bytes);
        $s .= sprintf("BT /%s %d Tf %s rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, $this->color($color), $x, 595 - $y, $bytes);
    }
}
