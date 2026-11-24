<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Recherche intelligente (UC11) par l'adresse /recherche, avec RG25. */
class RechercheTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_chef_ne_trouve_que_les_pv_de_sa_filiere(): void
    {
        $grh = Filiere::factory()->create(['code' => 'GRH']);
        $stat = Filiere::factory()->create(['code' => 'STAT']);
        ProcesVerbal::factory()->create(['promotion_id' => Promotion::factory()->create(['filiere_id' => $grh->id, 'libelle' => 'Promo Alpha'])->id]);
        ProcesVerbal::factory()->create(['promotion_id' => Promotion::factory()->create(['filiere_id' => $stat->id, 'libelle' => 'Promo Beta'])->id]);
        $chef = User::factory()->chef($grh)->create();

        $this->actingAs($chef)->get(route('recherche', ['q' => 'Promo']))
            ->assertOk()
            ->assertSee('Promo Alpha')
            ->assertDontSee('Promo Beta');
    }

    public function test_le_mode_partiel_renvoie_seulement_le_fragment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('recherche', ['q' => 'introuvable', 'partiel' => 1]))
            ->assertOk()
            ->assertSee('Aucun PV ne correspond')
            ->assertDontSee('<html', false);
    }

    public function test_un_texte_trop_court_ne_lance_pas_la_recherche(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('recherche', ['q' => 'a', 'partiel' => 1]))
            ->assertSee('au moins 2 caractères');
    }
}
