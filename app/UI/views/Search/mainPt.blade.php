<x-layout.index title="Pesquisar">

    <x-page-header
        eyebrow="Acervo"
        title="Pesquisar"
        description="Busque por palavras, identificador, acervo, ano ou categoria."
    />

    <x-section variant="parchment">
        <form
            id="frmSearch"
            hx-post="/pesquisar"
            hx-target="#results"
            hx-indicator="#search-spinner"
            style="display:flex;flex-direction:column;gap:var(--space-8);max-width:860px;"
            @if(!empty($q)) x-data x-init="$nextTick(() => htmx.trigger($el, 'submit'))" @endif
        >
            @csrf

            {{-- Full-text search — plain input, no nested <form> --}}
            <div class="k-form-group">
                <label class="k-label" for="search">
                    Palavras ou expressões entre aspas
                </label>
                <div class="k-search__box k-search--light">
                    <input
                        type="search"
                        id="search"
                        name="search"
                        class="k-search__input"
                        placeholder='ex: "carta" ou "O livro dos Espíritos"'
                        value="{{ old('search', $q ?? '') }}"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                    >
                </div>
            </div>

            {{-- Filter row --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--space-6);">

{{--                <div class="k-form-group">--}}
{{--                    <label class="k-label" for="idItem">Identificador (ex: 108)</label>--}}
{{--                    <input class="k-input" type="number" id="idItem" name="idItem" placeholder="" min="1">--}}
{{--                </div>--}}

                <div class="k-form-group">
                    <label class="k-label" for="collectionCode">Acervo</label>
                    <select class="k-select" id="collectionCode" name="collectionCode">
                        <option value="">Todos</option>
                        @foreach($collections as $col)
                            <option value="{{ $col->codeCollection }}">{{ $col->ptCollection }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="k-form-group">
                    <label class="k-label" for="idTag">Categoria</label>
                    <select class="k-select" id="idTag" name="idTag">
                        <option value="">Todas</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->idTag }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="k-form-group">
                    <label class="k-label" for="metadataType">Metadado</label>
                    <select
                        class="k-select"
                        id="metadataType"
                        name="metadataType"
                        hx-get="/pesquisar/metadata/instancias"
                        hx-trigger="change"
                        hx-target="#metadataInstanceId"
                        hx-include="this"
                        hx-swap="innerHTML"
                    >
                        @php
                            $metadataLabels = [
                                'author'    => 'Autor',
                                'addressee' => 'Destinatário',
                                'medium'    => 'Médium',
                                'spirit'    => 'Espírito',
                                'person'    => 'Pessoa citada',
                                'origin'    => 'Origem',
                                'place'     => 'Lugar',
                                'book'      => 'Livro citado',
                                'link'      => 'Link',
                            ];
                        @endphp
                        <option value="">Todos</option>
                        @foreach($metadataTypes as $mt)
                            <option value="{{ $mt->nameType }}">{{ $metadataLabels[$mt->nameType] ?? $mt->nameType }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="k-form-group">
                    <label class="k-label" for="metadataInstanceId">Valor</label>
                    <select class="k-select" id="metadataInstanceId" name="metadataInstanceId">
                        <option value="">Todos</option>
                    </select>
                </div>

            </div>

            {{-- Actions --}}
            <div style="display:flex;align-items:center;gap:var(--space-4);flex-wrap:wrap;">
                <button type="submit" class="k-nav__cta" style="border:none;cursor:pointer;">
                    Pesquisar
                </button>
                <a href="/pesquisar" style="font-family:var(--font-mono);font-size:var(--text-sm);color:var(--text-muted);letter-spacing:var(--tracking-wider);text-transform:uppercase;">
                    Nova pesquisa
                </a>
                <span id="search-spinner" class="htmx-indicator" style="font-family:var(--font-mono);font-size:var(--text-sm);color:var(--color-gold-500);">
                    Pesquisando…
                </span>
            </div>

        </form>

        {{-- Results (injected by HTMX) --}}
        <div id="results" style="margin-top:var(--space-10);"></div>
    </x-section>

</x-layout.index>
