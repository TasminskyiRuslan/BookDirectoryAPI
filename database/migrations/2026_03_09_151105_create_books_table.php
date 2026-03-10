<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations of the books table creation.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->date('publication_date')->index()->nullable();
            $table->timestamps();

            $table->fullText(['title', 'description'], 'books_search_index');
        });
    }

    /**
     * Reverse the migrations of the books table creation.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
