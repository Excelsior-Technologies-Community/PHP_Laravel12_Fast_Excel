<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_histories', function (Blueprint $table) {

            $table->id();

            $table->string('file_name');

            $table->enum('type', [
                'import',
                'export'
            ]);

            $table->integer('total_rows')->default(0);

            $table->integer('success_rows')->default(0);

            $table->integer('failed_rows')->default(0);

            $table->json('errors')->nullable();

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('file_histories');
    }
};