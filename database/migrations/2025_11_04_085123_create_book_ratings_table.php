<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('book_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1..10
            $table->string('rater_fingerprint', 191)->index();
            $table->timestamps();

            $table->index(['book_id','created_at']);
        });

        Schema::create('rater_cooldowns', function (Blueprint $table) {
            $table->string('rater_fingerprint',191)->primary();
            $table->timestamp('last_rating_at')->nullable()->index();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rater_cooldowns');
        Schema::dropIfExists('book_ratings');
    }
};
