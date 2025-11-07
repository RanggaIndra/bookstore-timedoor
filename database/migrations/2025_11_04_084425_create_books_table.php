<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->string('title')->index();
            $table->string('isbn')->nullable()->unique();
            $table->string('publisher')->nullable()->index();
            $table->unsignedSmallInteger('publication_year')->index();
            $table->enum('status', ['available','rented','reserved'])->default('available')->index();
            $table->string('store_location')->nullable()->index();

            $table->unsignedInteger('ratings_count')->default(0)->index();
            $table->decimal('ratings_avg', 5, 3)->default(0);

            $table->timestamps();

            $table->index(['author_id','publication_year']);
        });

        Schema::create('book_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['book_id','category_id']);
            $table->index('category_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('books');
    }
};
