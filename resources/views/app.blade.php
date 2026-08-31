<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1f7a3d">

    <title inertia>{{ config('app.name', 'فلفلی ساوه') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-screen bg-cream-100 antialiased">
    @inertia
</body>
</html>
