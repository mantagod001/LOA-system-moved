<?php

namespace App\Services;

use App\Models\LoaRequest;
use setasign\Fpdi\Tcpdf\Fpdi;

class LoaPdfGenerator
{
    /**
     * All coordinates are in inches from the template (measured in LibreOffice).
     * We convert to mm internally: 1 inch = 25.4 mm.
     * Template size: Letter 8.50" × 11.00"
     */

    /** Times New Roman — TCPDF 6 built-in core font name */
    private const FONT      = 'times';
    private const FONT_SIZE = 11;

    /** inch → mm */
    private static function mm(float $inches): float
    {
        return round($inches * 25.4, 4);
    }

    /**
     * Generate the filled LOA PDF and return the raw binary string.
     */
    public function generate(LoaRequest $loa): string
    {
        $loa->loadMissing([
            'department',
            'program',
            'deptHeadActor',
            'sasoActor',
            'campusDirectorActor',
        ]);

        // TCPDF 6 via FPDI — Letter, Portrait, mm, Unicode, UTF-8
        $pdf = new Fpdi('P', 'mm', 'LETTER', true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage();

        // Import the template as the page background
        $templatePath = base_path('resources/template/EVSU-SASO-F-040.pdf');
        if (! file_exists($templatePath)) {
            throw new \RuntimeException("LOA template not found at [{$templatePath}].");
        }

        $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, self::mm(8.50), self::mm(11.00));

        // Set font — Times (Times New Roman), regular, 11pt
        $pdf->SetFont(self::FONT, '', self::FONT_SIZE);
        $pdf->SetTextColor(0, 0, 0);

        // ── Field placements ──────────────────────────────────────────────────

        // Date filed (Date of filing: use actual submission date if available, fallback to today)
        $dateFiled = $loa->submitted_at?->format('F j, Y') ?? now()->format('F j, Y');
        $this->place($pdf, 6.37, 2.77, $dateFiled);

        // Leave start date — 12pt bold
        $pdf->SetFont(self::FONT, 'B', 12);
        $this->place($pdf, 6.00, 3.54, $loa->start_date?->format('F j, Y') ?? '');

        // Leave return date — 12pt bold
        $this->place($pdf, 1.15, 3.81, $loa->return_date?->format('F j, Y') ?? '');

        // Reason — 11pt regular, split into up to 4 lines (~90 chars each)
        $pdf->SetFont(self::FONT, '', 11);
        $lines = $this->wrapText($loa->reason ?? '', 90);

        $reasonCoords = [
            [0.75, 4.45],
            [0.75, 4.83],
            [0.75, 5.20],
            [0.75, 5.58],
        ];
        foreach ($reasonCoords as $i => [$rx, $ry]) {
            if (isset($lines[$i])) {
                $this->place($pdf, $rx, $ry, $lines[$i]);
            }
        }

        // Student name — 11pt bold
        $pdf->SetFont(self::FONT, 'B', 11);
        $this->place($pdf, 0.87, 6.12, $loa->full_name ?? '');

        // Program and year level — 11pt regular
        $pdf->SetFont(self::FONT, '', 11);
        $term = $loa->academicTermLabel();
        $programYear = trim(($loa->program?->name ?? '').'  '.($loa->year_level ?? '').($term !== '—' ? '  '.$term : ''));

        // Available line width is ~3.05 inches (~77.5mm)
        $maxWidthMm   = self::mm(3.05);
        $programLines = $this->wrapTextByWidth($pdf, $programYear, $maxWidthMm);

        if (count($programLines) <= 1) {
            // Fits on 1 line: stay at x 4.62, y 6.12
            $this->place($pdf, 4.62, 6.12, $programYear);
        } else {
            // Too long for 1 line: 2 lines at x 4.65, y 6.00 and x 4.65, y 6.20
            $this->place($pdf, 4.65, 6.00, $programLines[0]);
            $line2 = implode(' ', array_slice($programLines, 1));
            $this->place($pdf, 4.65, 6.20, $line2);
        }

        // Signatories — 11pt bold
        $pdf->SetFont(self::FONT, 'B', 11);

        // Department Head name
        $this->place($pdf, 3.13, 7.25, $loa->deptHeadActor?->name ?? '');

        // SASO Officer name
        $this->place($pdf, 3.13, 8.12, $loa->sasoActor?->name ?? '');

        // Campus Director name
        $this->place($pdf, 3.13, 9.37, $loa->campusDirectorActor?->name ?? '');

        // Return raw PDF binary
        return $pdf->Output('loa.pdf', 'S');
    }

    /**
     * Place text at the given X/Y position in inches.
     */
    private function place(Fpdi $pdf, float $x, float $y, string $text): void
    {
        $pdf->SetXY(self::mm($x), self::mm($y));
        // Width 0 = auto, height slightly larger than font size, no border, no newline
        $pdf->Cell(0, self::mm(0.20), $text, 0, 0, 'L');
    }

    /**
     * Word-wrap text into lines that do not exceed $maxWidthMm in the current font.
     */
    private function wrapTextByWidth(Fpdi $pdf, string $text, float $maxWidthMm): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $line  = '';

        foreach ($words as $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            if ($pdf->GetStringWidth($test) <= $maxWidthMm) {
                $line = $test;
            } else {
                if ($line !== '') {
                    $lines[] = $line;
                }
                $line = $word;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Word-wrap text into lines no longer than $maxChars characters.
     */
    private function wrapText(string $text, int $maxChars): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $line  = '';

        foreach ($words as $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            if (strlen($test) <= $maxChars) {
                $line = $test;
            } else {
                if ($line !== '') {
                    $lines[] = $line;
                }
                $line = $word;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
