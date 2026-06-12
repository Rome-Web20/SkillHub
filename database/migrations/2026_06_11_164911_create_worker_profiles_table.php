<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            // Ikinokonekta nito ang profile sa ID ng nasa users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Ang mga kategorya ay ibinase natin sa sakop ng iyong SkillHub project doc
            $table->enum('skills_category', ['electrician', 'plumber', 'carpenter', 'painter', 'cleaner', 'technician'])->nullable();

            // Verification system para sa tiwala ng mga kliyente niyo
            $table->boolean('is_verified')->default(false);
            $table->decimal('base_rate', 8, 2)->default(0.00);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};