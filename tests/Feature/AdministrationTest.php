<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Espace administrateur : comptes (UC03) et référentiels (UC04). */
class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function nouveauChef(Filiere $filiere, string $email): array
    {
        return [
            'name' => 'Chef '.$filiere->code,
            'email' => $email,
            'role' => 'chef_departement',
            'filiere_id' => $filiere->id,
            'password' => 'motdepasse8',
            'password_confirmation' => 'motdepasse8',
        ];
    }

    public function test_l_admin_cree_un_compte_de_chef(): void
    {
        $grh = Filiere::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.utilisateurs.store'), $this->nouveauChef($grh, 'chef@eneam.bj'))
            ->assertRedirect(route('admin.utilisateurs.index'));

        $chef = User::where('email', 'chef@eneam.bj')->sole();
        $this->assertTrue($chef->dirige($grh));
        $this->assertTrue($chef->actif);
    }

    public function test_un_chef_doit_avoir_une_filiere_rg03(): void
    {
        $donnees = $this->nouveauChef(Filiere::factory()->create(), 'a@eneam.bj');
        unset($donnees['filiere_id']);

        $this->actingAs($this->admin)->post(route('admin.utilisateurs.store'), $donnees)
            ->assertSessionHasErrors('filiere_id');
    }

    public function test_une_filiere_n_a_qu_un_chef_actif_rg04(): void
    {
        $grh = Filiere::factory()->create();
        User::factory()->chef($grh)->create();

        $this->actingAs($this->admin)->post(route('admin.utilisateurs.store'), $this->nouveauChef($grh, 'b@eneam.bj'))
            ->assertSessionHasErrors('filiere_id');
    }

    public function test_on_ne_reactive_pas_un_chef_si_la_filiere_en_a_deja_un_rg04(): void
    {
        $grh = Filiere::factory()->create();
        $ancien = User::factory()->chef($grh)->inactif()->create();
        User::factory()->chef($grh)->create();

        $this->actingAs($this->admin)->patch(route('admin.utilisateurs.activation', $ancien))
            ->assertSessionHas('erreur');
        $this->assertFalse($ancien->fresh()->actif);
    }

    public function test_l_admin_ne_peut_ni_se_desactiver_ni_perdre_son_role_rg06(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.utilisateurs.activation', $this->admin))
            ->assertSessionHas('erreur');
        $this->assertTrue($this->admin->fresh()->actif);

        $this->actingAs($this->admin)->put(route('admin.utilisateurs.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'expedition',
        ])->assertSessionHasErrors('role');
    }

    public function test_desactiver_puis_reactiver_un_compte(): void
    {
        $agent = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.utilisateurs.activation', $agent));
        $this->assertFalse($agent->fresh()->actif);

        $this->actingAs($this->admin)->patch(route('admin.utilisateurs.activation', $agent));
        $this->assertTrue($agent->fresh()->actif);
    }

    public function test_ajouter_une_filiere_rg23(): void
    {
        $this->actingAs($this->admin)->post(route('admin.filieres.store'), ['code' => 'gtl', 'libelle' => 'Gestion des transports et logistique'])
            ->assertRedirect(route('admin.filieres.index'));

        $this->assertDatabaseHas('filieres', ['code' => 'GTL']); // mis en majuscules

        $this->actingAs($this->admin)->post(route('admin.filieres.store'), ['code' => 'GTL', 'libelle' => 'Doublon'])
            ->assertSessionHasErrors('code');
    }

    public function test_une_filiere_avec_des_promotions_ne_se_supprime_pas_rg11(): void
    {
        $filiere = Filiere::factory()->create();
        Promotion::factory()->create(['filiere_id' => $filiere->id]);

        $this->actingAs($this->admin)->delete(route('admin.filieres.destroy', $filiere))->assertSessionHas('erreur');
        $this->assertModelExists($filiere);
    }

    public function test_format_de_l_annee_academique_rg09(): void
    {
        $this->actingAs($this->admin)->post(route('admin.annees.store'), ['libelle' => '2023-2025'])->assertSessionHasErrors('libelle');
        $this->actingAs($this->admin)->post(route('admin.annees.store'), ['libelle' => '2023/2024'])->assertSessionHasErrors('libelle');
        $this->actingAs($this->admin)->post(route('admin.annees.store'), ['libelle' => '2023-2024'])->assertSessionHasNoErrors();
    }

    public function test_un_non_admin_ne_peut_rien_envoyer_a_l_administration(): void
    {
        $chef = User::factory()->chef()->create();

        $this->actingAs($chef)->post(route('admin.filieres.store'), ['code' => 'X', 'libelle' => 'X'])->assertForbidden();
        $this->actingAs($chef)->post(route('admin.utilisateurs.store'), [])->assertForbidden();
        $this->assertDatabaseMissing('filieres', ['code' => 'X']);
    }

    public function test_toutes_les_pages_d_administration_s_affichent(): void
    {
        $filiere = Filiere::factory()->create();
        $promotion = Promotion::factory()->create(['filiere_id' => $filiere->id]);
        $chef = User::factory()->chef($filiere)->create();

        foreach ([
            route('admin.dashboard'),
            route('admin.utilisateurs.index'), route('admin.utilisateurs.create'), route('admin.utilisateurs.edit', $chef),
            route('admin.filieres.index'), route('admin.filieres.create'), route('admin.filieres.edit', $filiere),
            route('admin.promotions.index'), route('admin.promotions.create'), route('admin.promotions.edit', $promotion),
            route('admin.annees.index'), route('admin.annees.create'),
            route('admin.semestres.index'), route('admin.semestres.create'),
            route('consultation.filieres'), route('consultation.promotions', $filiere), route('consultation.annees', $promotion),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }
}
