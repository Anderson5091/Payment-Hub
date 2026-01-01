ll@extends('admin.layout')

@section('content')

<h2>Paiements en attente</h2>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Commande</th>
    <th>Méthode</th>
    <th>Montant</th>
    <th>Statut</th>
    <th>Action</th>
</tr>

@foreach($payments as $payment)
<tr>
    <td>{{ $payment->id }}</td>
    <td>{{ $payment->order_id }}</td>
    <td>{{ $payment->method }}</td>
    <td>{{ $payment->amount }}</td>
    <td>{{ $payment->status }}</td>
    <td>
        <a href="/admin/payments/{{ $payment->id }}">Voir</a>
    </td>
</tr>
@endforeach

</table>

@endsection
