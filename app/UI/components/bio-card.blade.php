{{--
    x-bio-card — Biography entry card (dark surface)
    ============================================================
    Props:
      $name   string  Required. Person's full name.
      $role   string  Optional. Role or description.
      $href   string  Optional. Link to biography detail page.

    Usage:
      <x-bio-card
          name="Allan Kardec"
          role="Autor principal · 1804–1869"
          :href="route('biografias.show', $bio)"
      />
--}}
@props([
    'name' => '',
    'role' => null,
    'href' => null,
])

@php
    $tag    = $href ? 'a' : 'div';
    $letter = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'k-bio-card']) }}
>
    <div class="k-bio-card__initial" aria-hidden="true">{{ $letter }}</div>
    <div class="k-bio-card__body">
        <div class="k-bio-card__name">{{ $name }}</div>
        @if($role)
            <div class="k-bio-card__role">{{ $role }}</div>
        @endif
    </div>
</{{ $tag }}>
