@extends('admin.layout')

@section('content')

<h2>Administrateurs</h2>

<h3>Ajouter un admin</h3>

<form method="POST">
    @csrf

    <input name="name" placeholder="Nom" required><br><br>
    <input name="email" type="email" placeholder="Email" required><br><br>
    <input name="password" type="password" placeholder="Mot de passe" required><br><br>

    <button type="submit">Ajouter</button>
</form>

<hr>

<h3>Liste des admins</h3>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Email</th>
</tr>

@foreach($admins as $admin)
<tr>
    <td>{{ $admin->id }}</td>
    <td>{{ $admin->name }}</td>
    <td>{{ $admin->email }}</td>
</tr>
@endforeach
</table>

@endsection
