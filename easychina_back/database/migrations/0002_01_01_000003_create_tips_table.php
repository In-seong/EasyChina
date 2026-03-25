<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->text('content');
            $table->string('image_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['PUBLIC', 'PRIVATE'])->default('PUBLIC');
            $table->timestamps();

            $table->index(['tip_category_id', 'status', 'sort_order'], 'idx_tips_category_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
        Schema::dropIfExists('tip_categories');
    }
};
