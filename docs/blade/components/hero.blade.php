{{--
    x-hero — Full-screen hero section with search
    ============================================================
    Props:
      $eyebrow   string  Optional. Small label above title.
      $title     string  Required. Main headline (supports HTML).
      $subtitle  string  Optional. Italic subtitle.
      $showStats bool    Optional. Show stats row. Default: true.

    Slots:
      $search    Slot for <x-search-box /> inside the hero.
      $stats     Slot for <x-stat /> items (used when showStats=true).
      $filters   Slot for filter chips below search.

    Usage:
      <x-hero
          eyebrow="Projeto Allan Kardec · UFJF"
          title="Manuscritos <em>&amp; Transcrições</em>"
          subtitle="Acervo digital de documentos originais de Allan Kardec (1804–1869)"
      >
          <x-slot:search>
              <x-search-box
                  placeholder="Pesquise por palavras, frases ou identificadores..."
                  :action="route('pesquisar')"
              />
          </x-slot:search>

          <x-slot:filters>
              <x-chip variant="dark" value="" label="Todos" active />
              <x-chip variant="dark" value="carta" label="Cartas" />
              <x-chip variant="dark" value="dissertacao" label="Dissertações" />
          </x-slot:filters>

          <x-slot:stats>
              <x-stat number="600+" label="Manuscritos" />
              <x-stat number="3"    label="Acervos" />
              <x-stat number="1800–1869" label="Período" />
          </x-slot:stats>
      </x-hero>
--}}
@props([
    'eyebrow'   => null,
    'title'     => 'Manuscritos <em>&amp; Transcrições</em>',
    'subtitle'  => null,
    'showStats' => true,
    'scrollTo'  => null,
])

<section class="k-hero" id="inicio">
    {{-- Decorative layers --}}
    <div class="k-hero__bg" aria-hidden="true"></div>
    <div class="k-hero__grain" aria-hidden="true"></div>

    <div class="k-hero__inner">

        @if($eyebrow)
            <p class="k-hero__eyebrow animate-fade-up delay-100">
                {{ $eyebrow }}
            </p>
        @endif

        <h1 class="k-hero__title animate-fade-up delay-200">
            {!! $title !!}
        </h1>

        @if($subtitle)
            <p class="k-hero__subtitle animate-fade-up delay-300">
                {{ $subtitle }}
            </p>
        @endif

        {{-- Search slot --}}
        @if(isset($search))
            <div class="animate-fade-up delay-400">
                {{ $search }}
            </div>
        @endif

        {{-- Filter chips slot --}}
        @if(isset($filters))
            <div class="k-search__filters animate-fade-up delay-500">
                {{ $filters }}
            </div>
        @endif

        {{-- Stats --}}
        @if($showStats && isset($stats))
            <div class="k-hero__stats">
                {{ $stats }}
            </div>
        @endif

    </div>

    {{-- Scroll hint --}}
    @if($scrollTo)
        <button
            class="k-hero__scroll"
            onclick="document.getElementById('{{ $scrollTo }}').scrollIntoView({ behavior: 'smooth' })"
            aria-label="Rolar para o conteúdo"
        >
            <span class="k-hero__scroll-line" aria-hidden="true"></span>
            <span>Explorar</span>
        </button>
    @endif
</section>
