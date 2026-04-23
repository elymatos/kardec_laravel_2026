{{--
    x-nav — Main navigation bar
    ============================================================
    Props:
      $links      array  Required. Each item: flat link or dropdown.
                         Flat:     ['href' => '/path', 'label' => 'Label']
                         Dropdown: ['label' => 'Label', 'children' => [
                                       ['href' => '/path', 'label' => 'Child'],
                                       ['divider' => true],  // optional separator
                                   ]]
      $ctaLabel   string Optional. Button label. Default: 'Entrar'
      $ctaHref    string Optional. Button URL.
      $searchHref string Optional. Search icon URL. Shows a 🔍 icon link.
      $letter     string Optional. Logo mark character. Default: 'K'
      $title      string Optional. Site name. Default: 'Projeto Allan Kardec'
      $subtitle   string Optional. Tagline below title.

    Usage:
      <x-nav
          :links="[
              ['label' => 'Manuscritos', 'children' => [
                  ['href' => '/acesso/acervo', 'label' => 'Ver todos'],
                  ['href' => '/acesso/ano',    'label' => 'Por Ano'],
              ]],
              ['href' => '/biografias', 'label' => 'Biografias'],
          ]"
          search-href="/pesquisar"
          cta-label="Entrar"
          :cta-href="'/auth0Login'"
      />
--}}
@props([
    'links'      => [],
    'ctaLabel'   => 'Entrar',
    'ctaHref'    => null,
    'searchHref' => null,
    'letter'     => 'K',
    'title'      => 'Projeto Allan Kardec',
    'subtitle'   => 'Acervo Digital - UFJF',
])

<nav
    class="k-nav"
    id="k-nav"
    x-data="{ open: false, activeDropdown: null }"
    @click.outside="activeDropdown = null"
    aria-label="Navegação principal"
>

    {{-- Logo --}}
    <a href="/" class="k-nav__logo">
{{--        <span class="k-nav__logo-mark" aria-hidden="true">{{ $letter }}</span>--}}
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
                @php $hasChildren = isset($link['children']) && count($link['children']); @endphp
                @php $key = $link['label']; @endphp

                <li @if($hasChildren) class="k-nav__dropdown" @endif>
                    @if($hasChildren)
                        {{-- Dropdown trigger --}}
                        <button
                            class="k-nav__dropdown-trigger"
                            @click="activeDropdown = activeDropdown === '{{ $key }}' ? null : '{{ $key }}'"
                            :aria-expanded="activeDropdown === '{{ $key }}'"
                            aria-haspopup="true"
                        >
                            {{ $link['label'] }}
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        {{-- Dropdown panel --}}
                        <div
                            class="k-nav__dropdown-panel"
                            x-show="activeDropdown === '{{ $key }}'"
                            x-cloak
                            role="menu"
                        >
                            @foreach($link['children'] as $child)
                                @if(!empty($child['divider']))
                                    <div class="k-nav__dropdown-divider" role="separator"></div>
                                @else
                                    <a
                                        href="{{ $child['href'] }}"
                                        role="menuitem"
                                        @click="activeDropdown = null"
                                        @class(['active' => request()->is(ltrim($child['href'], '/'))])
                                    >{{ $child['label'] }}</a>
                                @endif
                            @endforeach
                        </div>

                    @else
                        {{-- Flat link --}}
                        <a
                            href="{{ $link['href'] }}"
                            @class(['active' => request()->is(ltrim($link['href'], '/'))])
                            @if(request()->is(ltrim($link['href'], '/'))) aria-current="page" @endif
                        >
                            {{ $link['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Right side: search icon + CTA + hamburger --}}
    <div style="display:flex;align-items:center;gap:0.5rem;">

{{--        @if($searchHref)--}}
{{--            <a href="{{ $searchHref }}" class="k-nav__search-icon hide-tablet" aria-label="Pesquisar">--}}
{{--                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">--}}
{{--                    <circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.5"/>--}}
{{--                    <line x1="10.5" y1="10.5" x2="14" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>--}}
{{--                </svg>--}}
{{--            </a>--}}
{{--        @endif--}}

        @if($ctaHref)
            <a href="{{ $ctaHref }}" class="k-nav__cta hide-tablet">
                {{ $ctaLabel }}
            </a>
        @endif

        {{-- Hamburger (tablet/mobile) --}}
        <button
            class="k-nav__hamburger"
            @click="open = !open; activeDropdown = null"
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
            @if(!empty($link['children']))
                <div class="k-nav__drawer-group">
                    <span class="k-nav__drawer-label">{{ $link['label'] }}</span>
                    @foreach($link['children'] as $child)
                        @if(empty($child['divider']))
                            <a href="{{ $child['href'] }}" @click="open = false" class="k-nav__drawer-child">
                                {{ $child['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <a href="{{ $link['href'] }}" @click="open = false">
                    {{ $link['label'] }}
                </a>
            @endif
        @endforeach

        @if($ctaHref)
            <a href="{{ $ctaHref }}" @click="open = false" class="k-nav__drawer-cta">
                {{ $ctaLabel }}
            </a>
        @endif
    </div>
</nav>
