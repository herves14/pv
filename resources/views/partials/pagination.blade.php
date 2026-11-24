{{--
|--------------------------------------------------------------------------
| Pagination simple : « ‹ Précédent · Page 2 sur 5 · Suivant › »
|--------------------------------------------------------------------------
| Utilisation : {{ $envois->links('partials.pagination') }}
--}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="bouton bouton--secondaire bouton--desactive" aria-disabled="true">‹ Précédent</span>
        @else
            <a class="bouton bouton--secondaire" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Précédent</a>
        @endif

        <span class="pagination__position">Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="bouton bouton--secondaire" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant ›</a>
        @else
            <span class="bouton bouton--secondaire bouton--desactive" aria-disabled="true">Suivant ›</span>
        @endif
    </nav>
@endif
