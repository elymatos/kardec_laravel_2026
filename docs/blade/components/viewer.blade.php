{{--
    x-viewer — Split-pane manuscript image + transcript viewer
    ============================================================
    Props:
      $metaId      string  Optional. Document identifier.
      $metaAcervo  string  Optional. Archive name.
      $metaAno     string  Optional. Date.
      $metaCategory string Optional. Document category.
      $imageUrl    string  Optional. Manuscript image URL.
      $imageAlt    string  Optional. Image alt text.

    Slots:
      $image       Slot: custom image pane content (replaces default).
      $transcript  Slot: transcript HTML content.
      $imageActions Slot: buttons in the image pane toolbar.
      $textActions  Slot: buttons in the transcript pane toolbar.

    Usage:
      <x-viewer
          :meta-id="$manuscript->identifier"
          :meta-acervo="$manuscript->acervo->name"
          :meta-ano="$manuscript->year_label"
          :meta-category="$manuscript->category"
          :image-url="$manuscript->image_url"
          :image-alt="'Manuscrito ' . $manuscript->identifier"
      >
          <x-slot:transcript>
              {!! $manuscript->transcript_html !!}
          </x-slot:transcript>

          <x-slot:text-actions>
              <x-button variant="ghost-dark" size="sm">PT</x-button>
              <x-button variant="ghost-dark" size="sm">FR</x-button>
          </x-slot:text-actions>
      </x-viewer>
--}}
@props([
    'metaId'       => null,
    'metaAcervo'   => null,
    'metaAno'      => null,
    'metaCategory' => null,
    'imageUrl'     => null,
    'imageAlt'     => 'Manuscrito original',
])

<div {{ $attributes->merge(['class' => 'k-viewer reveal delay-2']) }}>

    {{-- ── LEFT PANE: image ─────────────────────────────────── --}}
    <div class="k-viewer__pane">
        {{-- Header --}}
        <div class="k-viewer__pane-header">
            <span class="k-viewer__pane-label">Manuscrito Original</span>
            <div class="k-viewer__pane-actions">
                @if(isset($imageActions))
                    {{ $imageActions }}
                @else
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Diminuir zoom">−</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Zoom 100%">100%</button>
                    <button class="k-btn k-btn--ghost-dark k-btn--sm" type="button" aria-label="Aumentar zoom">+</button>
                @endif
            </div>
        </div>

        {{-- Image area --}}
        <div class="k-viewer__img-area">
            @if(isset($image))
                {{ $image }}
            @elseif($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $imageAlt }}"
                    loading="lazy"
                >
            @else
                {{-- Placeholder --}}
                <div style="width:100%;max-width:320px;aspect-ratio:3/4;background:linear-gradient(145deg,#2a2420,#1e1a16);border:1px solid rgba(184,137,42,0.15);border-radius:4px;box-shadow:0 20px 60px rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
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

    {{-- ── RIGHT PANE: transcript ────────────────────────────── --}}
    <div class="k-viewer__pane">
        {{-- Header --}}
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

        {{-- Transcript text --}}
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
