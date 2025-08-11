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
            $table->string('name'); // human-readable label
            // $table->enum('rule_type', ['attribute', 'total', 'user_type']);

            // Discount action
            $table->enum('discount_type', ['percent', 'amount']); // percent = % off, amount = fixed KD off
            $table->decimal('discount_value', 8, 4); // e.g., 5.00 => 5% if percent, or 10.00 KD if amount

            // Conditions (nullable depending on rule_type)
            $table->foreignId('attribute_option_id')->nullable()->constrained('attribute_options')->nullOnDelete(); // for attribute-based
            $table->enum('comparator', ['>', '>=', '=', '<=', '<', '!=', '<>'])->nullable(); // for total-based
            $table->unsignedBigInteger('threshold')->nullable(); // for total-based
            $table->enum('user_type', ['normal', 'company'])->nullable(); // for user-type-based

            // Scoping & control
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(100); // lower = earlier; if equal, order by creation time (id)
            $table->boolean('stop_further')->default(false); // if true, no further discount rules will be applied after this rule
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index('priority');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
