<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TestIATest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test réel avec l'API Gemini
     */
    public function test_generate_ia_gemini_real_call()
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'prompt' => 'Dis-moi bonjour en un mot',
            'type' => 'post',
        ];

        $response = $this->postJson(route('api.generatpromptgemini'), $payload);

        // On vérifie que l'API nous répond bien un succès
        $response->assertStatus(200);

        // On vérifie que la structure JSON retournée par ton contrôleur contient bien les données de l'IA
        $response->assertJsonStructure(['title', 'content']);
    }

    /**
     * Test réel avec l'API DALL-E
     */
    public function test_generate_ia_picture_real_call()
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'prompt' => 'Une petite icône bleue',
            'size' => '1024x1024', // DALL-E 3 nécessite au minimum 1024x1024
        ];

        $response = $this->postJson(route('api.generatPictureGPT'), $payload);

        // Si l'appel réussit
        $response->assertStatus(200)
            ->assertJsonStructure(['image_url']);

        // Vérifier que l'image a bien été enregistrée localement
        $responseData = $response->json();
        $urlPath = parse_url($responseData['image_url'], PHP_URL_PATH);
        // Supprime '/storage/' du début s'il est présent pour avoir le chemin relatif au disque public
        $path = preg_replace('#^/storage/#', '', $urlPath);

        // Correction: Utilisation de assertTrue + assets sur le disque réel car assertExists n'existe pas sans fake
        $this->assertTrue(Storage::disk('public')->exists($path), "L'image n'a pas été trouvée sur le disque public : ".$path);
    }
}
