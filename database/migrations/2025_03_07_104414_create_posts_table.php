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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->datetime('datePost');
            $table->integer('idUser');
            $table->boolean('isValidated')->default(false);
            $table->boolean('isPublished')->default(false);
            $table->enum('network', ['facebook', 'linkedin', 'instagram']);
            $table->string('url', 500)->nullable();
            $table->string('titrePost', 1000)->nullable();
            $table->string('post', 1000);
            $table->string('IdpostNetwork');
            $table->string('postLikeNetwork')->nullable();
            $table->string('postCommentaireNetwork')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
