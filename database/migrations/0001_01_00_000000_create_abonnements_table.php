<?php

use App\Models\Abonnements;
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
    Schema::create('abonnements', function (Blueprint $table) {
      $table->id();
      $table->boolean('isFree');
      $table->boolean('isPremium'); // Correction de l'orthographe
      $table->boolean('isProfessionnel'); // Correction de l'orthographe
      $table->timestamps();
    });

    $abonnement = new Abonnements(['isFree' => 1, 'isPremium' => 0, 'isProfessionnel' => 0]);
    $abonnement->save();
    $abonnement = new Abonnements(['isFree' => 0, 'isPremium' => 1, 'isProfessionnel' => 0]);
    $abonnement->save();
    $abonnement = new Abonnements(['isFree' => 0, 'isPremium' => 0, 'isProfessionnel' => 1]);
    $abonnement->save();
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('abonnements');
  }
};
