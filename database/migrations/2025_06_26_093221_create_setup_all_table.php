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
        // 1. Table: templates
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path');
            $table->json('layout_json');
            $table->timestamps();
        });

        // 2. Table: photo_sessions
        Schema::create('photo_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'completed', 'printed'])->default('in_progress');
            $table->timestamps();
        });

        // 3. Table: photos
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('photo_sessions')->onDelete('cascade');
            $table->unsignedTinyInteger('sequence');
            $table->string('file_path');
            $table->boolean('retaken')->default(false);
            $table->timestamps();
        });

        // 4. Table: prints
        Schema::create('prints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('photo_sessions')->onDelete('cascade');
            $table->timestamp('printed_at');
            $table->string('printer_name')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prints');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('photo_sessions');
        Schema::dropIfExists('templates');
    }
};
