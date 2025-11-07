<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name')->index();
            $table->string('country')->nullable()->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('authors'); }
};
