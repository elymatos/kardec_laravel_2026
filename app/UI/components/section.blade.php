{{--
    x-section — Section wrapper with surface variants
    ============================================================
    Props:
      $id       string  Optional. HTML id attribute.
      $variant  string  Optional. 'light' | 'dark' | 'parchment'. Default: 'light'.
      $padding  string  Optional. 'default' | 'sm' | 'none'. Default: 'default'.
      $class    string  Optional. Additional CSS classes.

    Usage:
      <x-section id="manuscritos" variant="light">
          ...
      </x-section>

      <x-section id="transcricoes" variant="dark">
          ...
      </x-section>
--}}
@props([
    'id'      => null,
    'variant' => 'light',
    'padding' => 'default',
])

@php
    $surfaceClass = match($variant) {
        'dark'      => 'surface-dark',
        'parchment' => 'surface-parchment',
        default     => 'surface-page',
    };

    $paddingClass = match($padding) {
        'sm'   => '',
        'none' => '',
        default => 'section',
    };

    $paddingStyle = match($padding) {
        'sm'   => 'padding-block:clamp(2rem,4vw,4rem);padding-inline:var(--section-px)',
        'none' => '',
        default => '',
    };
@endphp

<section
    @if($id) id="{{ $id }}" @endif
    {{ $attributes->merge(['class' => "k-section-wrap {$surfaceClass} {$paddingClass}"]) }}
    @if($paddingStyle) style="{{ $paddingStyle }}" @endif
>
    <div class="container">
        {{ $slot }}
    </div>
</section>
