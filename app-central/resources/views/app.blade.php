<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <meta name="theme-color" content="#4f46e5">
        <link rel="manifest" href="/manifest.json">

        <title inertia>{{ config('app.name', 'Sagaretxe') }}</title>

        <!-- SEO & Open Graph (para compartir en redes/WhatsApp) -->
        <meta name="description" content="Gestión de Reservas y Club Sagaretxe - Panel de administración">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Sagaretxe">
        <meta property="og:title" content="Gestión de Reservas · Club Sagaretxe">
        <meta property="og:description" content="Panel de administración de reservas y gestión del Club Sagaretxe">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="Gestión de Reservas · Club Sagaretxe">
        <meta name="twitter:description" content="Panel de administración de reservas y gestión del Club Sagaretxe">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
