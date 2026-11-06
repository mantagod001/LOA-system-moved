<?php

namespace App\Services;

use App\Models\StudentWhitelist;
use App\Support\PersonName;
use App\Support\StudentId;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentWhitelistService
{
    /**
     * @return array{added: int, updated: int, skipped: int}
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        $headers = array_shift($rows);
        if (! is_array($headers) || $this->rowIsBlank($headers)) {
            throw new InvalidArgumentException('The file is empty.');
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($headers[0] ?? '')) ?? (string) ($headers[0] ?? '');
        $map = $this->headerMap($headers);

        $hasSplitName = isset($map['first_name'], $map['middle_name'], $map['last_name']);
        $hasFullName = isset($map['name']);

        if (! isset($map['student_id']) || (! $hasSplitName && ! $hasFullName)) {
            throw new InvalidArgumentException('The file must include student_id, first_name, middle_name, and last_name.');
        }

        $summary = ['added' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($rows, $map, &$summary) {
            foreach ($rows as $row) {
                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $studentId = StudentId::normalize($this->cell($row, $map['student_id']));
                $email = isset($map['email']) ? trim((string) $this->cell($row, $map['email'])) : '';
                $contact = isset($map['contact']) ? trim((string) $this->cell($row, $map['contact'])) : '';
                $parts = $this->namePartsFromRow($row, $map);

                if ($studentId === null || ! filled($parts['first_name']) || ! filled($parts['middle_name']) || ! filled($parts['last_name'])) {
                    $summary['skipped']++;
                    continue;
                }

                $existing = StudentWhitelist::query()->where('student_id', $studentId)->first();
                $wasNew = $existing === null;

                $payload = [
                    'first_name' => $parts['first_name'],
                    'middle_name' => $parts['middle_name'],
                    'last_name' => $parts['last_name'],
                ];

                if ($email !== '') {
                    $payload['email'] = $email;
                }
                if ($contact !== '') {
                    $payload['contact_number'] = $contact;
                }

                StudentWhitelist::updateOrCreate(
                    ['student_id' => $studentId],
                    $payload
                );

                if ($wasNew) {
                    $summary['added']++;
                } else {
                    $summary['updated']++;
                }
            }
        });

        return $summary;
    }

    /**
     * @return list<list<string|null>>
     */
    private function readRows(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xls', 'xsl', 'xslx'], true)) {
            throw new InvalidArgumentException('Please save the file as an Excel Workbook or a CSV file, then upload that.');
        }

        if ($ext === 'xlsx') {
            return $this->readXlsx((string) $file->getRealPath());
        }

        return $this->readCsv((string) $file->getRealPath());
    }

    /** @return list<list<string|null>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('The file could not be read.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if ($rows === []) {
            throw new InvalidArgumentException('The file is empty.');
        }

        return $rows;
    }

    /** @return list<list<string|null>> */
    private function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('The Excel file could not be read. Save it as .xlsx or .csv.');
        }

        $shared = [];
        $strings = $zip->getFromName('xl/sharedStrings.xml');
        if ($strings !== false) {
            $xml = @simplexml_load_string($strings);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $text = '';
                    foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $node) {
                        $text .= (string) $node;
                    }
                    $shared[] = $text;
                }
            }
        }

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/[^/]+\.xml$#', $name)) {
                    $sheet = $zip->getFromName($name);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheet === false) {
            throw new InvalidArgumentException('The Excel file has no first sheet to read.');
        }

        $xml = @simplexml_load_string($sheet);
        if (! $xml) {
            throw new InvalidArgumentException('The Excel file could not be parsed.');
        }

        $rows = [];

        foreach ($xml->xpath('//*[local-name()="row"]') ?: [] as $rowNode) {
            $cells = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->xlsxColumnIndex($ref);
                $type = (string) $cell['t'];
                $value = '';

                if ($type === 's') {
                    $value = $shared[(int) $cell->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    foreach ($cell->xpath('.//*[local-name()="t"]') ?: [] as $node) {
                        $value .= (string) $node;
                    }
                } else {
                    $value = (string) $cell->v;
                }

                $cells[$index] = $value;
            }

            if ($cells === []) {
                continue;
            }

            $width = max(array_keys($cells)) + 1;
            $row = array_fill(0, $width, '');
            foreach ($cells as $index => $value) {
                $row[$index] = $value;
            }
            $rows[] = $row;
        }

        if ($rows === []) {
            throw new InvalidArgumentException('The Excel file is empty.');
        }

        return $rows;
    }

    private function xlsxColumnIndex(string $cellRef): int
    {
        preg_match('/^([A-Z]+)/i', $cellRef, $matches);
        $letters = strtoupper($matches[1] ?? 'A');
        $index = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    /** @return array<string, mixed> */
    public function lookupPayload(string $studentId): array
    {
        $student = StudentWhitelist::findByStudentId($studentId);
        if (! $student) {
            return [
                'found' => false,
                'message' => 'This student number is not on the official list.',
            ];
        }

        return [
            'found' => true,
            'student_id' => $student->student_id,
            'name' => $student->name,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'message' => 'Student found on the official list.',
        ];
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int>  $map
     * @return array{first_name: string, middle_name: string, last_name: string}
     */
    private function namePartsFromRow(array $row, array $map): array
    {
        if (isset($map['first_name'], $map['middle_name'], $map['last_name'])) {
            return [
                'first_name' => $this->cell($row, $map['first_name']),
                'middle_name' => $this->cell($row, $map['middle_name']),
                'last_name' => $this->cell($row, $map['last_name']),
            ];
        }

        $full = isset($map['name']) ? $this->cell($row, $map['name']) : '';

        return PersonName::split($full);
    }

    /**
     * @param  list<string|null>  $headers
     * @return array<string, int>
     */
    private function headerMap(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $key = $this->normalizeHeader((string) $header);

            if (in_array($key, ['student id', 'id', 'student no', 'student number', 'studentno', 'stud id'], true)) {
                $map['student_id'] = $index;
            } elseif (in_array($key, ['first name', 'firstname', 'fname', 'given name'], true)) {
                $map['first_name'] = $index;
            } elseif (in_array($key, ['middle name', 'middlename', 'mname', 'middle initial'], true)) {
                $map['middle_name'] = $index;
            } elseif (in_array($key, ['last name', 'lastname', 'lname', 'surname', 'family name'], true)) {
                $map['last_name'] = $index;
            } elseif (in_array($key, ['name', 'student name', 'full name', 'fullname', 'studentname'], true)) {
                $map['name'] = $index;
            } elseif (in_array($key, ['email', 'e mail', 'student email', 'email address'], true)) {
                $map['email'] = $index;
            } elseif (in_array($key, ['contact number', 'contact', 'phone', 'mobile', 'contact no', 'phone number', 'cellphone'], true)) {
                $map['contact'] = $index;
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace(['_', '-'], ' ', $header);
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return $header;
    }

    /** @param  list<string|null>  $row */
    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /** @param  list<string|null>  $row */
    private function cell(array $row, int $index): string
    {
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }
}
