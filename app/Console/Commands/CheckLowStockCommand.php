<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlertMail;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class CheckLowStockCommand extends Command
{
    protected $signature = 'products:check-low-stock {--threshold=5 : Stock threshold} {--email=admin@example.com : Recipient email address}';
    protected $description = 'Scan products table for low stock and send an email alert with Excel sheet attached';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $recipient = $this->option('email');

        $this->info("Scanning inventory for products with stock <= {$threshold}...");

        $lowStockProducts = Product::where('stock', '<=', $threshold)->orderBy('stock', 'asc')->get();
        $count = $lowStockProducts->count();

        if ($count === 0) {
            $this->info("All products are well-stocked. No alert needed.");
            return self::SUCCESS;
        }

        $this->warn("Found {$count} products with low stock!");

        // Export to temp file
        $fileName = 'low_stock_alert_' . now()->format('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp_' . $fileName);

        (new FastExcel($lowStockProducts))->export($tempPath, function ($product) {
            return [
                'ID' => $product->id,
                'SKU' => $product->sku,
                'Barcode' => $product->barcode,
                'Product Name' => $product->name,
                'Category' => $product->category,
                'Current Stock' => $product->stock,
                'Price' => $product->price,
                'Urgency' => $product->stock == 0 ? 'CRITICAL (OUT OF STOCK)' : 'LOW STOCK',
            ];
        });

        $this->info("Exported low stock report to {$tempPath}.");

        // Send email
        try {
            Mail::to($recipient)->send(new LowStockAlertMail($count, $threshold, $tempPath));
            $this->info("Low stock email notification sent to {$recipient} successfully!");
        } catch (\Throwable $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return self::SUCCESS;
    }
}
