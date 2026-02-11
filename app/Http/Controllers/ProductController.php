<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display products with export/import buttons
     */
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    /**
     * Export all products to Excel
     */
    public function export()
    {
        $products = Product::all();
        
        return (new FastExcel($products))->download('products_' . date('Y-m-d') . '.xlsx', function ($product) {
            return [
                'ID' => $product->id,
                'Name' => $product->name,
                'Description' => $product->description,
                'Price' => $product->price,
                'Stock' => $product->stock,
                'Category' => $product->category,
                'Created At' => $product->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Export selected columns only
     */
    public function exportSimple()
    {
        $products = Product::select('name', 'price', 'stock', 'category')->get();
        
        return (new FastExcel($products))->download('simple_products_' . date('Y-m-d') . '.xlsx');
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
    public function exportCsv()
    {
        $products = Product::all();
        
        return (new FastExcel($products))->download('products_' . date('Y-m-d') . '.csv', function ($product) {
            return [
                'Name' => $product->name,
                'Price' => $product->price,
                'Stock' => $product->stock,
                'Category' => $product->category
            ];
        });
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