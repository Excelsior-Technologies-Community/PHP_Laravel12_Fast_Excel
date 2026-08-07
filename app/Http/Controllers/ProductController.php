<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\FileHistory;

class ProductController extends Controller
{
    /**
     * Display products with export/import buttons
     */
    public function index(Request $request)
    {
        $query = Product::query();

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Category Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('category')) {

            $query->where('category', $request->category);
        }

        /*
    |--------------------------------------------------------------------------
    | Minimum Price
    |--------------------------------------------------------------------------
    */

        if ($request->filled('min_price')) {

            $query->where('price', '>=', $request->min_price);
        }

        /*
    |--------------------------------------------------------------------------
    | Maximum Price
    |--------------------------------------------------------------------------
    */

        if ($request->filled('max_price')) {

            $query->where('price', '<=', $request->max_price);
        }

        /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

        $products = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

        $totalProducts = Product::count();

        $totalCategories = Product::distinct('category')->count('category');

        $totalStock = Product::sum('stock');

        $totalInventoryValue = Product::sum(
            DB::raw('price * stock')
        );

        /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

        $highestPrice = Product::orderByDesc('price')->first();

        $lowestPrice = Product::orderBy('price')->first();

        $averagePrice = Product::avg('price');

        $outOfStock = Product::where('stock', 0)->count();

        /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

        $categories = Product::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('products.index', compact(

            'products',

            'categories',

            'totalProducts',

            'totalCategories',

            'totalStock',

            'totalInventoryValue',

            'highestPrice',

            'lowestPrice',

            'averagePrice',

            'outOfStock'
        ));
    }

    /**
     * Export all products to Excel
     */
    public function export(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Min Price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Max Price
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->get();


        FileHistory::create([

            'file_name' => 'products_' . now()->format('Y-m-d_H-i-s') . '.xlsx',

            'type' => 'export',

            'total_rows' => $products->count(),

            'success_rows' => $products->count(),

            'failed_rows' => 0,

            'errors' => null

        ]);


        return (new FastExcel($products))->download(
            'products_' . now()->format('Y-m-d_H-i-s') . '.xlsx',
            function ($product) {

                return [

                    'ID' => $product->id,

                    'Name' => $product->name,

                    'Description' => $product->description,

                    'Category' => $product->category,

                    'Price' => $product->price,

                    'Stock' => $product->stock,

                    'Created At' => $product->created_at->format('Y-m-d H:i:s'),

                ];
            }
        );
    }

    /**
     * Export selected columns only
     */
    public function exportSimple(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->select(
            'name',
            'category',
            'price',
            'stock'
        )->get();

        FileHistory::create([

            'file_name' => 'simple_products.xlsx',

            'type' => 'export',

            'total_rows' => $products->count(),

            'success_rows' => $products->count(),

            'failed_rows' => 0,

            'errors' => null

        ]);


        return (new FastExcel($products))->download('simple_products.xlsx');
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('products.import');
    }

    /**
     * Import products from Excel
     */
    public function import(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Confirm Import From Preview
    |--------------------------------------------------------------------------
    */

        if ($request->has('confirm')) {

            $previewData = session('import_preview');

            if (!$previewData) {

                return redirect()
                    ->route('products.import.form')
                    ->with(
                        'error',
                        'Import session expired. Please upload the file again.'
                    );
            }

            $collection = collect($previewData);

            $fileName = session('import_file_name', 'preview_import.xlsx');
        } else {

            /*
        |--------------------------------------------------------------------------
        | Normal Direct Import
        |--------------------------------------------------------------------------
        */

            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048'
            ]);

            $file = $request->file('file');

            $fileName = $file->getClientOriginalName();

            try {

                $collection = collect(
                    (new FastExcel)
                        ->import($file->path())
                );
            } catch (\Exception $e) {

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Error importing file: ' . $e->getMessage()
                    );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Import Processing
    |--------------------------------------------------------------------------
    */

        try {

            $imported = 0;

            $failed = 0;

            $errors = [];


            foreach ($collection as $index => $row) {

                $validator = Validator::make($row, [

                    'Name' => 'required|string|max:255',

                    'Price' => 'required|numeric|min:0',

                    'Stock' => 'required|integer|min:0',

                    'Category' => 'required|string|max:100',

                    'Description' => 'nullable|string'

                ]);


                if ($validator->fails()) {

                    $failed++;

                    $errors[] = [

                        'row' => $index + 1,

                        'message' => $validator->errors()->all()

                    ];

                    continue;
                }


                Product::create([

                    'name' => $row['Name'],

                    'description' => $row['Description'] ?? '',

                    'price' => $row['Price'],

                    'stock' => $row['Stock'],

                    'category' => $row['Category']

                ]);


                $imported++;
            }


            /*
        |--------------------------------------------------------------------------
        | Store File History
        |--------------------------------------------------------------------------
        */

            FileHistory::create([

                'file_name' => $fileName,

                'type' => 'import',

                'total_rows' => $imported + $failed,

                'success_rows' => $imported,

                'failed_rows' => $failed,

                'errors' => $errors

            ]);


            /*
        |--------------------------------------------------------------------------
        | Clear Preview Session
        |--------------------------------------------------------------------------
        */

            session()->forget([
                'import_preview',
                'import_file_name'
            ]);


            /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

            $message = "Successfully imported {$imported} products.";

            if ($failed > 0) {

                $message .= " Failed: {$failed} products.";
            }


            return redirect()

                ->route('products.index')

                ->with('success', $message);
        } catch (\Exception $e) {

            return redirect()

                ->back()

                ->with(

                    'error',

                    'Error importing file: ' . $e->getMessage()

                );
        }
    }
    /**
     * Export as CSV
     */

    public function exportCsv(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->get();


        FileHistory::create([

            'file_name' => 'products.csv',

            'type' => 'export',

            'total_rows' => $products->count(),

            'success_rows' => $products->count(),

            'failed_rows' => 0,

            'errors' => null

        ]);


        return (new FastExcel($products))->download(
            'products.csv',
            function ($product) {

                return [

                    'Name' => $product->name,

                    'Category' => $product->category,

                    'Price' => $product->price,

                    'Stock' => $product->stock,

                    'Description' => $product->description,

                ];
            }
        );
    }

    /**
     * Download sample Excel template for import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Name' => 'Sample Product',
            'Description' => 'Sample Description',
            'Price' => 99.99,
            'Stock' => 10,
            'Category' => 'Electronics'
        ];

        return (new FastExcel(collect([$headers])))->download('import_template.xlsx');
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048'
        ]);

        try {

            $data = collect(
                (new FastExcel)
                    ->import($request->file('file')->path())
            );

            if ($data->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'The uploaded file is empty.');
            }

            // Store complete imported data and filename in session
            session([
                'import_preview' => $data->toArray(),
                'import_file_name' => $request->file('file')->getClientOriginalName()
            ]);

            return view(
                'products.import-preview',
                [
                    'products' => $data->take(10)
                ]
            );
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Error reading file: ' . $e->getMessage()
                );
        }
    }
}
