{{--
    x-timeline-event — A single event card in the timeline
    ============================================================
    Props:
      $year          string  Required. Year label (e.g. '1858').
      $title         string  Required. Event/document title.
      $acervo        string  Optional. Archive name.
      $category      string  Optional. Category label (e.g. 'Carta').
      $categoryColor string  Optional. Category accent color (hex).
      $href          string  Optional. Link to document.

    Usage (in a loop):
      @foreach($events as $event)
          <x-timeline-event
              :year="$event->year"
              :title="$event->title"
              :acervo="$event->acervo"
              :category="$event->category"
              :category-color="$event->category_color"
              :href="route('manuscritos.show', $event)"
          />
      @endforeach
--}}
@props([
    'year'          => '',
    'title'         => '',
    'acervo'        => null,
    'category'      => null,
    'categoryColor' => null,
    'href'          => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'k-timeline__event']) }}
>
    <div class="k-timeline__event-year" aria-label="Ano: {{ $year }}">{{ $year }}</div>
    <div class="k-timeline__event-body">
        <div class="k-timeline__event-title">{{ $title }}</div>
        @if($acervo)
            <div class="k-timeline__event-acervo">{{ $acervo }}</div>
        @endif
        @if($category)
            <span
                class="k-timeline__event-type"
                @if($categoryColor) style="color: {{ $categoryColor }}" @endif
            >
                {{ $category }}
            </span>
        @endif
    </div>
</{{ $tag }}>
