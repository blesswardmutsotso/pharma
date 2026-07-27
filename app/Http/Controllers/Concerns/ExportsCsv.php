<?php

namespace App\Http\Controllers\Concerns;

trait ExportsCsv
{
    /**
     * Stream a CSV built from an associative $columns map (db-key => header
     * label) and an iterable of associative rows keyed the same way.
     */
    protected function streamCsvExport(string $filename, array $columns, iterable $rows)
    {
        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_values($columns));

            foreach ($rows as $row) {
                $row = (array) $row;
                fputcsv($handle, array_map(fn ($key) => data_get($row, $key, ''), array_keys($columns)));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
