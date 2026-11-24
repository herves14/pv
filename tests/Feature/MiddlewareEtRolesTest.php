<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Vérifie le premier niveau de sécurité : les middlewares.
 *  - « auth »               : un visiteur est renvoyé vers la connexion ;
 *  - « role »               : chaque espace est fermé aux autres rôles (RG21) ;
 *  - « EnsureCompteActif »  : un compte désactivé est déconnecté (RG05).
 */
class MiddlewareEtRolesTest extends TestCase
{
    use RefreshDatabase;

    /** Pour chaque adresse : les rôles autorisés. */
    public static function espaces(): array
    {
        return [
            'tableau de bord admin' => ['/admin', ['admin']],
            'comptes' => ['/admin/utilisateurs', ['admin']],
            'filières (gestion)' => ['/admin/filieres', ['admin']],
            'promotions (gestion)' => ['/admin/promotions', ['admin']],
            'années (gestion)' => ['/admin/annees', ['admin']],
            'semestres (gestion)' => ['/admin/semestres', ['admin']],
            'consultation' => ['/consultation', ['admin', 'chef_departement']],
            'espace chef' => ['/departement', ['chef_departement']],
            'espace expédition' => ['/expedition', ['expedition']],
            'ajouter un PV' => ['/pv/ajouter', ['admin', 'chef_departement', 'expedition']],
            'scanner un PV' => ['/pv/scanner', ['admin', 'chef_departement', 'expedition']],
            'recherche' => ['/recherche', ['admin', 'chef_departement', 'expedition']],
            'mot de passe' => ['/compte/mot-de-passe', ['admin', 'chef_departement', 'expedition']],
        ];
    }

    private function compte(string $role): User
    {
        return match ($role) {
            'admin' => User::factory()->admin()->create(),
            'chef_departement' => User::factory()->chef()->create(),
            'expedition' => User::factory()->create(),
        };
    }

    #[DataProvider('espaces')]
    public function test_un_visiteur_est_renvoye_vers_la_connexion(string $url): void
    {
        $this->get($url)->assertRedirect('/login');
    }

    #[DataProvider('espaces')]
    public function test_chaque_espace_n_accepte_que_ses_roles(string $url, array $autorises): void
    {
        foreach (['admin', 'chef_departement', 'expedition'] as $role) {
            $reponse = $this->actingAs($this->compte($role))->get($url);

            if ($role === 'chef_departement' && $url === '/consultation') {
                $reponse->assertRedirect('/departement'); // un chef va directement à sa filière
            } elseif (in_array($role, $autorises, true)) {
                $reponse->assertOk();
            } else {
                $reponse->assertForbidden();
            }
        }
    }

    public function test_un_compte_desactive_en_cours_de_session_est_deconnecte(): void
    {
        $chef = User::factory()->chef()->create();
        $this->actingAs($chef)->get('/departement')->assertOk();

        $chef->update(['actif' => false]);

        $this->get('/departement')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_un_chef_ne_peut_pas_ouvrir_une_autre_filiere_par_l_adresse(): void
    {
        $grh = Filiere::factory()->create();
        $stat = Filiere::factory()->create();
        $chef = User::factory()->chef($grh)->create();

        $this->actingAs($chef)->get(route('consultation.promotions', $grh))->assertOk();
        $this->actingAs($chef)->get(route('consultation.promotions', $stat))->assertForbidden();
    }

    public function test_un_utilisateur_connecte_ne_revoit_pas_la_page_de_connexion(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect('/');
    }

    public function test_la_connexion_est_limitee_a_5_tentatives_par_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'x@eneam.bj', 'password' => 'faux']);
        }

        $this->post('/login', ['email' => 'x@eneam.bj', 'password' => 'faux'])->assertStatus(429);
    }

    public function test_le_menu_ne_montre_que_les_liens_du_role(): void
    {
        $this->actingAs(User::factory()->chef()->create())->get('/departement')
            ->assertSee('Ma filière')
            ->assertDontSee('Utilisateurs')
            ->assertDontSee('Tableau de bord');

        $this->actingAs(User::factory()->admin()->create())->get('/admin')
            ->assertSee('Utilisateurs')
            ->assertSee('Filières');
    }
}
