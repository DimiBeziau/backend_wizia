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
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->string('IdUser');
            $table->integer('generation_Prompte');
            $table->integer('generation_Picture');
            $table->integer('generation_Newsletter');
            $table->integer('nombre_Contact_Newsletter');
            $table->string('dateDebut');
            $table->string('dateFin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
