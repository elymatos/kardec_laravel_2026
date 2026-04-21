{{--
    x-nav — Main navigation bar
    ============================================================
    Props:
      $links      array  Required. Each item: ['href' => '', 'label' => '']
      $ctaLabel   string Optional. Button label. Default: 'Entrar'
      $ctaHref    string Optional. Button URL.
      $letter     string Optional. Logo mark character. Default: 'K'
      $title      string Optional. Site name. Default: 'Projeto Allan Kardec'
      $subtitle   string Optional. Tagline below title.

    Usage:
      <x-nav
          :links="[
              ['href' => route('manuscritos.index'), 'label' => 'Manuscritos'],
              ['href' => route('timeline'), 'label' => 'Timeline'],
          ]"
          cta-label="Entrar"
          :cta-href="route('login')"
      />
--}}
@props([
    'links'    => [],
    'ctaLabel' => 'Entrar',
    'ctaHref'  => null,
    'letter'   => 'K',
    'title'    => 'Projeto Allan Kardec',
    'subtitle' => 'UFJF — Acervo Digital',
])

<nav class="k-nav" id="k-nav" x-data="{ open: false }" aria-label="Navegação principal">

    {{-- Logo --}}
    <a href="{{ route('home') }}" class="k-nav__logo">
        <span class="k-nav__logo-mark" aria-hidden="true">{{ $letter }}</span>
        <span class="k-nav__title">
            {{ $title }}
            @if($subtitle)
                <small>{{ $subtitle }}</small>
            @endif
        </span>
    </a>

    {{-- Desktop links --}}
    @if(count($links))
        <ul class="k-nav__links hide-tablet" role="list">
            @foreach($links as $link)
                <li>
                    <a
                        href="{{ $link['href'] }}"
                        @class([
                            'active' => request()->url() === $link['href'],
                        ])
                        @if(request()->url() === $link['href']) aria-current="page" @endif
                    >
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Right side: CTA + hamburger --}}
    <div style="display:flex;align-items:center;gap:0.75rem;">
        @if($ctaHref)
            <a href="{{ $ctaHref }}" class="k-nav__cta hide-tablet">
                {{ $ctaLabel }}
            </a>
        @endif

        {{-- Hamburger (mobile) --}}
        <button
            class="k-nav__hamburger"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="k-nav-drawer"
            aria-label="Abrir menu"
        >
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                <line x1="2" y1="6"  x2="20" y2="6"  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="2" y1="11" x2="20" y2="11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="2" y1="16" x2="20" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    {{-- Mobile drawer --}}
    <div
        class="k-nav__drawer"
        id="k-nav-drawer"
        :class="{ 'is-open': open }"
        role="dialog"
        aria-label="Menu de navegação"
    >
        @foreach($links as $link)
            <a href="{{ $link['href'] }}" @click="open = false">
                {{ $link['label'] }}
            </a>
        @endforeach
        @if($ctaHref)
            <a href="{{ $ctaHref }}" @click="open = false">
                {{ $ctaLabel }}
            </a>
        @endif
    </div>
</nav>
