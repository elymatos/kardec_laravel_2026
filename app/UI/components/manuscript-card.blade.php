{{--
    x-manuscript-card — Card for a manuscript item
    ============================================================
    Props:
      $id          string|int  Required. Document identifier (e.g. '108').
      $title       string      Required. Document title.
      $acervo      string      Required. Archive name.
      $year        string      Optional. Date/period (e.g. 'c. 1858').
      $category    string      Optional. Document type (e.g. 'Carta').
      $href        string      Required. URL to the manuscript detail page.
      $imageUrl    string      Optional. Thumbnail image URL.
      $color       string      Optional. Accent hex color for SVG placeholder.
      $delay       int         Optional. Reveal animation delay 1–4. Default: 1.

    Usage:
      <x-manuscript-card
          :id="$manuscript->identifier"
          :title="$manuscript->title"
          :acervo="$manuscript->acervo->name"
          :year="$manuscript->year_label"
          :category="$manuscript->category"
          :href="route('manuscritos.show', $manuscript)"
          :image-url="$manuscript->thumbnail_url"
      />
--}}
@props([
    'id'       => '',
    'title'    => '',
    'acervo'   => '',
    'year'     => null,
    'category' => null,
    'href'     => '#',
    'imageUrl' => null,
    'color'    => '#b8892a',
    'delay'    => 1,
])

<article {{ $attributes->merge(['class' => "k-ms-card reveal delay-{$delay}"]) }}>
    <a href="{{ $href }}" class="k-ms-card__link" aria-label="{{ $title }}">

        {{-- Image area --}}
        <div class="k-ms-card__img">
            @if($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="Miniatura de {{ $title }}"
                    loading="lazy"
                    width="280"
                    height="373"
                >
            @else
                {{-- SVG placeholder when no image is available --}}
                <div class="k-ms-card__img-inner" aria-hidden="true">
                    <svg width="80" height="100" viewBox="0 0 80 100" fill="none" class="k-ms-card__svg">
                        <rect x="10" y="8" width="60" height="84" rx="2" stroke="{{ $color }}" stroke-width="1"/>
                        @for($i = 0; $i < 12; $i++)
                            <line
                                x1="16" y1="{{ 22 + $i * 5 }}"
                                x2="{{ $i % 3 === 0 ? 55 : 64 }}" y2="{{ 22 + $i * 5 }}"
                                stroke="{{ $color }}" stroke-width="0.8"
                                opacity="{{ 0.5 + $i * 0.04 }}"
                            />
                        @endfor
                    </svg>
                </div>
            @endif

            {{-- Archive badge --}}
            <span class="k-ms-card__badge">
                <x-badge variant="default">{{ $acervo }}</x-badge>
            </span>
        </div>

        {{-- Body --}}
        <div class="k-ms-card__body">
            @if($id)
                <div class="k-ms-card__id">Nº&nbsp;{{ $id }}</div>
            @endif
            <h3 class="k-ms-card__title">{{ $title }}</h3>
            <div class="k-ms-card__meta">
                @if($year)
                    <span class="k-ms-card__date">{{ $year }}</span>
                @endif
                @if($category)
                    <span class="k-ms-card__type">{{ $category }}</span>
                @endif
            </div>
        </div>

    </a>
</article>
