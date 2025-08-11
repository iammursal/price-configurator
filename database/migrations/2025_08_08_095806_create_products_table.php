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
            $table->id();

            $table->string('sku')
                ->unique();

            $table->string('name')
                ->unique();

            $table->string('slug')
                ->unique();

            $table->text('description')
                ->nullable();

            // Speeds up queries by price range or sorting
            $table->unsignedBigInteger('base_price')
                ->index()
                ->comment('In fils (1 KD = 1000 fils)');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
            // Optional: Full-text index for search (requires MySQL/MariaDB fulltext support on InnoDB)
            if ($this->supportsFullText()) {
                $table->fullText(['name', 'description'], 'products_fulltext_name_desc');
            }
        });
    }

    /**
     * Determine if the current database supports full-text indexes.
     */
    private function supportsFullText(): bool
    {
        $connection = Schema::getConnection();

        return $connection->getDriverName() === 'mysql'
            && version_compare(
                $connection->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION),
                '5.6.0',
                '>='
            );
    }

    /**`
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
