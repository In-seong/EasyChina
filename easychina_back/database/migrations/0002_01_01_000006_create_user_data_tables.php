<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'place_id']);
        });

        Schema::create('travel_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_course_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->integer('day_number')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('memo', 300)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('view_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at');

            $table->index(['user_id', 'viewed_at'], 'idx_view_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('view_histories');
        Schema::dropIfExists('travel_course_items');
        Schema::dropIfExists('travel_courses');
        Schema::dropIfExists('bookmarks');
    }
};
