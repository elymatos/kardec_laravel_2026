<!DOCTYPE html>
<html id="projetokardec" class="" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-MGYNKXNTVZ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag("js", new Date());
        gtag("config", "G-MGYNKXNTVZ");
    </script>
    <meta name="Generator" content="Laravel 13.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>{!! config('pk.pageTitle') !!}</title>
    <meta name="description" content="{!! config('pk.mainTitle') !!}">

    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <style>
        body { visibility: hidden; opacity: 0; }
    </style>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Spectral:ital,wght@0,300;0,400;0,500;1,300;1,400&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">

{{--    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Mono:wght@100..900&display=swap" rel="stylesheet">--}}

    <!--
    <script type="text/javascript" src="/scripts/htmx/htmx.min.js"></script>
    -->

    <script type="text/javascript" src="https://unpkg.com/htmx.org@2.0.3"></script>
{{--    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>--}}

{{--    <script type="text/javascript" src="/scripts/maestro/manager.js"></script>--}}
{{--    <script type="text/javascript" src="/scripts/pdf/jspdf.debug.js"></script>--}}
{{--    <script type="text/javascript" src="/scripts/pdf/html2canvas.min.js"></script>--}}
{{--    <script type="text/javascript" src="/scripts/pdf/html2pdf.min.js"></script>--}}
{{--    <script defer src="/scripts/alpinejs/cdn.min.js"></script>--}}

{{--    <script type="text/javascript" src="/scripts/jquery-easyui-1.10.17/jquery.easyui.min.js"></script>--}}

{{--    <script src="/scripts/animation-timeline-js/lib/animation-timeline.js?v=2" type="text/javascript"></script>--}}

{{--    <script src="/scripts/fomantic-ui/semantic.min.js"></script>--}}

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script src="scripts/timelines-chart/timelines-chart.js"></script>
    <script src="/scripts/viewerjs/viewer.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>


    @vite(['resources/js/app.js'])

    {{-- Page-specific head content --}}
    {{ $head ?? '' }}

</head>

<body class="{{ $bodyClass ?? '' }}">
    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
>
    {{-- Navigation --}}
    <x-nav
        :links="[
            ['label' => 'Manuscritos', 'children' => [
                ['href' => '/acesso/acervo',    'label' => 'Por Acervo'],
                ['href' => '/acesso/ano',        'label' => 'Por Ano'],
                ['href' => '/acesso/categoria',  'label' => 'Por Categoria'],
                ['href' => '/acesso/id',         'label' => 'Por Identificador'],
                ['divider' => true],
                ['href' => '/acesso/recente',    'label' => 'Publicações Recentes'],
            ]],
            ['label' => 'Explorar', 'children' => [
                ['href' => '/timeline',   'label' => 'Timeline'],
                ['href' => '/imagens',    'label' => 'Imagens'],
                ['href' => '/biografias', 'label' => 'Biografias'],
            ]],
            ['href' => '/pesquisar', 'label' => 'Pesquisar'],
            ['label' => 'O Projeto', 'children' => [
                ['href' => '/apresentacao',      'label' => 'Apresentação'],
                ['href' => '/acervos',           'label' => 'Acervos'],
                ['href' => '/equipe',            'label' => 'Equipe'],
                ['divider' => true],
                ['href' => '/politicaeditorial', 'label' => 'Política Editorial'],
                ['href' => '/condicoesdeuso',    'label' => 'Condições de Uso'],
                ['href' => '/contato',           'label' => 'Contato'],
                ['divider' => true],
                ['href' => '/bibliografia',      'label' => 'Bibliografia'],
            ]],
        ]"
        search-href="/pesquisar"
        cta-label="Entrar"
        cta-href="/auth0Login"
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
            ['href' => '/', 'label' => 'Início', 'icon' => '⌂'],
            ['href' => '/acesso/acervo', 'label' => 'Manuscritos', 'icon' => '📜'],
            ['href' => '/pesquisar', 'label' => 'Transcrições', 'icon' => '✍'],
            ['href' => '/timeline', 'label' => 'Timeline', 'icon' => '◷'],
            ['href' => '/apresentacao', 'label' => 'Projeto', 'icon' => 'ℹ'],
        ]"
    />

    {{-- Body reveal + scroll reveal (no framework dependency) --}}
    <script>
        (function () {
            function revealBody() {
                document.body.style.transition = 'opacity 0.25s ease';
                document.body.style.visibility = 'visible';
                document.body.style.opacity = '1';
            }

            // Wait for all resources (scripts, stylesheets) to finish loading
            if (document.readyState === 'complete') {
                revealBody();
            } else {
                window.addEventListener('load', revealBody);
            }

            // Fallback: reveal after 4 s so the page is never permanently hidden
            setTimeout(revealBody, 4000);

            // Scroll reveal via IntersectionObserver
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
