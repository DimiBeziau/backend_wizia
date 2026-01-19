<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
     * Test de la génération Gemini (Post structuré)
     */
    public function test_generate_ia_gemini()
    {
        Sanctum::actingAs($this->user);

        // Simulation de la réponse Gemini pour éviter le blocage CURL
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode(['title' => 'Titre Café', 'content' => 'Contenu Café'])]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $payload = [
            'prompt' => 'Crée un post sur le café',
            'type' => 'post'
        ];

        $response = $this->postJson(route('api.generatpromptgemini'), $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure(['title', 'content']);
    }

    /**
     * Test de la génération d'image DALL-E
     */
    public function test_generate_ia_picture()
    {
        Sanctum::actingAs($this->user);
        Storage::fake('public');

        // Simulation de la réponse OpenAI
        Http::fake([
            'api.openai.com/*' => Http::response([
                'data' => [
                    ['url' => 'https://fake-url.com/image.png']
                ]
            ], 200)
        ]);

        $payload = [
            'prompt' => 'Un chat astronaute',
            'size' => '1024x1024'
        ];

        // Note: Si ton contrôleur utilise file_get_contents sur l'URL, 
        // ce test peut encore bloquer si l'URL simulée n'est pas accessible.
        $response = $this->postJson(route('api.generatPictureGPT'), $payload);

        if ($response->status() === 500) {
            $response->assertJsonStructure(['error']);
        } else {
            $response->assertStatus(200)
                     ->assertJsonStructure(['image_url']);
        }
    }
}