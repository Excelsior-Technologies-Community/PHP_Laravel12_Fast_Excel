<?php

namespace App\Jobs;

use App\Models\FileHistory;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;

class ProcessProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes for massive datasets

    public function __construct(
        public int $fileHistoryId,
        public string $filePath,
        public array $columnMapping,
        public string $upsertMode = 'insert_new',
        public ?string $batchId = null
    ) {}

    public function handle(): void
    {
        $history = FileHistory::find($this->fileHistoryId);
        if (!$history) {
            return;
        }

        $history->update([
            'status' => 'processing',
            'batch_id' => $this->batchId,
            'upsert_mode' => $this->upsertMode
        ]);

        $absolutePath = Storage::disk('local')->path($this->filePath);
        if (!file_exists($absolutePath)) {
            $history->update(['status' => 'failed', 'errors' => [['message' => 'Uploaded file not found on disk']]]);
            return;
        }

        try {
            $importedCount = 0;
            $failedCount = 0;
            $errors = [];
            $failedRowsData = [];

            // Read the dataset using FastExcel generator for ultra-low memory
            $collection = (new FastExcel)->import($absolutePath);
            $totalRows = count($collection);
            $history->update(['total_rows' => $totalRows]);

            $chunkCount = 0;

            foreach ($collection as $index => $row) {
                $rowNumber = $index + 1;
                $chunkCount++;

                // Map row according to column mapping
                $mappedData = [
                    'name' => $this->getMappedValue($row, 'name'),
                    'category' => $this->getMappedValue($row, 'category'),
                    'price' => $this->getMappedValue($row, 'price'),
                    'stock' => $this->getMappedValue($row, 'stock'),
                    'description' => $this->getMappedValue($row, 'description'),
                    'sku' => $this->getMappedValue($row, 'sku'),
                    'barcode' => $this->getMappedValue($row, 'barcode'),
                    'image' => $this->getMappedValue($row, 'image'),
                ];

                // Validate row
                $validator = Validator::make($mappedData, [
                    'name' => 'required|string|max:255',
                    'category' => 'required|string|max:100',
                    'price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'description' => 'nullable|string',
                    'sku' => 'nullable|string|max:50',
                    'barcode' => 'nullable|string|max:50',
                    'image' => 'nullable|string'
                ]);

                if ($validator->fails()) {
                    $failedCount++;
                    $errorMsgs = implode('; ', $validator->errors()->all());
                    $errors[] = ['row' => $rowNumber, 'message' => $errorMsgs];

                    $failedRow = (array) $row;
                    $failedRow['Validation_Error_Reason'] = $errorMsgs;
                    $failedRowsData[] = $failedRow;
                    continue;
                }

                // Check for duplicate / existing product
                $existingQuery = Product::query();
                if (!empty($mappedData['sku'])) {
                    $existingQuery->where('sku', $mappedData['sku']);
                } else {
                    $existingQuery->where('name', $mappedData['name']);
                }
                $existingProduct = $existingQuery->first();

                if ($existingProduct) {
                    if ($this->upsertMode === 'skip_duplicates') {
                        // Skip without error
                        continue;
                    } elseif ($this->upsertMode === 'update_existing') {
                        // Update existing product
                        $existingProduct->update([
                            'price' => $mappedData['price'],
                            'stock' => $mappedData['stock'],
                            'category' => $mappedData['category'],
                            'description' => $mappedData['description'] ?? $existingProduct->description,
                            'barcode' => $mappedData['barcode'] ?? $existingProduct->barcode,
                            'image' => $mappedData['image'] ?? $existingProduct->image,
                            'import_batch_id' => $this->batchId,
                        ]);
                        $importedCount++;
                        continue;
                    } else { // insert_new only
                        $failedCount++;
                        $errorMsg = 'Duplicate product detected (SKU/Name matches existing record)';
                        $errors[] = ['row' => $rowNumber, 'message' => $errorMsg];
                        $failedRow = (array) $row;
                        $failedRow['Validation_Error_Reason'] = $errorMsg;
                        $failedRowsData[] = $failedRow;
                        continue;
                    }
                }

                // Create new product
                $sku = !empty($mappedData['sku']) ? $mappedData['sku'] : Product::generateSku($mappedData['category']);
                $barcode = !empty($mappedData['barcode']) ? $mappedData['barcode'] : Product::generateBarcode();

                Product::create([
                    'name' => $mappedData['name'],
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'description' => $mappedData['description'] ?? '',
                    'price' => $mappedData['price'],
                    'stock' => $mappedData['stock'],
                    'category' => $mappedData['category'],
                    'image' => $mappedData['image'] ?? null,
                    'import_batch_id' => $this->batchId,
                ]);

                $importedCount++;

                // Periodically update live progress for frontend polling
                if ($chunkCount % 50 === 0 || $rowNumber === $totalRows) {
                    $history->update([
                        'current_row' => $rowNumber,
                        'success_rows' => $importedCount,
                        'failed_rows' => $failedCount,
                    ]);
                }
            }

            // If there are failed rows, export them to a dedicated downloadable file
            $failedFilePath = null;
            if (!empty($failedRowsData)) {
                $failedFileName = 'failed_rows_' . ($this->batchId ?? now()->timestamp) . '.xlsx';
                $relativeFailedPath = 'failed_imports/' . $failedFileName;
                $absoluteFailedPath = Storage::disk('public')->path($relativeFailedPath);

                // Ensure directory exists
                if (!file_exists(dirname($absoluteFailedPath))) {
                    mkdir(dirname($absoluteFailedPath), 0755, true);
                }

                (new FastExcel(collect($failedRowsData)))->export($absoluteFailedPath);
                $failedFilePath = 'storage/' . $relativeFailedPath;
            }

            $history->update([
                'status' => 'completed',
                'current_row' => $totalRows,
                'total_rows' => $totalRows,
                'success_rows' => $importedCount,
                'failed_rows' => $failedCount,
                'errors' => array_slice($errors, 0, 100), // store top 100 in DB
                'failed_file_path' => $failedFilePath
            ]);

        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'errors' => [['message' => $e->getMessage()]]
            ]);
        } finally {
            // Optional cleanup of temp upload file
            // Storage::disk('local')->delete($this->filePath);
        }
    }

    private function getMappedValue(array $row, string $field): mixed
    {
        $mappedHeader = $this->columnMapping[$field] ?? null;
        if ($mappedHeader && array_key_exists($mappedHeader, $row)) {
            $val = trim((string) $row[$mappedHeader]);
            return $val !== '' ? $val : null;
        }

        // Fallback exact match or case-insensitive match
        foreach ($row as $header => $val) {
            if (strtolower(trim($header)) === strtolower($field)) {
                $v = trim((string) $val);
                return $v !== '' ? $v : null;
            }
        }

        return null;
    }
}
