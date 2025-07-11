<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $inputFile = storage_path('app/public/signin_logs.csv');
    $outputFile = storage_path('app/public/signin_logs_cleaned.csv');
    $expectedFields = 62; // Based on fixed header

    if (!file_exists($inputFile)) {
        die('Input file not found: ' . $inputFile . "\n");
    }

    $input = new SplFileObject($inputFile);
    $output = new SplFileObject($outputFile, 'w');
    $input->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
    $input->setCsvControl(',', '"', '\\');

    $header = $input->fgetcsv();
    if (count($header) !== $expectedFields) {
        die('Header has ' . count($header) . ' fields, expected ' . $expectedFields . "\n");
    }
    $output->fputcsv($header); // Write header as-is (already fixed)

    while (!$input->eof() && ($row = $input->fgetcsv()) !== false) {
        if ($row !== [null]) { // Skip completely empty rows
            // Pad or truncate row to 62 fields
            $row = array_pad(array_slice($row, 0, $expectedFields), $expectedFields, '');
            // Clean fields: handle quotes and ensure proper CSV formatting
            $cleanedRow = array_map(fn($field) => is_null($field) ? '' : str_replace('"', '""', (string)$field), $row);
            $output->fputcsv($cleanedRow);
        }
    }
    echo "Cleaned CSV created at: $outputFile\n";
} catch (Exception $e) {
    die('Error: ' . $e->getMessage() . "\n");
}
