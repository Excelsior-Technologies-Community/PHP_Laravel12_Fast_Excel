<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'description',
        'price',
        'stock',
        'category',
        'image',
        'import_batch_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer'
    ];

    /**
     * Automatically generate SKU if empty
     */
    public static function generateSku(string $category = 'GEN'): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category), 0, 4)) ?: 'PRD';
        do {
            $sku = $prefix . '-' . strtoupper(Str::random(6));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Automatically generate an EAN-13 style barcode if empty
     */
    public static function generateBarcode(): string
    {
        do {
            $number = '890' . mt_rand(100000000, 999999999);
        } while (static::where('barcode', $number)->exists());

        return $number;
    }

    /**
     * Check if product is low stock
     */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock <= $threshold;
    }
}
