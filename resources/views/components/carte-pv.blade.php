{{--
|--------------------------------------------------------------------------
| Composant <x-carte-pv :pv="$pv" /> : un PV dans une liste
|--------------------------------------------------------------------------
| Affiche le titre, le classement (filière, promotion, année, semestre),
| la taille du fichier et les actions AUTORISÉES pour l'utilisateur :
|   - Ouvrir / Télécharger : tous ceux qui voient le PV ;
|   - Modifier / Supprimer : seulement si ProcesVerbalPolicy l'autorise
|     (@can) ; la Policy est de toute façon revérifiée côté serveur.
| Le PV doit être chargé avec ->avecClassement() (évite les requêtes en trop).
| :classement="false" masque la ligne de classement (déjà dans le fil d'Ariane).
--}}
@props(['pv', 'classement' => true])

<article class="pv">
    <div class="pv__textes">
        <h3 class="pv__titre">{{ $pv->titre }}</h3>

        @if ($classement)
            <p class="pv__classement">
                {{ $pv->promotion->filiere->code }} · {{ $pv->promotion->libelle }} · {{ $pv->anneeAcademique->libelle }}
            </p>
        @endif

        <p class="pv__details">
            @if ($pv->date_deliberation)
                Délibération du {{ $pv->date_deliberation->format('d/m/Y') }} ·
            @endif
            PDF, {{ $pv->taille_lisible }}
            · ajouté le {{ $pv->created_at->format('d/m/Y') }}
        </p>

        @if ($pv->observation)
            <p class="pv__observation">{{ $pv->observation }}</p>
        @endif
    </div>

    <div class="pv__actions">
        {{-- target="_blank" : le PDF s'ouvre dans un nouvel onglet, la liste reste ouverte --}}
        <a class="bouton" href="{{ route('pv.fichier', $pv) }}" target="_blank" rel="noopener">Ouvrir le PDF</a>
        <a class="bouton bouton--secondaire" href="{{ route('pv.telecharger', $pv) }}">Télécharger</a>

        @can('update', $pv)
            <a class="bouton bouton--lien" href="{{ route('pv.edit', $pv) }}">Modifier</a>
        @endcan

        @can('delete', $pv)
            <form method="POST" action="{{ route('pv.destroy', $pv) }}"
                  data-confirmer="Supprimer définitivement le {{ $pv->titre }} ({{ $pv->anneeAcademique->libelle }}) ?">
                @csrf
                @method('DELETE')
                <button type="submit" class="bouton bouton--lien bouton--danger">Supprimer</button>
            </form>
        @endcan
    </div>
</article>
