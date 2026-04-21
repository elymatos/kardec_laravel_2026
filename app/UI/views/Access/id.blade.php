<x-layout.index title="Por Identificador">

    <x-page-header
        eyebrow="Acesso ao Acervo"
        title="Por Identificador"
        description="Todos os manuscritos listados em ordem de identificador numérico."
    />

    <x-section variant="parchment">
        <ul class="k-access-list k-access-list--flat">
            @forelse($list as $item)
                @php $itemTitle = $lang === 'fr' ? $item->frTitle : $item->ptTitle; @endphp
                <li>
                    <a class="k-access-item" href="/item-{{ $lang }}?id={{ $item->idItem }}">
                        <span class="k-access-item__id">[{{ $item->idItem }}]</span>
                        <span class="k-access-item__title">{{ $itemTitle }}</span>
                        @if($item->docDate)
                            <span class="k-access-item__date">{{ $item->docDate }}</span>
                        @endif
                    </a>
                </li>
            @empty
                <p style="color:var(--text-muted);">Nenhum documento encontrado.</p>
            @endforelse
        </ul>
    </x-section>

</x-layout.index>
