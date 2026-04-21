{{--
    x-stat — A number + label statistic block
    ============================================================
    Props:
      $number   string  Required. The big number (e.g. '600+', '1800–1869').
      $label    string  Required. Label below number.
      $variant  string  Optional. 'dark' (default, on hero) | 'ink' (on light bg).

    Usage (in hero):
      <x-stat number="600+" label="Manuscritos" />
      <x-stat number="3"    label="Acervos" />

    Usage (on light background):
      <x-stat number="600+" label="Manuscritos" variant="ink" />
--}}
@props([
    'number'  => '',
    'label'   => '',
    'variant' => 'dark',
])

<div {{ $attributes->merge(['class' => "k-stat k-stat--{$variant}"]) }}>
    <div class="k-stat__number">{{ $number }}</div>
    <div class="k-stat__label">{{ $label }}</div>
</div>
