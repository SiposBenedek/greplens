<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('check_id');
            $table->string('file_path', 1000);
            $table->unsignedInteger('start_line');
            $table->unsignedInteger('start_col');
            $table->unsignedInteger('end_line');
            $table->unsignedInteger('end_col');
            $table->text('message');
            $table->string('severity', 20)->default('WARNING');
            $table->text('code_snippet')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'severity']);
            $table->index(['project_id', 'check_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
