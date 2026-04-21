<x-layout.index title="Biografias">

    <x-page-header
        eyebrow="Acervo"
        title="Biografias"
        description="Notas biográficas de personalidades citadas no acervo."
    />

    <x-section variant="light">

        @if($list)
        <div class="k-bio-split" x-data="{ active: null }">

            {{-- Left: name list --}}
            <nav class="k-bio-split__list" aria-label="Lista de biografias">
                @php
                    $grouped = collect($list)->groupBy(fn($item) => mb_strtoupper(mb_substr($item->title, 0, 1, 'UTF-8'), 'UTF-8'));
                @endphp

                @foreach($grouped as $letter => $items)
                    <div class="k-bio-split__group">
                        <div class="k-bio-split__letter">{{ $letter }}</div>
                        @foreach($items as $item)
                            <button
                                type="button"
                                class="k-bio-split__name"
                                :class="{ 'is-active': active === {{ $item->idItem }} }"
                                @click="active = {{ $item->idItem }}"
                                hx-get="/biografias/item/{{ $item->idItem }}/fragment"
                                hx-target="#bio-content"
                                hx-swap="innerHTML"
                                hx-indicator="#bio-spinner"
                            >
                                {{ $item->title }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Right: biography content --}}
            <div class="k-bio-split__content">
                <div id="bio-spinner" class="htmx-indicator k-bio-split__spinner">
                    Carregando…
                </div>
                <div id="bio-content" class="k-bio-split__body">
                    <p class="k-bio-split__placeholder">
                        Selecione uma biografia na lista ao lado.
                    </p>
                </div>
            </div>

        </div>
        @else
            <p style="color:var(--text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">
                Nenhuma biografia disponível no momento.
            </p>
        @endif

    </x-section>

</x-layout.index>
