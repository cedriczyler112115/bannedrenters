<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banned', function (Blueprint $table) {
            $table->string('license')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('banned')->whereNull('license')->update(['license' => '']);

        Schema::table('banned', function (Blueprint $table) {
            $table->string('license')->nullable(false)->change();
        });
    }
};
