<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SMS Gateway Manager</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('sms-bridge.index') }}">SMS Admin</a>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
</body>

</html>
