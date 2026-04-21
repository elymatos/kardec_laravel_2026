<x-layout.index title="Início">

    {{-- ── 1. HERO ──────────────────────────────────────────────────────────── --}}
    <x-hero
        eyebrow="Projeto Allan Kardec · UFJF"
        title="Manuscritos <em>&amp; Transcrições</em>"
        subtitle="Acervo digital de documentos originais de Allan Kardec"
        scroll-to="manuscritos"
    >
        <x-slot:search>
            <x-search-box action="/pesquisar" />
        </x-slot:search>

        <x-slot:filters>
            <x-chip label="Todos"        :active="true" />
            <x-chip label="Cartas"       value="carta" />
            <x-chip label="Dissertações" value="dissertacao" />
            <x-chip label="Comunicações" value="comunicacao" />
            <x-chip label="Notas"        value="nota" />
            <x-chip label="Fragmentos"   value="fragmento" />
        </x-slot:filters>

        <x-slot:stats>
            <x-stat number="600+"      label="Manuscritos" />
            <x-stat number="3"         label="Acervos" />
            <x-stat number="1800–1869" label="Período" />
        </x-slot:stats>
    </x-hero>

    {{-- ── 2. MANUSCRIPTS ──────────────────────────────────────────────────── --}}
    <x-section id="manuscritos" variant="light">
        <x-section-header
            tag="Acervo"
            title="Manuscritos Recentes"
            link-text="Ver todos"
            link-href="/acesso/acervo"
        />
        <div class="k-ms-grid">
            @foreach($manuscripts as $ms)
                <x-manuscript-card
                    :id="$ms->identifier"
                    :title="$ms->title"
                    :acervo="$ms->acervo->name"
                    href="/item-pt?id={{ $ms->idItem }}"
                />
            @endforeach
        </div>
    </x-section>

    {{-- ── 3. VIEWER ───────────────────────────────────────────────────────── --}}
    <x-section id="transcricoes" variant="dark">
        <x-section-header
            tag="Transcrições"
            title="Imagem &amp; Transcrição"
            :inverted="true"
        />
        <x-viewer
            :meta-id="$viewerItem->identifier"
            :meta-acervo="$viewerItem->acervo"
            :meta-ano="$viewerItem->year"
            :meta-category="$viewerItem->category"
        >
            <x-slot:transcript>
                <p>{!! $viewerItem->excerpt !!}</p>
            </x-slot:transcript>
        </x-viewer>
    </x-section>

    {{-- ── 4. TIMELINE ─────────────────────────────────────────────────────── --}}
    <x-section id="timeline" variant="parchment">
        <x-section-header
            tag="Cronologia"
            title="Linha do Tempo"
        />

        <div class="k-timeline__bar-wrap reveal">
            <div class="k-timeline__years" aria-hidden="true">
                @foreach(['1800', '1820', '1840', '1860', '1869'] as $y)
                    <span>{{ $y }}</span>
                @endforeach
            </div>
            <div class="k-timeline__track" aria-hidden="true">
                @foreach($timelineCategories as $cat)
                    <div
                        class="k-timeline__segment"
                        style="left:{{ $cat->left }}%;width:{{ $cat->width }}%;background:{{ $cat->color }};"
                        title="{{ $cat->label }}"
                    ></div>
                @endforeach
            </div>
            <div class="k-timeline__filters">
                @foreach($timelineCategories as $cat)
                    <x-chip
                        :label="$cat->label"
                        :dot-color="$cat->color"
                        variant="light"
                    />
                @endforeach
            </div>
        </div>

        <div class="k-timeline__events">
            @foreach($timelineEvents as $event)
                <x-timeline-event
                    :year="$event->year"
                    :title="$event->title"
                    :acervo="$event->acervo"
                    :category="$event->category"
                    :category-color="$event->color"
                    @if($event->id) href="/item-pt?id={{ $event->id }}" @endif
                    class="reveal"
                />
            @endforeach
        </div>
    </x-section>

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
