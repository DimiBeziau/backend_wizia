<?php

use App\Models\Abonnements;
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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->boolean('isFree')->default(false);
            $table->boolean('isPremium')->default(false);
            $table->boolean('isProfessionnel')->default(false);
            $table->decimal('prix', 8, 2)->default(0);
            $table->timestamps();
        });

        // $abonnement = new Abonnements(['isFree' => 1, 'isPremium' => 0, 'isProfessionnel' => 0, 'prix' => 0]);
        // $abonnement->save();
        // $abonnement = new Abonnements(['isFree' => 0, 'isPremium' => 1, 'isProfessionnel' => 0, 'prix' => 17.99 ]);
        // $abonnement->save();
        // $abonnement = new Abonnements(['isFree' => 0, 'isPremium' => 0, 'isProfessionnel' => 1, 'prix' => 29.99 ]);
        // $abonnement->save();

        DB::table('abonnements')->insert([
            [
                'isFree' => true,
                'isPremium' => false,
                'isProfessionnel' => false,
                'prix' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'isFree' => false,
                'isPremium' => true,
                'isProfessionnel' => false,
                'prix' => 17.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'isFree' => false,
                'isPremium' => false,
                'isProfessionnel' => true,
                'prix' => 29.99,
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
        Schema::dropIfExists('abonnements');
    }
};
