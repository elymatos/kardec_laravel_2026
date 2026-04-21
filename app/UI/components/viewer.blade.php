{{--
    x-viewer — Split-pane manuscript image + transcript viewer
    ============================================================
    Props:
      $files       array   Optional. Associative array of file objects
                           (keys = labels, values = objects with fullsize/thumbnail).
                           When provided, renders a numbered tab bar (001, 002…)
                           in the image pane with ViewerJS on click.
      $metaId      string  Optional. Document identifier.
      $metaAcervo  string  Optional. Archive name.
      $metaAno     string  Optional. Date.
      $metaCategory string Optional. Document category.
      $imageAlt    string  Optional. Image alt text.

    Slots:
      $image       Slot: custom image pane (used when $files is empty — e.g. homepage).
      $transcript  Slot: transcript HTML content (right pane body).
      $imageActions Slot: buttons in image pane toolbar (overrides defaults).
      $textActions  Slot: buttons in transcript pane toolbar.

    Note: ViewerJS (viewer.min.js + viewer.min.css) must be loaded by the
    parent view via the $head slot.
--}}
@props([
    'files'        => [],
    'metaId'       => null,
    'metaAcervo'   => null,
    'metaAno'      => null,
    'metaCategory' => null,
    'imageAlt'     => 'Manuscrito original',
])

@php
    // Normalise files into a flat indexed list with padded labels
    $fileList = [];
    $idx = 1;
    foreach ($files as $file) {
        $fileList[] = [
            'label'    => str_pad($idx, 3, '0', STR_PAD_LEFT),
            'fullsize' => $file->fullsize  ?? null,
            'thumb'    => $file->thumbnail ?? null,
        ];
        $idx++;
    }
    $hasFiles   = count($fileList) > 0;
    $multiFiles = count($fileList) > 1;
@endphp

<div {{ $attributes->merge(['class' => 'k-viewer reveal delay-2']) }}>

    {{-- ── LEFT PANE: images ───────────────────────────────────────────────── --}}
    <div
        class="k-viewer__pane"
        @if($hasFiles)
        x-data="{
            current: 0,
            files: [],
            init() {
                this.files = JSON.parse(this.$refs.filesData.textContent);
            },
            openViewer() {
                const fig = this.$refs.galleryFig;
                if (!fig || typeof Viewer === 'undefined') return;
                const v = new Viewer(fig, {
                    inline:     false,
                    zoomable:   true,
                    rotatable:  true,
                    scalable:   true,
                    toolbar:    true,
                    navbar:     {{ $multiFiles ? 'true' : 'false' }},
                    title:      false,
                    transition: true,
                    keyboard:   true,
                });
                fig.addEventListener('shown',  () => v.view(this.current), { once: true });
                fig.addEventListener('hidden', () => v.destroy(),           { once: true });
                v.show();
            }
        }"
        @endif
    >
        {{-- File data carrier for Alpine (avoids double-quote escaping in x-data attr) --}}
        @if($hasFiles)
            <script x-ref="filesData" type="application/json">{!! json_encode($fileList) !!}</script>
        @endif

        {{-- Header: image tabs OR label + optional custom actions --}}
        <div class="k-viewer__pane-header">
            @if($hasFiles && $multiFiles)
                <div class="k-viewer__img-tabs" role="tablist">
                    @foreach($fileList as $i => $file)
                        <button
                            class="k-viewer__img-tab"
                            :class="{ 'is-active': current === {{ $i }} }"
                            @click="current = {{ $i }}"
                            role="tab"
                            type="button"
                            aria-label="Imagem {{ $file['label'] }}"
                        >{{ $file['label'] }}</button>
                    @endforeach
                </div>
            @else
                <span class="k-viewer__pane-label">Manuscrito Original</span>
            @endif

            <div class="k-viewer__pane-actions">
                @if(isset($imageActions))
                    {{ $imageActions }}
                @elseif($hasFiles)
                    <button
                        class="k-btn k-btn--ghost-dark k-btn--sm"
                        type="button"
                        aria-label="Ampliar imagem"
                        @click="openViewer()"
                        title="Ampliar"
                    >
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                            <path d="M1 1h4M1 1v4M12 1h-4M12 1v4M1 12h4M1 12v-4M12 12h-4M12 12v-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                @else
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Diminuir zoom">−</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Zoom 100%">100%</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Aumentar zoom">+</button>
                @endif
            </div>
        </div>

        {{-- Hidden figure: all images for ViewerJS gallery --}}
        @if($hasFiles)
            <figure x-ref="galleryFig" style="display:none;" aria-hidden="true">
                @foreach($fileList as $file)
                    <img src="{{ $file['fullsize'] }}" alt="{{ $file['label'] }}">
                @endforeach
            </figure>
        @endif

        {{-- Image display area --}}
        <div class="k-viewer__img-area">
            @if($hasFiles)
                {{-- Active image — switches on tab click --}}
                @foreach($fileList as $i => $file)
                    <img
                        src="{{ $file['fullsize'] }}"
                        alt="{{ $imageAlt }} — {{ $file['label'] }}"
                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                        x-show="current === {{ $i }}"
                        @click="openViewer()"
                        style="cursor:zoom-in;max-width:100%;height:auto;display:block;"
                        title="Clique para ampliar"
                    >
                @endforeach
            @elseif(isset($image))
                {{ $image }}
            @else
                {{-- Placeholder --}}
                <div style="width:100%;max-width:320px;aspect-ratio:3/4;background:linear-gradient(145deg,#2a2420,#1e1a16);border:1px solid rgba(184,137,42,0.15);border-radius:4px;display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:rgba(184,137,42,0.3);letter-spacing:0.1em;text-transform:uppercase;">Imagem não disponível</span>
                </div>
            @endif
        </div>

        {{-- Metadata bar --}}
        <div class="k-viewer__meta">
            @if($metaId)
                <div class="k-viewer__meta-item">
                    <div class="k-viewer__meta-key">Identificador</div>
                    <div class="k-viewer__meta-val">{{ $metaId }}</div>
                </div>
            @endif
            @if($metaAcervo)
                <div class="k-viewer__meta-item">
                    <div class="k-viewer__meta-key">Acervo</div>
                    <div class="k-viewer__meta-val">{{ $metaAcervo }}</div>
                </div>
            @endif
            @if($metaAno)
                <div class="k-viewer__meta-item">
                    <div class="k-viewer__meta-key">Ano</div>
                    <div class="k-viewer__meta-val">{{ $metaAno }}</div>
                </div>
            @endif
            @if($metaCategory)
                <div class="k-viewer__meta-item">
                    <div class="k-viewer__meta-key">Categoria</div>
                    <div class="k-viewer__meta-val">{{ $metaCategory }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── RIGHT PANE: transcript ───────────────────────────────────────────── --}}
    <div class="k-viewer__pane">
        <div class="k-viewer__pane-header">
            <span class="k-viewer__pane-label">Transcrição</span>
            <div class="k-viewer__pane-actions">
                @if(isset($textActions))
                    {{ $textActions }}
                @else
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button">PT</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button">FR</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Baixar transcrição">↓</button>
                @endif
            </div>
        </div>

        <div class="k-viewer__text-area">
            <div class="k-viewer__text-content">
                @if(isset($transcript))
                    {{ $transcript }}
                @else
                    <p><em>Transcrição não disponível para este documento.</em></p>
                @endif
            </div>
        </div>
    </div>

</div>
