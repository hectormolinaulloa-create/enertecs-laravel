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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->string('mandante')->nullable()->after('cliente');
            $table->string('region', 10)->nullable()->after('año');
            $table->string('comuna')->nullable()->after('region');
            $table->string('direccion')->nullable()->after('comuna');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['mandante', 'region', 'comuna', 'direccion']);
        });
    }
};
