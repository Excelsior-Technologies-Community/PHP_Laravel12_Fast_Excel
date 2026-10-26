<?php

namespace Tests\Feature;

use App\Models\FileHistory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFastExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Product Dashboard');
    }

    public function test_template_download(): void
    {
        $response = $this->get('/products/template');
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_multisheet_export(): void
    {
        Product::create([
            'name' => 'Test Laptop',
            'category' => 'Computers',
            'price' => 1200,
            'stock' => 10,
        ]);

        $response = $this->get('/products/export/multisheet');
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_pdf_report_view(): void
    {
        $response = $this->get('/products/export/pdf');
        $response->assertStatus(200);
        $response->assertSee('Inventory Valuation & Stock Report');
    }

    public function test_one_click_rollback(): void
    {
        $batchId = 'BATCH-TEST123';

        $product = Product::create([
            'name' => 'Rollback Product',
            'category' => 'Testing',
            'price' => 50,
            'stock' => 5,
            'import_batch_id' => $batchId
        ]);

        $history = FileHistory::create([
            'file_name' => 'test_import.xlsx',
            'type' => 'import',
            'status' => 'completed',
            'batch_id' => $batchId,
            'total_rows' => 1,
            'success_rows' => 1,
        ]);

        $response = $this->post("/files/history/{$history->id}/rollback");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('file_histories', [
            'id' => $history->id,
            'status' => 'rolled_back'
        ]);
    }
}
