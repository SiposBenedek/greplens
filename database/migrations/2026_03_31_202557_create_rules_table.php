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
        Schema::create('rule_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('rule_groups')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('rule_data')->nullable();
            $table->text('test_code')->nullable();
            $table->boolean('is_active')->default(true);


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
        Schema::dropIfExists('rule_groups');
    }
};
