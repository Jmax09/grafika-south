<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use ZipArchive;

class SupplierReportExporter
{
    /**
     * Generate an executive-styled .xlsx workbook binary for the given suppliers.
     *
     * @param Collection<int, Supplier> $suppliers
     */
    public function generateXlsx(Collection $suppliers): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_gs_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create temporary XLSX archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());
        $zip->addFromString('_rels/.rels', $this->buildRootRels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
        $zip->addFromString('xl/styles.xml', $this->buildStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildWorksheet($suppliers));

        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    private function buildContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
    }

    private function buildRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
    }

    private function buildWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
    }

    private function buildWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="20480" windowHeight="10240"/></bookViews>' .
            '<sheets><sheet name="Suppliers Directory" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';
    }

    /**
     * Styles breakdown:
     * 0: Default normal
     * 1: Brand title (15pt bold, #0F172A)
     * 2: Report title (11pt bold, #0284C7 cyan)
     * 3: Meta line (9pt, #64748B)
     * 4: Header cell (10pt bold white, Navy background #1E293B, centered)
     * 5: Data text left (10pt, thin border #E2E8F0)
     * 6: Data text center (10pt, thin border #E2E8F0)
     * 7: Data integer center (10pt, thin border #E2E8F0)
     * 8: Data currency right (10pt, thin border #E2E8F0, formatted ₱#,##0.00)
     * 9: Total label right (10pt bold, gray bg #F8FAFC, double bottom border)
     * 10: Total integer center (10pt bold, gray bg #F8FAFC, double bottom border)
     * 11: Total currency right (10pt bold, gray bg #F8FAFC, double bottom border)
     * 12: Total balance red right (10pt bold red #DC2626, gray bg #F8FAFC, double bottom border)
     * 13: Empty total cell (gray bg #F8FAFC, double bottom border)
     */
    private function buildStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<numFmts count="1">' .
            '<numFmt numFmtId="164" formatCode="&quot;₱&quot;#,##0.00"/>' .
            '</numFmts>' .
            '<fonts count="7">' .
            '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF1F2937"/></font>' . // 0: Normal
            '<font><b/><sz val="15"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' . // 1: Brand
            '<font><b/><sz val="11"/><name val="Segoe UI"/><color rgb="FF0284C7"/></font>' . // 2: Report Name
            '<font><sz val="9"/><name val="Segoe UI"/><color rgb="FF64748B"/></font>' . // 3: Meta
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' . // 4: Header white
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' . // 5: Total bold
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FFDC2626"/></font>' . // 6: Total red
            '</fonts>' .
            '<fills count="4">' .
            '<fill><patternFill patternType="none"/></fill>' . // 0
            '<fill><patternFill patternType="gray125"/></fill>' . // 1
            '<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' . // 2: Slate Navy #1E293B
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . // 3: Soft Gray #F8FAFC
            '</fills>' .
            '<borders count="3">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' . // 0: None
            '<border>' . // 1: Thin border for data
            '<left style="thin"><color rgb="FFE2E8F0"/></left>' .
            '<right style="thin"><color rgb="FFE2E8F0"/></right>' .
            '<top style="thin"><color rgb="FFE2E8F0"/></top>' .
            '<bottom style="thin"><color rgb="FFE2E8F0"/></bottom>' .
            '</border>' .
            '<border>' . // 2: Total row border
            '<left style="thin"><color rgb="FFE2E8F0"/></left>' .
            '<right style="thin"><color rgb="FFE2E8F0"/></right>' .
            '<top style="thin"><color rgb="FF94A3B8"/></top>' .
            '<bottom style="double"><color rgb="FF0F172A"/></bottom>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="14">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . // 0: Default
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' . // 1: Brand title
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' . // 2: Report title
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' . // 3: Meta
            '<xf numFmtId="0" fontId="4" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 4: Header
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>' . // 5: Text left
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 6: Text center
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 7: Int center
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 8: Currency right
            '<xf numFmtId="0" fontId="5" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 9: Total label right
            '<xf numFmtId="0" fontId="5" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 10: Total int center
            '<xf numFmtId="164" fontId="5" fillId="3" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 11: Total curr right
            '<xf numFmtId="164" fontId="6" fillId="3" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 12: Total red curr right
            '<xf numFmtId="0" fontId="5" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>' . // 13: Empty total cell
            '</cellXfs>' .
            '</styleSheet>';
    }

    private function buildWorksheet(Collection $suppliers): string
    {
        // Generous column widths so NO words wrap, clip, or become "###"
        $colWidths = [
            1  => 28, // A: Supplier Name
            2  => 22, // B: Contact Person
            3  => 20, // C: Phone (text formatted)
            4  => 28, // D: Email
            5  => 32, // E: Address
            6  => 16, // F: Catalog Items
            7  => 18, // G: Purchase Orders
            8  => 22, // H: Total Purchases (PHP)
            9  => 22, // I: Total Paid (PHP)
            10 => 22, // J: Remaining Balance (PHP)
            11 => 18, // K: Payment Status
            12 => 16, // L: Date Added
        ];

        $colsXml = '<cols>';
        foreach ($colWidths as $col => $w) {
            $colsXml .= '<col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $rowsXml = '<sheetData>';

        // Row 1: Brand title
        $rowsXml .= '<row r="1" ht="24" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>GRAFIKA SOUTH</t></is></c>';
        $rowsXml .= '</row>';

        // Row 2: Report Name
        $rowsXml .= '<row r="2" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>Suppliers &amp; Purchasing Directory Report</t></is></c>';
        $rowsXml .= '</row>';

        // Row 3: Generation Metadata
        $exportTime = now()->format('F d, Y · h:i A');
        $rowsXml .= '<row r="3" ht="18" customHeight="1">';
        $rowsXml .= '<c r="A3" s="3" t="inlineStr"><is><t>Exported on: ' . $this->xmlEscape($exportTime) . ' | Total Suppliers Listed: ' . $suppliers->count() . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 4: Spacer
        $rowsXml .= '<row r="4" ht="10" customHeight="1"></row>';

        // Row 5: Clean Single-Line Table Headers
        $headers = [
            'Supplier Name',
            'Contact Person',
            'Phone',
            'Email',
            'Address',
            'Catalog Items',
            'Purchase Orders',
            'Total Purchases (PHP)',
            'Total Paid (PHP)',
            'Remaining Balance (PHP)',
            'Payment Status',
            'Date Added',
        ];

        $rowsXml .= '<row r="5" ht="30" customHeight="1">';
        foreach ($headers as $idx => $h) {
            $cellRef = $this->columnLetter($idx + 1) . '5';
            $rowsXml .= '<c r="' . $cellRef . '" s="4" t="inlineStr"><is><t>' . $this->xmlEscape($h) . '</t></is></c>';
        }
        $rowsXml .= '</row>';

        // Data Rows starting at Row 6
        $currentRow = 6;
        $sumPurchases = 0;
        $sumPaid = 0;
        $sumBalance = 0;
        $sumPOs = 0;
        $sumCatalog = 0;

        foreach ($suppliers as $s) {
            $purchases = (float) $s->total_purchases;
            $paid = (float) $s->total_paid;
            $balance = (float) $s->balance;
            $pos = (int) $s->purchases_count;
            $catalogCount = (int) ($s->materials_count ?? $s->materials()->count());

            $sumPurchases += $purchases;
            $sumPaid += $paid;
            $sumBalance += $balance;
            $sumPOs += $pos;
            $sumCatalog += $catalogCount;

            $statusText = $balance > 0
                ? 'Has Payables'
                : ($pos > 0 ? 'Fully Settled' : 'No Orders');

            $dateText = $s->created_at ? $s->created_at->format('Y-m-d') : '—';
            $phone = $s->phone ?: '—';

            $rowsXml .= '<row r="' . $currentRow . '" ht="22" customHeight="1">';
            $rowsXml .= '<c r="A' . $currentRow . '" s="5" t="inlineStr"><is><t>' . $this->xmlEscape($s->name) . '</t></is></c>';
            $rowsXml .= '<c r="B' . $currentRow . '" s="5" t="inlineStr"><is><t>' . $this->xmlEscape($s->contact_person ?: '—') . '</t></is></c>';
            // Store phone explicitly as text string to prevent scientific notation (9.28E+14)
            $rowsXml .= '<c r="C' . $currentRow . '" s="6" t="inlineStr"><is><t>' . $this->xmlEscape($phone) . '</t></is></c>';
            $rowsXml .= '<c r="D' . $currentRow . '" s="5" t="inlineStr"><is><t>' . $this->xmlEscape($s->email ?: '—') . '</t></is></c>';
            $rowsXml .= '<c r="E' . $currentRow . '" s="5" t="inlineStr"><is><t>' . $this->xmlEscape($s->address ?: '—') . '</t></is></c>';
            $rowsXml .= '<c r="F' . $currentRow . '" s="7"><v>' . $catalogCount . '</v></c>';
            $rowsXml .= '<c r="G' . $currentRow . '" s="7"><v>' . $pos . '</v></c>';
            $rowsXml .= '<c r="H' . $currentRow . '" s="8"><v>' . number_format($purchases, 2, '.', '') . '</v></c>';
            $rowsXml .= '<c r="I' . $currentRow . '" s="8"><v>' . number_format($paid, 2, '.', '') . '</v></c>';
            $rowsXml .= '<c r="J' . $currentRow . '" s="8"><v>' . number_format($balance, 2, '.', '') . '</v></c>';
            $rowsXml .= '<c r="K' . $currentRow . '" s="6" t="inlineStr"><is><t>' . $this->xmlEscape($statusText) . '</t></is></c>';
            $rowsXml .= '<c r="L' . $currentRow . '" s="6" t="inlineStr"><is><t>' . $this->xmlEscape($dateText) . '</t></is></c>';
            $rowsXml .= '</row>';

            $currentRow++;
        }

        // Total Summary Row: Merged A to E
        $totalRow = $currentRow;
        $rowsXml .= '<row r="' . $totalRow . '" ht="26" customHeight="1">';
        $rowsXml .= '<c r="A' . $totalRow . '" s="9" t="inlineStr"><is><t>TOTAL SUMMARY</t></is></c>';
        $rowsXml .= '<c r="B' . $totalRow . '" s="9"/>';
        $rowsXml .= '<c r="C' . $totalRow . '" s="9"/>';
        $rowsXml .= '<c r="D' . $totalRow . '" s="9"/>';
        $rowsXml .= '<c r="E' . $totalRow . '" s="9"/>';
        $rowsXml .= '<c r="F' . $totalRow . '" s="10"><v>' . $sumCatalog . '</v></c>';
        $rowsXml .= '<c r="G' . $totalRow . '" s="10"><v>' . $sumPOs . '</v></c>';
        $rowsXml .= '<c r="H' . $totalRow . '" s="11"><v>' . number_format($sumPurchases, 2, '.', '') . '</v></c>';
        $rowsXml .= '<c r="I' . $totalRow . '" s="11"><v>' . number_format($sumPaid, 2, '.', '') . '</v></c>';
        $balanceStyle = $sumBalance > 0 ? '12' : '11';
        $rowsXml .= '<c r="J' . $totalRow . '" s="' . $balanceStyle . '"><v>' . number_format($sumBalance, 2, '.', '') . '</v></c>';
        $rowsXml .= '<c r="K' . $totalRow . '" s="13"/>';
        $rowsXml .= '<c r="L' . $totalRow . '" s="13"/>';
        $rowsXml .= '</row>';

        $rowsXml .= '</sheetData>';

        // Merge total label across A{totalRow}:E{totalRow}
        $mergeXml = '<mergeCells count="1"><mergeCell ref="A' . $totalRow . ':E' . $totalRow . '"/></mergeCells>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            $colsXml .
            $rowsXml .
            $mergeXml .
            '</worksheet>';
    }

    private function columnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $modulo = ($colIndex - 1) % 26;
            $letter = chr(65 + $modulo) . $letter;
            $colIndex = (int) (($colIndex - $modulo) / 26);
        }
        return $letter;
    }

    private function xmlEscape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
