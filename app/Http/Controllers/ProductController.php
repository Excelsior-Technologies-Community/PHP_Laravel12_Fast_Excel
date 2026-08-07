<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

        return (new FastExcel($products))
            ->download('simple_products.xlsx');
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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $collection = (new FastExcel)->import($request->file('file')->path());

            $imported = 0;
            $failed = 0;
            $errors = [];

            foreach ($collection as $row) {
                // Validate row data
                $validator = Validator::make($row, [
                    'Name' => 'required|string|max:255',
                    'Price' => 'required|numeric|min:0',
                    'Stock' => 'required|integer|min:0',
                    'Category' => 'required|string|max:100',
                    'Description' => 'nullable|string'
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = "Row with Name '{$row['Name']}' failed: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Create product
                Product::create([
                    'name' => $row['Name'],
                    'description' => $row['Description'] ?? '',
                    'price' => $row['Price'],
                    'stock' => $row['Stock'],
                    'category' => $row['Category']
                ]);

                $imported++;
            }

            $message = "Successfully imported {$imported} products.";
            if ($failed > 0) {
                $message .= " Failed: {$failed} products.";
            }

            return redirect()->route('products.index')
                ->with('success', $message)
                ->with('errors', $errors);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing file: ' . $e->getMessage());
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
}
