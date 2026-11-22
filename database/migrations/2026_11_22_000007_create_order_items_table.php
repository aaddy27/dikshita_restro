<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained();
            $table->foreignId('item_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->string('variant_name')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('kot_status', 20)->default('pending'); // pending, printed
            $table->string('added_by_type', 20)->default('customer'); // customer, waiter, cashier
            $table->unsignedBigInteger('added_by_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
