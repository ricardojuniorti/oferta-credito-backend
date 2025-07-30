<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->timestamp('data_oferta')->nullable()->after('valor_a_pagar');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            //
        });
    }
};
