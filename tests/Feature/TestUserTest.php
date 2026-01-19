<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class TestUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Données par défaut pour éviter les erreurs de contraintes SQL
     */
    private function defaultUserData(): array
    {
        return [
            'activity'    => 'Coiffure',
            'call'        => 'Professionnel',
            'color'       => '#000000',
            'description' => 'Ma description de test', // Correction du NOT NULL description
        ];
    }

    /** ============================
     * getUser()
     * ============================ */
    public function test_get_user_ok()
    {
        $user = User::factory()->create($this->defaultUserData());

        Sanctum::actingAs($user);

        $response = $this->getJson("/users/{$user->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_get_user_not_found()
    {
        $user = User::factory()->create($this->defaultUserData());

        Sanctum::actingAs($user);

        $response = $this->getJson('/users/999');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Utilisateur non trouvé'
            ]);
    }

    /** ============================
     * searchUser()
     * ============================ */
    public function test_search_user_success()
{
  $user = User::factory()->create(array_merge($this->defaultUserData(), [
            'password' => Hash::make('secret123'),
        ]));

        // Obligatoire si la route est dans le groupe middleware auth:sanctum
        Sanctum::actingAs($user);

        $response = $this->postJson('/users/sertchUser', [
            'email' => $user->email,
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['email' => $user->email]);
}

    public function test_search_user_wrong_password()
    {
       $user = User::factory()->create(array_merge($this->defaultUserData(), [
            'password' => Hash::make('secret123'),
        ]));

        // AJOUT : Authentification nécessaire pour accéder à la route
        Sanctum::actingAs($user);

        $response = $this->postJson('/users/sertchUser', [
            'email' => $user->email,
            'password' => 'wrong'
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Email ou mot de passe invalide']);
    }

    /** ============================
     * register()
     * ============================ */
    public function test_register_user()
    {
       $response = $this->postJson('/auth/register', [
        'name' => 'Laura',
        'email' => 'laura@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '0600000000',
        'activity' => 'Coiffure',
        'logo' => 'https://example.com/logo.png',
        'color' => '#000000',
        'description' => 'Test',
        'companyName' => 'Wizia',
        'tone' => 'Professionnel',
        'call' => 'Professionnel',
        'idAbonnement' => 1,
    ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'laura@test.com'
        ]);
    }

    /** ============================
     * login()
     * ============================ */
    public function test_login_ok()
    {
        $user = User::factory()->create(array_merge($this->defaultUserData(), [
            'password' => Hash::make('password123'),
        ]));

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    /** ============================
     * GetAuthenticatedUser()
     * ============================ */
    public function test_get_authenticated_user()
    {
        $user = User::factory()->create($this->defaultUserData());

        Sanctum::actingAs($user);

        $response = $this->postJson('/auth/AuthenticatedUser');

        $response
            ->assertStatus(200)
            ->assertJson([
                'User' => [
                    'id' => $user->id
                ]
            ]);
    }

    /** ============================
     * updateUser()
     * ============================ */
    public function test_update_user()
{
    $user = User::factory()->create($this->defaultUserData());
    Sanctum::actingAs($user);

    $response = $this->putJson("/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => $user->email,
        'phone' => '0600000000', // Correspond à la migration
        'activity' => 'Coiffure',
        'color' => '#FF0000',
        'description' => 'Ma nouvelle description',
        'companyName' => 'Wizia',
        'tone' => 'Professionnel', // Valeur ENUM valide
        'call' => 'Professionnel', // Valeur ENUM valide
    ]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'Utilisateur mis à jour avec succès']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name'
    ]);
}
    /** ============================
     * deleteUser()
     * ============================ */
    public function test_delete_user()
    {
        $user = User::factory()->create($this->defaultUserData());

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/users/{$user->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Utilisateur supprimé avec succès'
            ]);
    }
}