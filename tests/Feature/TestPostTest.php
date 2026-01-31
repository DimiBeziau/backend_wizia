<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Posts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TestPostTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** 1. Route: /post (createPublishPost) */
   public function test_create_publish_post_switcher()
{
    Sanctum::actingAs($this->user);

    // On prépare TOUS les paramètres que ton $request->validate exige
    // Utilisation d'une URL d'image existante et publique pour que Make.com puisse la télécharger
    $payload = [
        'post'         => 'Mon super contenu de post',      // required
        'titrePost'    => 'Mon Titre de Post',              // nullable
        'url'          => 'https://placehold.co/600x400/png', // required - Image valide et légère
        'id_post'      => null,                             // nullable
        'datePost'     => now()->toDateTimeString(),        // nullable
        'network'      => 'facebook',                       // required
        'idUser'       => $this->user->id,                  // required
        'now'          => true,                             // nullable (boolean)
        'isValidated'  => 1                                 // required (0 ou 1)
    ];

    /**
     * Correction de l'URL pour éviter le 404 :
     * Comme tu as Route::group(['prefix' => "/post"]), l'URL est /api/post/
     * On utilise route('post.createPublishPost') pour être sûr à 100%
     */
    $response = $this->postJson(route('post.createPublishPost'), $payload);

    // Debug : si ça échoue encore, décommente la ligne suivante pour voir l'erreur
    // $response->dump();

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Publication Facebook envoyée & post mis à jour'
    ]);
}

    /** 2. Routes: /Facebook, /Instagrame, /Linkeding */
   public function test_social_post_routes()
{
    Sanctum::actingAs($this->user);

    // Utilisation des noms définis dans tes Route::post(...)->name('...')
    // Le groupe a le nom 'post.', donc on concatène
    $routeNames = [
        'post.createAndPublishPostPictureFacebook',
        'post.createAndPublishPostInstagramePicture',
        'post.createAndPublishPostPictureLinkeding'
    ];

    foreach ($routeNames as $routeName) {
        $response = $this->postJson(route($routeName), [
            'post' => 'Contenu du post',
            'titrePost' => 'Titre du post',
            'url' => 'https://placehold.co/600x400/png', // Image valide
            'id_post' => null,
            'datePost' => now()->toDateTimeString(),
            'idUser' => $this->user->id,
            'now' => true,
            'isValidated' => 1
        ]);

        if ($response->status() !== 200) {
            dump("Échec sur la route : " . $routeName . " (URL: " . route($routeName) . ")");
            dump($response->json());
        }

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}

    /** 3. Route: /addPosts/{idUser} */
   public function test_add_posts()
{
    // 1. Authentification
    Sanctum::actingAs($this->user);

    // 2. Préparation des données (on respecte le format 'url' et les enums de 'network')
    $payload = [
        'post'          => 'Ceci est un test de contenu manuel',
        'url'           => 'https://www.monsite.com/image.jpg', // Format URL requis
        'titre_post'    => 'Mon Super Titre',
        'date'          => now()->toDateTimeString(),
        'network'       => 'facebook', // facebook, linkedin ou instagram
        'idPostNetwork' => 'ID_TEST_123',
        'isValidated'   => 1
    ];

    // 3. Exécution avec le nom de la route pour éviter le 404
    // Route::post('/addPosts/{idUser}', 'addPosts')->name('addPosts');
    // Le nom complet avec le groupe est 'post.addPosts'
    $response = $this->postJson(route('post.addPosts', ['idUser' => $this->user->id]), $payload);

    // Debug au cas où
    if ($response->status() !== 200) {
        dump("Erreur détectée :");
        dump($response->json());
    }

    // 4. Assertions
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Post ajouté avec succès'
    ]);

    // 5. Vérification en base de données
    $this->assertDatabaseHas('posts', [
        'titrePost' => 'Mon Super Titre',
        'idUser'    => $this->user->id,
        'network'   => 'facebook'
    ]);
}
    /** 4. Route: /SearchPost/{idPost} */
   public function test_search_post_by_id()
{
    Sanctum::actingAs($this->user);

    // 1. Création du post en base
    // On s'assure de remplir les champs nécessaires pour éviter les erreurs SQL
    $post = Posts::create([
        'datePost' => now(),
        'idUser' => $this->user->id,
        'network' => 'linkedin',
        'post' => 'Contenu à rechercher',
        'titrePost' => 'Mon Titre',
        'url' => 'https://test.com',
        'IdpostNetwork' => 'SEARCH_123',
        'isValidated' => 1
    ]);

    // 2. Exécution de la recherche
    // On utilise route('post.SearchPost') car c'est un POST selon ton api.php
    $response = $this->postJson(route('post.SearchPost', ['idPost' => $post->id]));

    // Debug si 404
    if ($response->status() !== 200) {
        dump("URL générée : " . route('post.SearchPost', ['idPost' => $post->id]));
        $response->dump();
    }

    // 3. Assertions
    $response->assertStatus(200);
    
    // On vérifie que le fragment existe dans la clé 'Post' (majuscule comme dans ton code)
    $response->assertJsonFragment([
        'IdpostNetwork' => 'SEARCH_123'
    ]);
}
 

}