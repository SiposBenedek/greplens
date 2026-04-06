<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->text('yaml_content')->nullable()->after('description');
            $table->dropColumn('rule_data');
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->text('rule_data')->nullable()->after('description');
            $table->dropColumn('yaml_content');
        });
    }
};
