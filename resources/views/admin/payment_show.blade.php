@extends('admin.layout')

@section('content')

<h2>Détail du paiement #{{ $payment->id }}</h2>

<ul>
    <li>Commande : {{ $payment->order_id }}</li>
    <li>Méthode : {{ $payment->method }}</li>
    <li>Montant : {{ $payment->amount }}</li>
    <li>Statut : {{ $payment->status }}</li>

    <li>Wallet source : {{ $payment->src_wallet_number }} ({{ $payment->src_wallet_name }})</li>
    <li>Wallet destination : {{ $payment->dest_wallet_number }} ({{ $payment->dest_wallet_name }})</li>

    <li>Transaction : {{ $payment->transaction_number }}</li>

    <li>
        Preuve :
        <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank">
            Voir le fichier
        </a>
    </li>
</ul>

@if($payment->status === 'pending')
<form method="POST" action="/admin/payments/{{ $payment->id }}/validate">
    @csrf
    <button name="status" value="validated">Valider</button>
    <button name="status" value="rejected">Rejeter</button>
</form>
@endif

@endsection
