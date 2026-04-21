{{--
    x-mobile-nav — Fixed bottom navigation for mobile
    ============================================================
    Props:
      $items  array  Required. Each: ['href' => '', 'label' => '', 'icon' => ''].

    Usage:
      <x-mobile-nav
          :items="[
              ['href' => route('home'),               'label' => 'Início',       'icon' => '⌂'],
              ['href' => route('manuscritos.index'),  'label' => 'Manuscritos',  'icon' => '📜'],
              ['href' => route('transcricoes.index'), 'label' => 'Transcrições', 'icon' => '✍'],
              ['href' => route('timeline'),           'label' => 'Timeline',     'icon' => '◷'],
              ['href' => route('sobre'),              'label' => 'Projeto',      'icon' => 'ℹ'],
          ]"
      />
--}}
@props([
    'items' => [],
])

<nav class="k-mobile-nav" aria-label="Navegação rápida">
    <div class="k-mobile-nav__inner">
        @foreach($items as $item)
            <a
                href="{{ $item['href'] }}"
                class="k-mobile-nav__item {{ request()->url() === $item['href'] ? 'is-active' : '' }}"
                @if(request()->url() === $item['href']) aria-current="page" @endif
            >
                <span class="k-mobile-nav__icon" aria-hidden="true">{{ $item['icon'] }}</span>
                <span class="k-mobile-nav__label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
