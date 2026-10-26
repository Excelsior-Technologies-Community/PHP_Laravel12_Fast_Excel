<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name')->index();
            $table->string('barcode')->nullable()->after('sku')->index();
            $table->string('image')->nullable()->after('category');
            $table->string('import_batch_id')->nullable()->after('image')->index();
        });

        Schema::table('file_histories', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('type'); // pending, processing, completed, failed, rolled_back
            $table->string('batch_id')->nullable()->after('status')->index();
            $table->string('failed_file_path')->nullable()->after('errors');
            $table->integer('current_row')->default(0)->after('failed_rows');
            $table->string('upsert_mode')->nullable()->after('failed_file_path'); // insert_new, update_existing, skip_duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['sku']);
            $table->dropIndex(['barcode']);
            $table->dropIndex(['import_batch_id']);
            $table->dropColumn(['sku', 'barcode', 'image', 'import_batch_id']);
        });

        Schema::table('file_histories', function (Blueprint $table) {
            $table->dropIndex(['batch_id']);
            $table->dropColumn(['status', 'batch_id', 'failed_file_path', 'current_row', 'upsert_mode']);
        });
    }
};
