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
        Schema::create('optional_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('optional_id')->constrained();
            $table->foreignId('requires_optional_id')->nullable()->constrained('optionals');
            $table->foreignId('excludes_optional_id')->nullable()->constrained('optionals');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('optional_compatibilities');
    }
};
