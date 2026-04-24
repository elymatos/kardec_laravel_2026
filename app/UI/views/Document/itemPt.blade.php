@php
    $hasItem  = is_object($item);
    $files    = $hasItem && is_array($item->files) ? array_values($item->files) : [];
    $fileCount = count($files);
    $firstTag  = $hasItem && !empty($item->tags) ? $item->tags[0]->ptName : null;
@endphp

<x-layout.index :title="$hasItem ? ($item->title . ' [' . $item->docDate . ']') : 'Manuscrito'">

    <x-slot:head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/viewerjs@1/dist/viewer.min.css">
        <script src="https://cdn.jsdelivr.net/npm/viewerjs@1/dist/viewer.min.js" defer></script>
    </x-slot:head>

    <x-page-header
        eyebrow="Acervo"
        :title="$hasItem ? $item->title : 'Manuscrito'"
    >
        @if($hasItem)
            <x-slot:actions>
                <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;align-items:center;">
                    <span class="k-badge">#{{ $item->idItem }}</span>
                    @foreach($item->tags as $tag)
                        <span class="k-badge k-badge--outline">{{ $tag->ptName }}</span>
                    @endforeach
                </div>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- ── VIEWER ─────────────────────────────────────────────────────────── --}}
    <x-section variant="dark">
        @if(!$hasItem)
            <p style="color:var(--text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">
                Documento não disponível no momento.
            </p>
        @else
            <div x-data="{
                mode: 'original',
                lang: 'pt',
                cache: @js($item->translations),

                setMode(m) { this.mode = m; },

                setLang(l) {
                    this.mode = 'translation';
                    this.lang = l;
                }
            }">

                <x-viewer
                    :files="$files"
                    :meta-id="'#' . $item->idItem"
                    :meta-acervo="$item->collection ?? null"
                    :meta-ano="$item->docDate ?? null"
                    :meta-category="$firstTag"
                    :image-alt="$item->title"
                >
                    {{-- Text pane toolbar: primary mode toggle --}}
                    <x-slot:textActions>
                        <button
                            class="k-btn k-btn--ghost-dark k-btn--sm"
                            :class="{ 'is-active': mode === 'original' }"
                            @click="setMode('original')"
                            type="button"
                        >Original</button>
                        <button
                            class="k-btn k-btn--ghost-dark k-btn--sm"
                            :class="{ 'is-active': mode === 'translation' }"
                            @click="setMode('translation')"
                            type="button"
                        >Tradução</button>
                    </x-slot:textActions>

                    {{-- Text pane content --}}
                    <x-slot:transcript>

                        {{-- Language strip — slides in when in translation mode --}}
                        <div class="k-lang-strip" x-show="mode === 'translation'" x-cloak>
                            <div class="k-lang-strip__pills" role="tablist" aria-label="Idioma da tradução">
                                @foreach(['pt'=>'Português','en'=>'Inglês','de'=>'Alemão','it'=>'Italiano','zh'=>'Chinês','ja'=>'Japonês'] as $code => $label)
                                    <button
                                        class="k-lang-pill"
                                        :class="{ 'is-active': lang === '{{ $code }}' }"
                                        @click="setLang('{{ $code }}')"
                                        role="tab"
                                        type="button"
                                        aria-label="{{ $label === 'PT' ? 'Português' : $label }}"
                                    >{{ $label }}</button>
                                @endforeach
                            </div>
                            </div>

                        {{-- Original transcription --}}
                        <div x-show="mode === 'original'">
                            @if(!empty($item->transcription))
                                {!! $item->transcription !!}
                            @else
                                <p><em style="color:var(--text-on-dark-muted);">Transcrição não disponível.</em></p>
                            @endif
                        </div>

                        {{-- PT translation (pre-existing, no fetch needed) --}}
                        <div x-show="mode === 'translation' && lang === 'pt'" x-cloak>
                            @if(!empty($item->translation))
                                {!! $item->translation !!}
                            @else
                                <p><em style="color:var(--text-on-dark-muted);">Tradução não disponível.</em></p>
                            @endif
                        </div>

                        {{-- DB translations for all non-PT languages --}}
                        <div x-show="mode === 'translation' && lang !== 'pt'" x-cloak>
                            <div x-html="cache[lang] || '<p><em style=\'color:var(--text-on-dark-muted);\'>Tradução não disponível.</em></p>'"></div>
                        </div>

                    </x-slot:transcript>
                </x-viewer>

                {{-- Secondary tabs: close dates --}}
                @if(!empty($item->around))
                    <div
                        x-data="{ tab: 'closeDates' }"
                        style="margin-top:var(--space-12);"
                    >
                        <div class="k-doc-tabs" role="tablist">
                            <button
                                class="k-doc-tab"
                                :class="{ 'is-active': tab === 'closeDates' }"
                                @click="tab = 'closeDates'"
                                role="tab"
                            >Datas próximas</button>
                        </div>
                        <div class="k-doc-panel" x-show="tab === 'closeDates'">
                            @include('Document.closeDatesPt')
                        </div>
                    </div>
                @endif

            </div>
        @endif
    </x-section>

    {{-- ── METADATA ───────────────────────────────────────────────────────── --}}
    @if($hasItem)
        <x-section variant="parchment">
            <div style="max-width:680px;">
                @include('Document.metadataPt')
            </div>
        </x-section>
    @endif

</x-layout.index>
