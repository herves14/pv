<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ajout (UC05 / UC10), modification et suppression (UC06) des PV,
 * avec les règles RG13, RG14, RG17 et RG22.
 */
class GestionPvTest extends TestCase
{
    use RefreshDatabase;

    private Filiere $grh;

    private Filiere $stat;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->grh = Filiere::factory()->create(['code' => 'GRH']);
        $this->stat = Filiere::factory()->create(['code' => 'STAT']);
    }

    /** Un vrai petit PDF (le serveur vérifie le contenu, pas seulement l'extension). */
    private function pdf(string $nom = 'pv.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nom, "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF");
    }

    private function donnees(Filiere $filiere, array $surcharge = []): array
    {
        return $surcharge + [
            'promotion_id' => Promotion::factory()->create(['filiere_id' => $filiere->id])->id,
            'annee_academique_id' => AnneeAcademique::factory()->create()->id,
            'semestre_id' => Semestre::factory()->create()->id,
            'session' => 'normale',
            'fichier' => $this->pdf(),
        ];
    }

    public function test_l_expedition_televerse_un_pv_dans_n_importe_quelle_filiere(): void
    {
        $agent = User::factory()->create();

        $this->actingAs($agent)->post(route('pv.store'), $this->donnees($this->stat))
            ->assertRedirect(route('expedition.index'))
            ->assertSessionHas('succes');

        $pv = ProcesVerbal::sole();
        $this->assertSame($agent->id, $pv->televerse_par);
        $this->assertStringStartsWith('pv/stat/', $pv->fichier_chemin);
        Storage::disk('local')->assertExists($pv->fichier_chemin);
    }

    public function test_un_chef_ajoute_dans_sa_filiere(): void
    {
        $chef = User::factory()->chef($this->grh)->create();

        $this->actingAs($chef)->post(route('pv.store'), $this->donnees($this->grh))->assertRedirect();

        $this->assertSame(1, ProcesVerbal::count());
    }

    public function test_un_chef_ne_peut_pas_ajouter_dans_une_autre_filiere_meme_en_trichant(): void
    {
        $chef = User::factory()->chef($this->grh)->create();

        // Le formulaire ne propose que GRH ; on envoie quand même une promotion STAT.
        $this->actingAs($chef)->post(route('pv.store'), $this->donnees($this->stat))->assertForbidden();

        $this->assertSame(0, ProcesVerbal::count());
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_le_formulaire_du_chef_ne_propose_que_sa_filiere(): void
    {
        Promotion::factory()->create(['filiere_id' => $this->grh->id, 'libelle' => 'Promo GRH']);
        Promotion::factory()->create(['filiere_id' => $this->stat->id, 'libelle' => 'Promo STAT']);
        $chef = User::factory()->chef($this->grh)->create();

        $this->actingAs($chef)->get(route('pv.create'))
            ->assertOk()
            ->assertSee('Promo GRH')
            ->assertDontSee('Promo STAT');
    }

    public function test_un_fichier_qui_n_est_pas_un_pdf_est_refuse(): void
    {
        $donnees = $this->donnees($this->grh, ['fichier' => UploadedFile::fake()->image('photo.jpg')]);

        $this->actingAs(User::factory()->admin()->create())->post(route('pv.store'), $donnees)
            ->assertSessionHasErrors('fichier');
    }

    public function test_un_pdf_de_plus_de_20_mo_est_refuse(): void
    {
        $donnees = $this->donnees($this->grh, ['fichier' => UploadedFile::fake()->create('gros.pdf', 20481, 'application/pdf')]);

        $this->actingAs(User::factory()->admin()->create())->post(route('pv.store'), $donnees)
            ->assertSessionHasErrors('fichier');
    }

    public function test_un_doublon_est_refuse_rg13(): void
    {
        $admin = User::factory()->admin()->create();
        $donnees = $this->donnees($this->grh);

        $this->actingAs($admin)->post(route('pv.store'), $donnees)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('pv.store'), ['fichier' => $this->pdf()] + $donnees)
            ->assertSessionHasErrors('session');

        $this->assertSame(1, ProcesVerbal::count());
    }

    public function test_remplacer_le_fichier_supprime_l_ancien(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('pv.store'), $this->donnees($this->grh));
        $pv = ProcesVerbal::sole();
        $ancien = $pv->fichier_chemin;

        $this->actingAs($admin)->put(route('pv.update', $pv), ['fichier' => $this->pdf('nouveau.pdf'), 'observation' => 'Rescanné'])
            ->assertRedirect();

        $pv->refresh();
        $this->assertSame('nouveau.pdf', $pv->fichier_nom_original);
        $this->assertSame('Rescanné', $pv->observation);
        Storage::disk('local')->assertMissing($ancien);
        Storage::disk('local')->assertExists($pv->fichier_chemin);
    }

    public function test_l_expedition_ne_modifie_pas_le_pv_d_un_autre(): void
    {
        $this->actingAs(User::factory()->create())->post(route('pv.store'), $this->donnees($this->grh));
        $pv = ProcesVerbal::sole();
        $autreAgent = User::factory()->create();

        $this->actingAs($autreAgent)->get(route('pv.edit', $pv))->assertForbidden();
        $this->actingAs($autreAgent)->put(route('pv.update', $pv), ['observation' => 'x'])->assertForbidden();
        $this->actingAs($autreAgent)->delete(route('pv.destroy', $pv))->assertForbidden();
    }

    public function test_supprimer_un_pv_efface_aussi_le_fichier(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('pv.store'), $this->donnees($this->grh));
        $pv = ProcesVerbal::sole();

        $this->actingAs($admin)->delete(route('pv.destroy', $pv))->assertRedirect();

        $this->assertModelMissing($pv);
        Storage::disk('local')->assertMissing($pv->fichier_chemin);
    }

    public function test_la_page_des_semestres_affiche_les_pv_et_les_actions_autorisees(): void
    {
        $chef = User::factory()->chef($this->grh)->create();
        $this->actingAs(User::factory()->create())->post(route('pv.store'), $this->donnees($this->grh));
        $pv = ProcesVerbal::sole();

        $this->actingAs($chef)->get(route('consultation.semestres', [$pv->promotion_id, $pv->annee_academique_id]))
            ->assertOk()
            ->assertSee('Ouvrir le PDF')
            ->assertSee(route('pv.fichier', $pv))
            ->assertDontSee(route('pv.edit', $pv)); // le chef n'est pas l'auteur
    }
}
