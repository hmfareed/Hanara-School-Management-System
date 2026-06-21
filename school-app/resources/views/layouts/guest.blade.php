<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Hanara Schools')</title>
    <meta name="description" content="Hanara Schools Management System - Admin Portal">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-on-surface antialiased min-h-screen">
    @yield('content')
</body>
</html>
