<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('settlement_key');
            $table->enum('zone_type', ['URBANO', 'RURAL']);
            $table->foreignId('settlement_type_id')->constrained('settlement_types');
            $table->foreignId('municipality_id')->constrained('municipalities');
            $table->foreignId('locality_id')->constrained('localities');
            $table->foreignId('zip_code_id')->constrained('zip_codes');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settlements');
    }
};
