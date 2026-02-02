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
        Schema::create('limites', function (Blueprint $table) {
            $table->id();
            $table->integer('idAbonnement')->unique();
            $table->enum('nomModule', ['Free', 'Prenium', 'Professionnel']);
            $table->boolean('islimitAbonnement');
            $table->boolean('isprofessionnelle');
            $table->integer('isLimitTexte');
            $table->integer('isLimiteImage');
            $table->timestamps();
        });

        DB::table('limites')->insert([
            [
                'idAbonnement' => 1,
                'nomModule' => 'Free',
                'islimitAbonnement' => true,
                'isprofessionnelle' => false,
                'isLimitTexte' => 10,
                'isLimiteImage' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idAbonnement' => 2,
                'nomModule' => 'Prenium',
                'islimitAbonnement' => true,
                'isprofessionnelle' => false,
                'isLimitTexte' => 15,
                'isLimiteImage' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idAbonnement' => 3,
                'nomModule' => 'Professionnel',
                'islimitAbonnement' => false,
                'isprofessionnelle' => false,
                'isLimitTexte' => 55,
                'isLimiteImage' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limites');
    }
};
