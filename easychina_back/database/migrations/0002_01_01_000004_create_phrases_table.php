<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phrase_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('phrases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phrase_category_id')->constrained()->cascadeOnDelete();
            $table->string('text_ko', 300);
            $table->string('text_cn', 300);
            $table->string('pinyin', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['PUBLIC', 'PRIVATE'])->default('PUBLIC');
            $table->timestamps();

            $table->index(['phrase_category_id', 'status', 'sort_order'], 'idx_phrases_category_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phrases');
        Schema::dropIfExists('phrase_categories');
    }
};
