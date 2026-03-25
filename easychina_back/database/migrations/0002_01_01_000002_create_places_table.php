<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_ko', 100);
            $table->string('name_cn', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('pinyin', 200)->nullable();
            $table->string('address_ko', 300)->nullable();
            $table->string('address_cn', 300);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('phone', 30)->nullable();
            $table->string('business_hours', 200)->nullable();
            $table->string('closed_days', 100)->nullable();
            $table->integer('price_min')->nullable();
            $table->integer('price_max')->nullable();
            $table->boolean('pay_alipay')->default(true);
            $table->boolean('pay_wechat')->default(true);
            $table->boolean('pay_cash')->default(false);
            $table->boolean('has_english_menu')->default(false);
            $table->tinyInteger('restroom_rating')->nullable();
            $table->text('description')->nullable();
            $table->text('tips')->nullable();
            $table->integer('recommendation_score')->default(50);
            $table->decimal('rating', 2, 1)->nullable();
            $table->enum('status', ['PUBLIC', 'PRIVATE', 'DRAFT'])->default('DRAFT');
            $table->integer('view_count')->default(0);
            $table->integer('bookmark_count')->default(0);
            $table->timestamps();

            $table->index(['city_id', 'category_id', 'status'], 'idx_places_city_category');
            $table->index(['status', 'recommendation_score'], 'idx_places_status_recommendation');
            $table->index(['latitude', 'longitude'], 'idx_places_coordinates');
        });

        Schema::create('place_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->string('image_url', 500);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->timestamps();
        });

        Schema::create('place_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['place_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('place_images');
        Schema::dropIfExists('places');
    }
};
