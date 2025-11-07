<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('mailings', function (Blueprint $table) {
    //         $table->id();
    //         $table->integer('idUser')->unique();
    //         $table->integer('idListeClient')->unique();
    //         $table->timestamps();
    //     });
    // }
     public function up(): void
    {
        Schema::create('mailings', function (Blueprint $table) {
            $table->id();
            $table->integer('idUser');
            $table->integer('idListeClient');
            $table->string('subject');
            $table->text('body');
            $table->boolean('isValidated')->default(false);
            $table->boolean('isPublished')->default(false);
            $table->text('altBody')->nullable();
            $table->string('fromName')->nullable();
            $table->string('fromEmail')->nullable();
            $table->datetime('date')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
};
