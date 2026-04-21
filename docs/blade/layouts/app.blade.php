<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>{{ $title ?? 'Projeto Allan Kardec' }} — Manuscritos e Transcrições</title>
    <meta name="description" content="{{ $description ?? 'Acervo digital de manuscritos e documentos originais de Allan Kardec (1804–1869). Projeto vinculado à UFJF.' }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Spectral:ital,wght@0,300;0,400;0,500;1,300;1,400&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">

    {{-- Design System CSS --}}
    @vite([
        'resources/css/kardec-tokens.css',
        'resources/css/kardec-base.css',
        'resources/css/kardec-components.css',
        'resources/js/app.js',
    ])

    {{-- Page-specific head content --}}
    {{ $head ?? '' }}
</head>
<body class="{{ $bodyClass ?? '' }}">

    {{-- Navigation --}}
    <x-nav
        :links="[
            ['href' => route('manuscritos.index'), 'label' => 'Manuscritos'],
            ['href' => route('transcricoes.index'), 'label' => 'Transcrições'],
            ['href' => route('timeline'), 'label' => 'Timeline'],
            ['href' => route('sobre'), 'label' => 'O Projeto'],
            ['href' => route('biografias.index'), 'label' => 'Biografias'],
        ]"
        cta-label="Entrar"
        :cta-href="route('login')"
    />

    {{-- Main content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-footer />

    {{-- Mobile bottom nav --}}
    <x-mobile-nav
        :items="[
            ['href' => route('home'), 'label' => 'Início', 'icon' => '⌂'],
            ['href' => route('manuscritos.index'), 'label' => 'Manuscritos', 'icon' => '📜'],
            ['href' => route('transcricoes.index'), 'label' => 'Transcrições', 'icon' => '✍'],
            ['href' => route('timeline'), 'label' => 'Timeline', 'icon' => '◷'],
            ['href' => route('sobre'), 'label' => 'Projeto', 'icon' => 'ℹ'],
        ]"
    />

    {{-- Scroll reveal script (no framework dependency) --}}
    <script>
    (function () {
        const observer = new IntersectionObserver(
            (entries) => entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            }),
            { threshold: 0.1 }
        );
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    })();
    </script>

    {{-- Page-specific scripts --}}
    {{ $scripts ?? '' }}

</body>
</html>
