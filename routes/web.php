<?php

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\FichierPvController;
use Illuminate\Support\Facades\Route;

// --- Public ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [ConnexionController::class, 'formulaire'])->name('login');
    Route::post('/login', [ConnexionController::class, 'connecter'])
        ->middleware('throttle:5,1'); // 5 essais par minute (UC01)
});

// --- Utilisateur connecté ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [ConnexionController::class, 'deconnecter'])->name('logout');

    // Redirection selon le rôle (RG21)
    Route::get('/', AccueilController::class)->name('accueil');

    // Ouverture d'un PV : contrôle fait par ProcesVerbalPolicy
    Route::get('/pv/{pv}/fichier', [FichierPvController::class, 'afficher'])->name('pv.fichier');
    Route::get('/pv/{pv}/telecharger', [FichierPvController::class, 'telecharger'])->name('pv.telecharger');

    // Espaces par rôle : les écrans détaillés (section 7) viendront dans les prochaines étapes.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'espaces.admin')->name('dashboard');
    });

    Route::middleware('role:chef_departement')->prefix('departement')->name('departement.')->group(function () {
        Route::view('/', 'espaces.departement')->name('index');
    });

    Route::middleware('role:expedition')->prefix('expedition')->name('expedition.')->group(function () {
        Route::view('/', 'espaces.expedition')->name('index');
    });
});
