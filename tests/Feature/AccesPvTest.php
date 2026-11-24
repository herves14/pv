<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Vérifie les règles d'accès RG05, RG17 à RG23. */
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

    public function test_un_chef_peut_ajouter_un_pv_seulement_dans_sa_filiere(): void
    {
        $grh = Filiere::factory()->create();
        $stat = Filiere::factory()->create();
        $chef = User::factory()->chef($grh)->create();

        $this->assertTrue($chef->can('create', ProcesVerbal::class));
        $this->assertTrue($chef->can('televerserDans', [ProcesVerbal::class, Promotion::factory()->create(['filiere_id' => $grh->id])]));
        $this->assertFalse($chef->can('televerserDans', [ProcesVerbal::class, Promotion::factory()->create(['filiere_id' => $stat->id])]));
    }

    public function test_un_chef_remplace_ses_envois_mais_pas_ceux_des_autres(): void
    {
        $grh = Filiere::factory()->create();
        $chef = User::factory()->chef($grh)->create();
        $sien = $this->pvDansFiliere($grh, $chef);
        $autre = $this->pvDansFiliere($grh);

        $this->assertTrue($chef->can('update', $sien));
        $this->assertTrue($chef->can('delete', $sien));
        $this->assertFalse($chef->can('update', $autre));
    }

    public function test_seul_l_admin_ajoute_une_filiere(): void
    {
        $this->assertTrue(User::factory()->admin()->create()->can('create', Filiere::class));
        $this->assertFalse(User::factory()->chef()->create()->can('create', Filiere::class));
        $this->assertFalse(User::factory()->create()->can('create', Filiere::class));
    }

    public function test_la_recherche_filtre_et_respecte_les_droits(): void
    {
        $grh = Filiere::factory()->create(['code' => 'GRH']);
        $stat = Filiere::factory()->create(['code' => 'STAT']);
        $chef = User::factory()->chef($grh)->create();

        $promo = Promotion::factory()->create(['filiere_id' => $grh->id, 'libelle' => 'Promotion 2022-2025']);
        $s3 = Semestre::factory()->create(['code' => 'S3']);
        $voulu = ProcesVerbal::factory()->create(['promotion_id' => $promo->id, 'semestre_id' => $s3->id, 'session' => 'rattrapage']);
        ProcesVerbal::factory()->create(['promotion_id' => $promo->id, 'session' => 'normale']);
        // Même semestre, mais dans une autre filière : jamais visible pour ce chef (RG25).
        ProcesVerbal::factory()->create([
            'promotion_id' => Promotion::factory()->create(['filiere_id' => $stat->id, 'libelle' => 'Promotion 2022-2025'])->id,
            'semestre_id' => $s3->id,
            'session' => 'rattrapage',
        ]);

        $ids = ProcesVerbal::visiblesPar($chef)->recherche('2022 s3 RATTRAPAGE')->pluck('id')->all();

        $this->assertSame([$voulu->id], $ids);
        $this->assertCount(2, ProcesVerbal::visiblesPar($chef)->recherche('')->get());
    }
}
