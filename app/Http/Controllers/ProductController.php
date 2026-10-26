<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessProductImportJob;
use App\Mail\LowStockAlertMail;
use App\Models\FileHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use ZipArchive;

class ProductController extends Controller
{
    /**
     * Display products with search, category/stock filters, and analytics
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search across Name, SKU, Barcode, Category, Description
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Min & Max Price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Stock Status Filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->whereBetween('stock', [1, 5]);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock', '>', 5);
            }
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        // KPI Analytics
        $totalProducts = Product::count();
        $totalCategories = Product::distinct('category')->count('category');
        $totalStock = (int) Product::sum('stock');
        $totalInventoryValue = (float) Product::sum(DB::raw('price * stock'));

        $lowStockCount = Product::whereBetween('stock', [1, 5])->count();
        $outOfStock = Product::where('stock', '<=', 0)->count();
        $averagePrice = (float) (Product::avg('price') ?? 0);

        $categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return view('products.index', compact(
            'products',
            'categories',
            'totalProducts',
            'totalCategories',
            'totalStock',
            'totalInventoryValue',
            'lowStockCount',
            'outOfStock',
            'averagePrice'
        ));
    }

    /**
     * Advanced Universal Export (handles custom columns, selected rows, format: xlsx, csv, json)
     */
    public function export(Request $request)
    {
        $query = Product::query();

        // Filter by selected row IDs if provided (Export Selected Rows)
        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids)
                ? $request->selected_ids
                : array_filter(explode(',', $request->selected_ids));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        } else {
            // Apply current search & filter criteria
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('min_price')) {
                $query->where('price', '>=', (float) $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }
            if ($request->filled('stock_status')) {
                if ($request->stock_status === 'out_of_stock') {
                    $query->where('stock', '<=', 0);
                } elseif ($request->stock_status === 'low_stock') {
                    $query->whereBetween('stock', [1, 5]);
                } elseif ($request->stock_status === 'in_stock') {
                    $query->where('stock', '>', 5);
                }
            }
        }

        $products = $query->latest()->get();
        $format = strtolower($request->get('format', 'xlsx'));

        // Handle JSON Export
        if ($format === 'json') {
            FileHistory::create([
                'file_name' => 'products_' . now()->format('Y-m-d_H-i-s') . '.json',
                'type' => 'export',
                'status' => 'completed',
                'total_rows' => $products->count(),
                'success_rows' => $products->count(),
            ]);

            return response()->json($products, 200, [
                'Content-Disposition' => 'attachment; filename="products_' . now()->format('Y-m-d_His') . '.json"',
            ]);
        }

        // Custom Column Selector mapping
        $requestedCols = $request->get('columns', ['id', 'sku', 'name', 'category', 'price', 'stock', 'barcode', 'created_at']);
        if (!is_array($requestedCols)) {
            $requestedCols = explode(',', $requestedCols);
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'products_' . now()->format('Y-m-d_H-i-s') . '.' . $extension;

        FileHistory::create([
            'file_name' => $fileName,
            'type' => 'export',
            'status' => 'completed',
            'total_rows' => $products->count(),
            'success_rows' => $products->count(),
            'failed_rows' => 0,
        ]);

        return (new FastExcel($products))->download($fileName, function ($product) use ($requestedCols) {
            $row = [];
            if (in_array('id', $requestedCols)) $row['ID'] = $product->id;
            if (in_array('sku', $requestedCols)) $row['SKU'] = $product->sku;
            if (in_array('barcode', $requestedCols)) $row['Barcode'] = $product->barcode;
            if (in_array('name', $requestedCols)) $row['Product Name'] = $product->name;
            if (in_array('category', $requestedCols)) $row['Category'] = $product->category;
            if (in_array('price', $requestedCols)) $row['Price'] = (float) $product->price;
            if (in_array('stock', $requestedCols)) $row['Stock Quantity'] = (int) $product->stock;
            if (in_array('description', $requestedCols)) $row['Description'] = $product->description;
            if (in_array('image', $requestedCols)) $row['Image URL'] = $product->image;
            if (in_array('created_at', $requestedCols)) $row['Created At'] = $product->created_at?->format('Y-m-d H:i:s');
            return $row;
        });
    }

    /**
     * Multi-Sheet Excel Export (Sheet 1: Products, Sheet 2: Category Summary, Sheet 3: Low Stock Alert)
     */
    public function exportMultiSheet()
    {
        $allProducts = Product::latest()->get()->map(function ($p) {
            return [
                'ID' => $p->id,
                'SKU' => $p->sku,
                'Barcode' => $p->barcode,
                'Name' => $p->name,
                'Category' => $p->category,
                'Price' => (float) $p->price,
                'Stock' => (int) $p->stock,
                'Inventory Value' => (float) ($p->price * $p->stock),
            ];
        });

        $categorySummary = Product::select('category', DB::raw('count(*) as total_items'), DB::raw('sum(stock) as total_stock'), DB::raw('sum(price * stock) as total_value'))
            ->groupBy('category')
            ->get()
            ->map(function ($c) {
                return [
                    'Category Name' => $c->category,
                    'Total Products' => (int) $c->total_items,
                    'Total Stock Quantity' => (int) $c->total_stock,
                    'Total Valuation ($)' => round((float) $c->total_value, 2),
                ];
            });

        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock', 'asc')->get()->map(function ($p) {
            return [
                'ID' => $p->id,
                'SKU' => $p->sku,
                'Product Name' => $p->name,
                'Category' => $p->category,
                'Current Stock' => (int) $p->stock,
                'Price' => (float) $p->price,
                'Restock Urgency' => $p->stock <= 0 ? 'CRITICAL - OUT OF STOCK' : 'LOW STOCK (<= 5)',
            ];
        });

        $sheets = new SheetCollection([
            'Products Master' => $allProducts,
            'Category Summary' => $categorySummary,
            'Low Stock Alerts' => $lowStockProducts,
        ]);

        $fileName = 'multi_sheet_inventory_' . now()->format('Y-m-d_His') . '.xlsx';

        FileHistory::create([
            'file_name' => $fileName,
            'type' => 'export',
            'status' => 'completed',
            'total_rows' => $allProducts->count() + $categorySummary->count() + $lowStockProducts->count(),
            'success_rows' => $allProducts->count(),
        ]);

        return (new FastExcel($sheets))->download($fileName);
    }

    /**
     * Printable PDF Report View
     */
    public function exportPdf(Request $request)
    {
        $products = Product::latest()->get();
        $totalValuation = Product::sum(DB::raw('price * stock'));

        return view('products.pdf-report', compact('products', 'totalValuation'));
    }

    /**
     * Show import form & mapping
     */
    public function showImportForm()
    {
        return view('products.import');
    }

    /**
     * Step 1: Upload file, detect Excel headers, and render Column Mapping screen
     */
    public function uploadAndMap(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:20480', // up to 20MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->storeAs('imports', 'import_' . Str::random(10) . '_' . $originalName, 'local');

        try {
            $absolutePath = Storage::disk('local')->path($storedPath);
            // Read first row to extract column headers
            $sampleRow = (new FastExcel)->import($absolutePath)->first();

            if (!$sampleRow) {
                Storage::disk('local')->delete($storedPath);
                return redirect()->back()->with('error', 'Uploaded file is empty or headers could not be read.');
            }

            $excelHeaders = array_keys((array) $sampleRow);

            // Auto-detect default column mappings using fuzzy name matching
            $autoMappings = [
                'name' => $this->guessHeaderMatch($excelHeaders, ['name', 'product name', 'item', 'title', 'product_name']),
                'category' => $this->guessHeaderMatch($excelHeaders, ['category', 'group', 'type', 'cat']),
                'price' => $this->guessHeaderMatch($excelHeaders, ['price', 'cost', 'rate', 'unit price', 'unit_price', 'amount']),
                'stock' => $this->guessHeaderMatch($excelHeaders, ['stock', 'qty', 'quantity', 'inventory', 'count']),
                'description' => $this->guessHeaderMatch($excelHeaders, ['description', 'desc', 'details', 'about']),
                'sku' => $this->guessHeaderMatch($excelHeaders, ['sku', 'product_code', 'item_code', 'code']),
                'barcode' => $this->guessHeaderMatch($excelHeaders, ['barcode', 'upc', 'ean', 'ean13']),
                'image' => $this->guessHeaderMatch($excelHeaders, ['image', 'image_url', 'photo', 'picture']),
            ];

            // Sample rows for preview (first 5)
            $previewRows = collect((new FastExcel)->import($absolutePath))->take(5);

            return view('products.column-mapping', [
                'storedPath' => $storedPath,
                'fileName' => $originalName,
                'excelHeaders' => $excelHeaders,
                'autoMappings' => $autoMappings,
                'previewRows' => $previewRows,
            ]);

        } catch (\Throwable $e) {
            Storage::disk('local')->delete($storedPath);
            return redirect()->back()->with('error', 'Error reading Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Step 2: Execute Import with selected column mappings, upsert mode, and queue/sync choice
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'stored_path' => 'required|string',
            'file_name' => 'required|string',
            'upsert_mode' => 'required|in:insert_new,update_existing,skip_duplicates',
            'mapping' => 'required|array',
            'mapping.name' => 'required|string',
            'mapping.category' => 'required|string',
            'mapping.price' => 'required|string',
            'mapping.stock' => 'required|string',
            'processing_mode' => 'nullable|in:queue,sync',
        ]);

        $storedPath = $request->stored_path;
        $fileName = $request->file_name;
        $upsertMode = $request->upsert_mode;
        $mapping = $request->mapping;
        $batchId = 'BATCH-' . strtoupper(Str::random(8));

        // Create FileHistory initial record
        $history = FileHistory::create([
            'file_name' => $fileName,
            'type' => 'import',
            'status' => 'pending',
            'batch_id' => $batchId,
            'upsert_mode' => $upsertMode,
            'total_rows' => 0,
            'current_row' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
        ]);

        $processingMode = $request->get('processing_mode', 'sync');

        // Check if queue processing requested
        if ($processingMode === 'queue') {
            ProcessProductImportJob::dispatch(
                $history->id,
                $storedPath,
                $mapping,
                $upsertMode,
                $batchId
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'file_history_id' => $history->id,
                    'batch_id' => $batchId,
                    'message' => 'Import queued for background processing.',
                ]);
            }

            return redirect()->route('files.history')->with(
                'success',
                "Import for '{$fileName}' has been queued in background (Batch: {$batchId}). You can monitor progress in real-time."
            );
        }

        // Direct synchronous processing
        (new ProcessProductImportJob(
            $history->id,
            $storedPath,
            $mapping,
            $upsertMode,
            $batchId
        ))->handle();

        $history->refresh();

        $msg = "Import completed! Successfully imported/updated {$history->success_rows} products.";
        if ($history->failed_rows > 0) {
            $msg .= " Failed rows: {$history->failed_rows}.";
        }

        return redirect()->route('products.index')->with('success', $msg);
    }

    /**
     * API for Live Real-Time Progress Bar Polling
     */
    public function importProgress($id)
    {
        $history = FileHistory::findOrFail($id);

        return response()->json([
            'id' => $history->id,
            'status' => $history->status,
            'total_rows' => $history->total_rows,
            'current_row' => $history->current_row,
            'success_rows' => $history->success_rows,
            'failed_rows' => $history->failed_rows,
            'percentage' => $history->percentage,
            'failed_file_url' => $history->failed_file_path ? asset($history->failed_file_path) : null,
        ]);
    }

    /**
     * Bulk Product Images ZIP Import
     */
    public function importImagesZip(Request $request)
    {
        $request->validate([
            'images_zip' => 'required|file|mimes:zip|max:51200', // max 50MB
        ]);

        $zipFile = $request->file('images_zip');
        $zip = new ZipArchive();

        if ($zip->open($zipFile->getRealPath()) !== true) {
            return redirect()->back()->with('error', 'Unable to extract the uploaded ZIP archive.');
        }

        $extractPath = Storage::disk('public')->path('products');
        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $matchedCount = 0;
        $unmatchedCount = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Skip directories or hidden files
            if (str_ends_with($filename, '/') || str_starts_with(basename($filename), '.')) {
                continue;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            // Target image identifier without extension (matches SKU or ID)
            $identifier = pathinfo($filename, PATHINFO_FILENAME);

            // Save file
            $newFileName = Str::slug($identifier) . '_' . time() . '.' . $ext;
            copy("zip://" . $zipFile->getRealPath() . "#" . $filename, $extractPath . '/' . $newFileName);

            // Find product matching SKU or ID
            $product = Product::where('sku', $identifier)
                ->orWhere('id', is_numeric($identifier) ? (int)$identifier : 0)
                ->first();

            if ($product) {
                $product->update(['image' => 'storage/products/' . $newFileName]);
                $matchedCount++;
            } else {
                $unmatchedCount++;
            }
        }

        $zip->close();

        return redirect()->route('products.index')->with(
            'success',
            "Bulk image import finished! Successfully matched & updated {$matchedCount} product photos. ({$unmatchedCount} unlinked images stored)."
        );
    }

    /**
     * Trigger Low Stock Alert Email Immediately from Dashboard
     */
    public function triggerLowStockAlert(Request $request)
    {
        $threshold = (int) $request->get('threshold', 5);
        $email = $request->get('email', config('mail.from.address', 'admin@example.com'));

        $lowStockProducts = Product::where('stock', '<=', $threshold)->orderBy('stock', 'asc')->get();
        $count = $lowStockProducts->count();

        if ($count === 0) {
            return redirect()->back()->with('success', 'All products are currently well-stocked. No low stock alert needed.');
        }

        $fileName = 'low_stock_report_' . now()->format('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp_' . $fileName);

        (new FastExcel($lowStockProducts))->export($tempPath, function ($p) {
            return [
                'ID' => $p->id,
                'SKU' => $p->sku,
                'Barcode' => $p->barcode,
                'Product Name' => $p->name,
                'Category' => $p->category,
                'Stock' => $p->stock,
                'Price' => $p->price,
                'Status' => $p->stock <= 0 ? 'OUT OF STOCK' : 'LOW STOCK',
            ];
        });

        try {
            Mail::to($email)->send(new LowStockAlertMail($count, $threshold, $tempPath));
            $statusMsg = "Low stock alert with itemized Excel report sent successfully to {$email} for {$count} products.";
        } catch (\Throwable $e) {
            $statusMsg = "Low stock report generated ({$count} items). Email notice: " . $e->getMessage();
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return redirect()->back()->with('success', $statusMsg);
    }

    /**
     * Download comprehensive sample Excel template
     */
    public function downloadTemplate()
    {
        $templateData = [
            [
                'Name' => 'Apple iPhone 15 Pro',
                'SKU' => 'APL-IPH15-256',
                'Barcode' => '8901234567890',
                'Category' => 'Smartphones',
                'Price' => 999.00,
                'Stock' => 25,
                'Description' => 'Titanium design with A17 Pro chip and 48MP camera',
                'Image' => 'https://via.placeholder.com/300x300.png?text=iPhone+15',
            ],
            [
                'Name' => 'Sony WH-1000XM5 Headphones',
                'SKU' => 'SNY-WHXM5-BLK',
                'Barcode' => '8909876543210',
                'Category' => 'Audio',
                'Price' => 349.99,
                'Stock' => 4, // low stock sample
                'Description' => 'Industry-leading noise canceling wireless headphones',
                'Image' => 'https://via.placeholder.com/300x300.png?text=Headphones',
            ],
        ];

        return (new FastExcel(collect($templateData)))->download('product_import_template.xlsx');
    }

    /**
     * Helper to guess best matching Excel header
     */
    private function guessHeaderMatch(array $excelHeaders, array $aliases): ?string
    {
        foreach ($excelHeaders as $header) {
            $cleaned = strtolower(trim(str_replace(['_', '-'], ' ', $header)));
            foreach ($aliases as $alias) {
                if ($cleaned === strtolower($alias) || str_contains($cleaned, strtolower($alias))) {
                    return $header;
                }
            }
        }
        return null;
    }
}
