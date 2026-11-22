<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rule_type', 20)->default('weekday'); // weekday, specific_date
            $table->string('day_of_week', 20)->nullable(); // monday, tuesday, wednesday, etc.
            $table->date('specific_date')->nullable();
            $table->string('discount_type', 20)->default('percentage'); // percentage, flat
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
