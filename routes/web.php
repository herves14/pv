<?php

/*
|--------------------------------------------------------------------------
| Routes web de l'application « Archives des PV »
|--------------------------------------------------------------------------
| Organisation (de la moins protégée à la plus protégée) :
|   1. Public            : connexion.
|   2. Tout connecté     : accueil (redirection par rôle), mot de passe,
|                          ouverture des PDF, ajout / scan / gestion des PV.
|                          -> le contrôle fin est fait par les Policies.
|   3. Consultation      : admin + chef de département.
|   4. Espace chef       : chef de département.
|   5. Espace expédition : service d'expédition.
|   6. Administration    : admin uniquement.
|
| Deux niveaux de sécurité :
|   - le middleware « role » ferme un espace entier aux autres rôles ;
|   - les Policies (app/Policies) décident pour chaque PV / filière précis.
*/

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\Admin\AnneeAcademiqueController;
use App\Http\Controllers\Admin\FiliereController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SemestreController;
use App\Http\Controllers\Admin\TableauDeBordController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\Compte\MotDePasseController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\FichierPvController;
use App\Http\Controllers\MesEnvoisController;
use App\Http\Controllers\ProcesVerbalController;
use App\Http\Controllers\RechercheController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- 1. Public
Route::middleware('guest')->group(function () {
    Route::get('/login', [ConnexionController::class, 'formulaire'])->name('login');
    Route::post('/login', [ConnexionController::class, 'connecter'])
        ->middleware('throttle:5,1') // 5 tentatives par minute (UC01)
        ->name('login.tentative');
});

// ----------------------------------------------------- 2. Tout utilisateur connecté
Route::middleware('auth')->group(function () {
    Route::post('/logout', [ConnexionController::class, 'deconnecter'])->name('logout');

    // Redirection vers l'espace du rôle (RG21)
    Route::get('/', AccueilController::class)->name('accueil');

    // Mon compte : changer son mot de passe (UC02)
    Route::get('/compte/mot-de-passe', [MotDePasseController::class, 'edit'])->name('compte.mot-de-passe');
    Route::put('/compte/mot-de-passe', [MotDePasseController::class, 'update'])->name('compte.mot-de-passe.update');

    // Ouverture d'un PDF (UC08) : ProcesVerbalPolicy::view
    Route::get('/pv/{pv}/fichier', [FichierPvController::class, 'afficher'])->name('pv.fichier');
    Route::get('/pv/{pv}/telecharger', [FichierPvController::class, 'telecharger'])->name('pv.telecharger');

    // Ajout, scan, remplacement, suppression d'un PV (UC05, UC06, UC10).
    // Ouvert aux 3 rôles : ProcesVerbalPolicy limite le chef à sa filière (RG22)
    // et chef / expédition à leurs propres envois (RG17).
    Route::get('/pv/ajouter', [ProcesVerbalController::class, 'create'])->name('pv.create');
    Route::get('/pv/scanner', [ProcesVerbalController::class, 'scanner'])->name('pv.scanner');
    Route::post('/pv', [ProcesVerbalController::class, 'store'])->name('pv.store');
    Route::get('/pv/{pv}/modifier', [ProcesVerbalController::class, 'edit'])->name('pv.edit');
    Route::put('/pv/{pv}', [ProcesVerbalController::class, 'update'])->name('pv.update');
    Route::delete('/pv/{pv}', [ProcesVerbalController::class, 'destroy'])->name('pv.destroy');

    // Recherche intelligente (UC11) : page complète, ou fragment HTML pour le filtrage instantané
    Route::get('/recherche', RechercheController::class)->name('recherche');

    // ------------------------------------------- 3. Consultation (admin + chef)
    Route::middleware('role:admin,chef_departement')
        ->prefix('consultation')->name('consultation.')
        ->controller(ConsultationController::class)
        ->group(function () {
            Route::get('/', 'filieres')->name('filieres');
            Route::get('/filieres/{filiere}', 'promotions')->name('promotions');
            Route::get('/promotions/{promotion}', 'annees')->name('annees');
            Route::get('/promotions/{promotion}/annees/{annee}', 'semestres')->name('semestres');
        });

    // ---------------------------------------------- 4. Espace chef de département
    Route::middleware('role:chef_departement')->group(function () {
        // Le chef arrive directement sur les promotions de SA filière.
        Route::get('/departement', [ConsultationController::class, 'maFiliere'])->name('departement.index');
    });

    // --------------------------------------------------- 5. Espace expédition
    Route::middleware('role:expedition')->group(function () {
        Route::get('/expedition', MesEnvoisController::class)->name('expedition.index');
    });

    // ------------------------------------------------- 6. Administration (admin)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', TableauDeBordController::class)->name('dashboard');

        Route::resource('utilisateurs', UtilisateurController::class)
            ->except(['show', 'destroy'])
            ->parameters(['utilisateurs' => 'utilisateur']);
        Route::patch('/utilisateurs/{utilisateur}/activation', [UtilisateurController::class, 'basculerActivation'])
            ->name('utilisateurs.activation');

        Route::resource('filieres', FiliereController::class)->except(['show'])
            ->parameters(['filieres' => 'filiere']);
        Route::resource('promotions', PromotionController::class)->except(['show']);
        Route::resource('annees', AnneeAcademiqueController::class)->except(['show'])
            ->parameters(['annees' => 'annee']);
        Route::resource('semestres', SemestreController::class)->except(['show']);
    });
});
