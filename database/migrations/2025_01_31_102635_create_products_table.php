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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Product name
            $table->string('barcode')->nullable(); // Optional barcode
            $table->string('sku')->nullable(); // Optional barcode
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key for category
            $table->decimal('price', 10, 2); // Product price
            $table->integer('quantity'); // Product quantity
            $table->string('image')->nullable(); // Product image
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->timestamps(); // Created and Updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
