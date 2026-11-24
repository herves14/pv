<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Vérifie les règles d'accès RG05, RG18 à RG21. */
class AccesPvTest extends TestCase
{
    use RefreshDatabase;

    private function pvDansFiliere(Filiere $filiere, ?User $auteur = null): ProcesVerbal
    {
        $pv = ProcesVerbal::factory()->create([
            'promotion_id' => Promotion::factory()->create(['filiere_id' => $filiere->id])->id,
            'televerse_par' => ($auteur ?? User::factory()->create())->id,
        ]);
        Storage::disk('local')->put($pv->fichier_chemin, '%PDF-1.4 test');

        return $pv;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_redirection_selon_le_role(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertRedirect('/admin');
        $this->actingAs(User::factory()->chef()->create())->get('/')->assertRedirect('/departement');
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/expedition');
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $user = User::factory()->inactif()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_connexion_puis_redirection(): void
    {
        $chef = User::factory()->chef()->create();

        $this->post('/login', ['email' => $chef->email, 'password' => 'password'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($chef);
    }

    public function test_un_chef_ne_voit_que_sa_filiere(): void
    {
        $grh = Filiere::factory()->create();
        $stat = Filiere::factory()->create();
        $chef = User::factory()->chef($grh)->create();

        $pvGrh = $this->pvDansFiliere($grh);
        $pvStat = $this->pvDansFiliere($stat);

        $this->actingAs($chef)->get(route('pv.fichier', $pvGrh))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->actingAs($chef)->get(route('pv.fichier', $pvStat))->assertForbidden();

        $this->assertSame(
            [$pvGrh->id],
            ProcesVerbal::visiblesPar($chef)->pluck('id')->all()
        );
    }

    public function test_l_admin_voit_tout(): void
    {
        $admin = User::factory()->admin()->create();
        $pv = $this->pvDansFiliere(Filiere::factory()->create());

        $this->actingAs($admin)->get(route('pv.fichier', $pv))->assertOk();
    }

    public function test_l_expedition_ne_voit_que_ses_envois(): void
    {
        $agent = User::factory()->create();
        $filiere = Filiere::factory()->create();
        $sien = $this->pvDansFiliere($filiere, $agent);
        $autre = $this->pvDansFiliere($filiere);

        $this->actingAs($agent)->get(route('pv.fichier', $sien))->assertOk();
        $this->actingAs($agent)->get(route('pv.fichier', $autre))->assertForbidden();
    }

    public function test_un_espace_d_un_autre_role_est_interdit(): void
    {
        $chef = User::factory()->chef()->create();

        $this->actingAs($chef)->get('/admin')->assertForbidden();
        $this->actingAs($chef)->get('/expedition')->assertForbidden();
        $this->actingAs($chef)->get('/departement')->assertOk();
    }
}
