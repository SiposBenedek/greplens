<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->timestamp('scanned_at')->nullable()->after('metadata');
            $table->index(['project_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'scanned_at']);
            $table->dropColumn('scanned_at');
        });
    }
};
