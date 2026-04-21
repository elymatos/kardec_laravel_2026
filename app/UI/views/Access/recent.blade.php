<x-layout.index title="Publicações Recentes">

    <x-page-header
        eyebrow="Acesso ao Acervo"
        title="Publicações Recentes"
        description="Documentos adicionados ou atualizados mais recentemente."
    />

    <x-section variant="parchment">
        @forelse($list as $date => $items)
            <div class="k-access-group reveal">
                <h2 class="k-access-group__head">{{ $date }}</h2>
                <ul class="k-access-list">
                    @foreach($items as $item)
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
                    @endforeach
                </ul>
            </div>
        @empty
            <p style="color:var(--text-muted);">Nenhum documento encontrado.</p>
        @endforelse
    </x-section>

</x-layout.index>
