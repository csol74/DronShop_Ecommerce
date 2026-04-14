<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acceso') — DronShop</title>
    <link rel="stylesheet" href="{{ asset('css/dronshop.css') }}">
</head>
<body>
    {{ $slot }}
</body>
</html>
