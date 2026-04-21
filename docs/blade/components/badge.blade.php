{{--
    x-badge — Small label tag
    ============================================================
    Props:
      $variant  string  Optional. 'default' | 'gold' | 'outline' | 'parchment'.
                        Default: 'default'.

    Usage:
      <x-badge>Museu AKOL</x-badge>
      <x-badge variant="gold">Novo</x-badge>
      <x-badge variant="parchment">Carta</x-badge>
--}}
@props([
    'variant' => 'default',
])

<span {{ $attributes->merge(['class' => "k-badge k-badge--{$variant}"]) }}>
    {{ $slot }}
</span>
