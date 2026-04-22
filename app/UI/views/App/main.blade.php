<x-layout.index title="Início">

    {{-- ── 1. HERO ──────────────────────────────────────────────────────────── --}}
    <x-hero
        eyebrow="Acervo Digital · UFJF"
        title="Projeto<br><em>Allan Kardec</em>"
        subtitle=""
        scroll-to="manuscritos"
    >
        <x-slot:search>
            <x-search-box action="/pesquisar" />
        </x-slot:search>

        <x-slot:filters>
{{--            <x-chip label="Todos"        :active="true" />--}}
{{--            <x-chip label="Cartas"       value="carta" />--}}
{{--            <x-chip label="Dissertações" value="dissertacao" />--}}
{{--            <x-chip label="Comunicações" value="comunicacao" />--}}
{{--            <x-chip label="Notas"        value="nota" />--}}
{{--            <x-chip label="Fragmentos"   value="fragmento" />--}}
        </x-slot:filters>

        <x-slot:stats>
            <x-stat number="300+"      label="Manuscritos" />
            <x-stat number="4"         label="Acervos" />
            <x-stat number="1831–1873" label="Período" />
        </x-slot:stats>
    </x-hero>

    {{-- ── 2. MANUSCRIPTS ──────────────────────────────────────────────────── --}}
    <x-section id="manuscritos" variant="light">
        <x-section-header
            tag="Acervo"
            title="Publicações Recentes"
            link-text="Ver todos"
            link-href="/acesso/acervo"
        />
        <div class="k-ms-grid">
            @foreach($manuscripts as $ms)
                <x-manuscript-card
                    :id="$ms->identifier"
                    :item-id="$ms->idItem"
                    :title="$ms->title"
                    :acervo="$ms->acervo->name"
                    href="/item-pt?id={{ $ms->idItem }}"
                    :image-url="$ms->thumbnail ?? null"
                />
            @endforeach
        </div>
    </x-section>

    {{-- ── 3. VIEWER ───────────────────────────────────────────────────────── --}}
    <x-section id="transcricoes" variant="dark">
        <x-section-header
            tag="Manuscritos"
            title="Imagem &amp; Texto"
            :inverted="true"
        />
        @if(is_object($viewerItem))
            @php
                $previewChars  = 500;
                $files         = is_array($viewerItem->files) ? array_values($viewerItem->files) : [];
                $firstTag      = !empty($viewerItem->tags) ? $viewerItem->tags[0]->ptName : null;
                $transcription = !empty($viewerItem->transcription)
                    ? \Illuminate\Support\Str::limit(strip_tags($viewerItem->transcription), $previewChars)
                    : null;
                $translation   = !empty($viewerItem->translation)
                    ? \Illuminate\Support\Str::limit(strip_tags($viewerItem->translation), $previewChars)
                    : null;
            @endphp
            <div x-data="{
                mode: 'original',
                lang: 'pt',
                loading: false,
                cache: {},
                setMode(m) {
                    this.mode = m;
                    if (m === 'translation' && this.lang !== 'pt' && !this.cache[this.lang]) {
                        this.translate(this.lang);
                    }
                },
                setLang(l) {
                    this.mode = 'translation';
                    this.lang  = l;
                    if (l !== 'pt' && !this.cache[l]) {
                        this.translate(l);
                    }
                },
                async translate(l) {
                    this.loading = true;
                    try {
                        const res = await fetch('/item/{{ $viewerItem->idItem }}/translate/' + l);
                        this.cache[l] = res.ok
                            ? await res.text()
                            : '<p><em>Tradução não disponível.</em></p>';
                    } catch {
                        this.cache[l] = '<p><em>Erro de conexão. Tente novamente.</em></p>';
                    }
                    this.loading = false;
                }
            }">
                <x-viewer
                    :files="$files"
                    :meta-id="'#' . $viewerItem->idItem"
                    :meta-acervo="$viewerItem->collection ?? null"
                    :meta-ano="$viewerItem->docDate ?? null"
                    :meta-category="$firstTag"
                    :image-alt="$viewerItem->title"
                    :max-chars="$previewChars"
                >
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

                    <x-slot:transcript>
                        <div class="k-lang-strip" x-show="mode === 'translation'" x-cloak>
                            <div class="k-lang-strip__pills" role="tablist" aria-label="Idioma da tradução">
                                @foreach(['pt'=>'PT','en'=>'EN','de'=>'DE','it'=>'IT','zh'=>'ZH','ja'=>'JA'] as $code => $label)
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
                            <div class="k-lang-strip__status" x-show="loading" x-cloak>
                                <span class="k-lang-spinner" aria-hidden="true"></span>
                                <span>traduzindo…</span>
                            </div>
                        </div>

                        <div x-show="mode === 'original'">
                            @if($transcription)
                                {!! $transcription !!}
                            @else
                                <p><em style="color:var(--text-on-dark-muted);">Transcrição não disponível.</em></p>
                            @endif
                        </div>

                        <div x-show="mode === 'translation' && lang === 'pt'" x-cloak>
                            @if($translation)
                                {!! $translation !!}
                            @else
                                <p><em style="color:var(--text-on-dark-muted);">Tradução não disponível.</em></p>
                            @endif
                        </div>

                        <div x-show="mode === 'translation' && lang !== 'pt'" x-cloak>
                            <div class="k-text-skeleton" x-show="loading">
                                <div class="k-text-skeleton__line" style="width:91%"></div>
                                <div class="k-text-skeleton__line" style="width:76%"></div>
                                <div class="k-text-skeleton__line" style="width:84%"></div>
                                <div class="k-text-skeleton__line" style="width:58%"></div>
                            </div>
                            <div x-show="!loading" x-html="cache[lang] ?? ''"></div>
                        </div>
                    </x-slot:transcript>
                </x-viewer>
            </div>
        @endif
    </x-section>

    {{-- ── 4. TIMELINE ─────────────────────────────────────────────────────── --}}
{{--    <x-section id="timeline" variant="parchment">--}}
{{--        <x-section-header--}}
{{--            tag="Cronologia"--}}
{{--            title="Linha do Tempo"--}}
{{--        />--}}

{{--        <div class="k-timeline__bar-wrap reveal">--}}
{{--            <div class="k-timeline__years" aria-hidden="true">--}}
{{--                @foreach(['1800', '1820', '1840', '1860', '1869'] as $y)--}}
{{--                    <span>{{ $y }}</span>--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--            <div class="k-timeline__track" aria-hidden="true">--}}
{{--                @foreach($timelineCategories as $cat)--}}
{{--                    <div--}}
{{--                        class="k-timeline__segment"--}}
{{--                        style="left:{{ $cat->left }}%;width:{{ $cat->width }}%;background:{{ $cat->color }};"--}}
{{--                        title="{{ $cat->label }}"--}}
{{--                    ></div>--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--            <div class="k-timeline__filters">--}}
{{--                @foreach($timelineCategories as $cat)--}}
{{--                    <x-chip--}}
{{--                        :label="$cat->label"--}}
{{--                        :dot-color="$cat->color"--}}
{{--                        variant="light"--}}
{{--                    />--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="k-timeline__events">--}}
{{--            @foreach($timelineEvents as $event)--}}
{{--                <x-timeline-event--}}
{{--                    :year="$event->year"--}}
{{--                    :title="$event->title"--}}
{{--                    :acervo="$event->acervo"--}}
{{--                    :category="$event->category"--}}
{{--                    :category-color="$event->color"--}}
{{--                    @if($event->id) href="/item-pt?id={{ $event->id }}" @endif--}}
{{--                    class="reveal"--}}
{{--                />--}}
{{--            @endforeach--}}
{{--        </div>--}}
{{--    </x-section>--}}

    {{-- ── 5. ABOUT ─────────────────────────────────────────────────────────── --}}
    <x-section id="sobre" variant="light">
        <div class="k-about__grid">

            <div class="k-about__text reveal">
                <x-section-header
                    tag="O Projeto"
                    title="Allan Kardec &amp; os Manuscritos"
                />
                <p>Acervo digital de documentos manuscritos, transcrições e edições críticas reunidos por pesquisadores da Universidade Federal de Juiz de Fora, com apoio da FAPEMIG.</p>
                <p>Os manuscritos compreendem cartas, dissertações, notas autógrafas e comunicações espíritas produzidos entre <strong>1800 e 1869</strong>, preservados em três acervos internacionais.</p>
                <blockquote class="k-about__pull">
                    "A caridade sem a fé pode existir, mas a fé sem a caridade é uma fé morta."
                    <cite>— Allan Kardec</cite>
                </blockquote>
            </div>

            <aside class="k-about__side">
                <div class="k-info-card reveal">
                    <p class="k-info-card__label">Categorias documentais</p>
                    <div class="k-info-card__content">
                        Cartas · Dissertações · Notas autógrafas · Comunicações espíritas · Fragmentos · Obras publicadas
                    </div>
                </div>
                <div class="k-info-card reveal">
                    <p class="k-info-card__label">Apoio</p>
                    <div class="k-info-card__content">
                        FAPEMIG APQ-04113-23<br>
                        Universidade Federal de Juiz de Fora
                    </div>
                </div>
            </aside>

        </div>
    </x-section>

    {{-- ── 6. BIOGRAPHIES ──────────────────────────────────────────────────── --}}
    <x-section id="biografias" variant="dark">
        <x-section-header
            tag="Equipe &amp; Biógrafos"
            title="Pessoas Envolvidas"
            :inverted="true"
        />
        <div class="k-bio-grid">
            @foreach($biographies as $bio)
                <x-bio-card
                    :name="$bio->name"
                    :role="$bio->role"
                    href="/biografias"
                    class="reveal"
                />
            @endforeach
        </div>
    </x-section>

</x-layout.index>
