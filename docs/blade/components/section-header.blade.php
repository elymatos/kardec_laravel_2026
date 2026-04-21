{{--
    x-section-header — Section tag + title + optional "see all" link
    ============================================================
    Props:
      $tag        string  Optional. Small label above title (e.g. 'Acervo').
      $title      string  Required. Section heading text.
      $linkText   string  Optional. "See all" link label.
      $linkHref   string  Optional. "See all" link URL.
      $inverted   bool    Optional. True for dark-background sections. Default: false.

    Usage:
      <x-section-header
          tag="Acervo"
          title="Manuscritos Recentes"
          link-text="Ver todos"
          :link-href="route('manuscritos.index')"
      />

      {{-- On dark background --}}
      <x-section-header
          tag="Visualizador"
          title="Imagem &amp; Transcrição"
          :inverted="true"
      />
--}}
@props([
    'tag'      => null,
    'title'    => '',
    'linkText' => null,
    'linkHref' => null,
    'inverted' => false,
])

<div {{ $attributes->merge(['class' => 'k-section-header reveal ' . ($inverted ? 'k-section-header--inverted' : '')]) }}>
    <div>
        @if($tag)
            <p class="k-section-tag">{{ $tag }}</p>
        @endif
        <h2 class="k-section-title">{!! $title !!}</h2>
    </div>

    @if($linkText && $linkHref)
        <a
            href="{{ $linkHref }}"
            class="k-section-more {{ $inverted ? 'k-section-more--inverted' : '' }}"
        >
            {{ $linkText }}
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <line x1="2" y1="7" x2="12" y2="7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                <polyline points="8,3 12,7 8,11" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    @endif

    {{-- Optional slot for custom right-side content --}}
    {{ $slot }}
</div>
