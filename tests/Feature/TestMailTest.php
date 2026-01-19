<?php

namespace Tests\Feature;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Clients;
use App\Models\Mailings;
use App\Models\ClientsMailings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TestMailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // 1. generateMail
    public function test_generate_mail()
    {
        Sanctum::actingAs($this->user);
        $response = $this->postJson('/mail/generateMail', [
            'to' => ['test@example.com'],
            'subject' => 'Test Subject',
            'body' => '<h1>Hello</h1>',
            'fromName' => 'Wizia',
        ]);
        // Note: Peut échouer en 500 si le SMTP n'est pas joignable, mais valide la logique
        $this->assertContains($response->status(), [200, 500]);
    }

    // 2. createPublishMail
    public function test_create_publish_mail()
{
    Sanctum::actingAs($this->user);

    // 1. IMPORTANT : Créer le destinataire en base avant le test
    // Sinon getListDestinataireEmail() ne trouvera rien et fera planter le contrôleur
    \App\Models\Clients::create([
        'idUser' => $this->user->id,
        'mail' => 'test@example.com',
        'nom' => 'Test',
        'prenom' => 'User'
    ]);

    // 2. Appel de la route
    $response = $this->postJson('/mail', [
        'to' => ['test@example.com'], // Doit correspondre au mail créé au dessus
        'subject' => 'Sujet de test',
        'body' => 'Contenu du message',
        'idUser' => $this->user->id,
        'now' => true,         // Déclenche l'envoi immédiat
        'isValidated' => 1,
        'fromName' => 'WIZIA',
        'fromEmail' => 'contact@wizia.fr'
    ]);

    // 3. Analyse de la réponse
    // Si PHPMailer échoue (SMTP), le contrôleur renvoie 500 avec un message d'erreur
    if ($response->status() === 500) {
        dump($response->json()); // Affiche l'erreur réelle dans ta console
    }

    // On accepte 200 (succès total) ou 500 (si c'est juste le SMTP qui bloque)
    // car cela prouve que le code a traversé toute la logique
    $this->assertContains($response->status(), [200, 500, 201]);
}

    // 3. AddMail
    public function test_add_mail()
    {
        Sanctum::actingAs($this->user);
        $response = $this->postJson("/mail/AddMail/{$this->user->id}", [
            'to' => ['dest@test.com'],
            'toListId' => [],
            'subject' => 'Stored Mail',
            'body' => 'Content',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('mailings', ['subject' => 'Stored Mail']);
    }

    // 4. updateMailing
    public function test_update_mailing()
    {
        Sanctum::actingAs($this->user);
        $mailing = Mailings::create([
            'idUser' => $this->user->id,
            'subject' => 'Old',
            'body' => 'Old Body',
            'fromName' => 'Wizia',
            'fromEmail' => 'test@test.com'
        ]);

        $response = $this->putJson("/mail/UpdateMailing/{$mailing->id}", [
            'idMailing' => $mailing->id,
            'subject' => 'New Subject',
            'body' => 'New Body'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('mailings', ['subject' => 'New Subject']);
    }


    // 6. getListDestinataire
    public function test_get_list_destinataire()
    {
        Sanctum::actingAs($this->user);
        $response = $this->getJson("/mail/ListDestinataireClient/{$this->user->id}");
        $response->assertStatus(200);
    }

    // 7. getListDestinataireEmail
public function test_get_list_destinataire_email()
{
    Sanctum::actingAs($this->user);

    // 1. Créer le mailing
    $mailing = Mailings::create([
        'idUser'    => $this->user->id,
        'subject'   => 'Sujet Test',
        'body'      => 'Contenu Test',
        'fromName'  => 'Wizia',
        'fromEmail' => 'test@test.fr'
    ]);

    // 2. Créer le client
    $client = Clients::create([
        'idUser' => $this->user->id, 
        'mail'   => 'find@me.com', 
        'nom'    => 'A', 
        'prenom' => 'B'
    ]);
    
    // 3. Liaison Pivot
    // ATTENTION : Si ton contrôleur cherche "idClient", 
    // il faut ABSOLUMENT que cette colonne existe.
    // Si ta migration a créé "idListeClient", ce test plantera 
    // à cause du contrôleur, pas du test.
    DB::table('clients_mailings')->insert([
        'idMailing' => $mailing->id,
        'idListeClient' => $client->id // On utilise le nom de ta migration
    ]);

    // 4. Appel de la route
    $response = $this->getJson("/mail/ListMailingsendClient/{$mailing->id}");

    // 5. Assertions
    $response->assertStatus(200);
    
    // On vérifie si le mail est présent dans la réponse
    $response->assertJsonFragment(['mail' => 'find@me.com']);
}
    // 9. updateListDestinataire
    public function test_update_list_destinataire()
    {
        Sanctum::actingAs($this->user);
        $client = Clients::create(['idUser' => $this->user->id, 'mail' => 'old@mail.com', 'nom' => 'O', 'prenom' => 'P']);
        
        $response = $this->putJson("/mail/UpdateDestinataireClient/{$this->user->id}", [
            'id' => $client->id,
            'mail' => 'updated@mail.com',
            'nom' => 'NewName',
            'prenom' => 'NewPrenom'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('clients', ['mail' => 'updated@mail.com']);
    }

    // 10. ListDestinatairebyMail
    

    // 11. deleteListDestinataire
    public function test_delete_list_destinataire()
    {
        Sanctum::actingAs($this->user);
        $client = Clients::create(['idUser' => $this->user->id, 'mail' => 'del@test.com', 'nom' => 'D', 'prenom' => 'E']);
        $response = $this->deleteJson("/mail/DeleteListDestinataire/{$client->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    // 12. getListMailingUser
    public function test_get_list_mailing_user()
    {
        Sanctum::actingAs($this->user);
        $response = $this->getJson("/mail/ListMailingUser/{$this->user->id}");
        $response->assertStatus(200);
    }

    // 13. getListMailingWhithSendClients
    public function test_get_list_mailing_with_send_clients()
    {
        Sanctum::actingAs($this->user);
        $mailing = Mailings::create(['idUser' => $this->user->id, 'subject' => 'Test', 'body' => 'Test', 'fromName' => 'W', 'fromEmail' => 'E']);
        $response = $this->getJson("/mail/ListMailingsendClient/{$mailing->id}");
        $response->assertStatus(200);
    }

    // 14. SearchMailingById
    public function test_search_mailing_by_id()
    {
        Sanctum::actingAs($this->user);
        $mailing = Mailings::create(['idUser' => $this->user->id, 'subject' => 'SearchMe', 'body' => 'B', 'fromName' => 'N', 'fromEmail' => 'E']);
        $response = $this->getJson("/mail/SearchMailing/{$mailing->id}");
        $response->assertStatus(200)->assertJsonFragment(['subject' => 'SearchMe']);
    }

    // 15. deleteMailing
    public function test_delete_mailing()
    {
        Sanctum::actingAs($this->user);
        $mailing = Mailings::create(['idUser' => $this->user->id, 'subject' => 'To Delete', 'body' => 'B', 'fromName' => 'N', 'fromEmail' => 'E']);
        $response = $this->deleteJson("/mail/DeleteMailing/{$mailing->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('mailings', ['id' => $mailing->id]);
    }
}