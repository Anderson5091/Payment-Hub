<h2>Paiement – Montant: {{ $amount }} HTG</h2>

<form method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <select name="method" required>
        <option value="">Choisir paiement</option>
        <option value="moncash">MonCash</option>
        <option value="natcash">NatCash</option>
        <option value="bank_online">Virement bancaire</option>
    </select>

    <input name="src_wallet_number" placeholder="Numéro portefeuille">
    <input name="src_wallet_name" placeholder="Nom portefeuille">

    <input name="transaction_number" placeholder="Numéro transaction">

    <input type="file" name="proof" required>

    <button type="submit">Envoyer paiement</button>
</form>
