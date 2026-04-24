<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculadora_solicitudes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        DB::table('calculadora_solicitudes')->whereNull('uuid')->each(function ($row) {
            DB::table('calculadora_solicitudes')
                ->where('id', $row->id)
                ->update(['uuid' => Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('calculadora_solicitudes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
