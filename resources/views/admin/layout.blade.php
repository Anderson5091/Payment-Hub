<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Payment Hub – Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<header>
    <h1>Payment Hub – Dashboard Admin</h1>

    <nav>
        <a href="/admin/dashboard">Paiements</a> |
        <a href="/admin/users">Admins</a> |
        <a href="/admin/logs">Logs</a> |
        <form method="POST" action="/admin/logout" style="display:inline">
            @csrf
            <button type="submit">Déconnexion</button>
        </form>
    </nav>

    <hr>
</header>

<main>
    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul style="color:red">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @yield('content')
</main>

</body>
</html>
