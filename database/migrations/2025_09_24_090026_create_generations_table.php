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
            $table->integer('generation_Prompte');// plus coter post
            $table->integer('generation_Picture');// plus coter post
            $table->integer('generation_Newsletter');// plus coter mail 
            $table->integer('nombre_Contact_Newsletter');// plus coter mail 
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
