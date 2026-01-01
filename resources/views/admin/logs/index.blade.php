@extends('admin.layout')

@section('content')

<h2>Logs du système</h2>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Événement</th>
    <th>Message</th>
    <th>IP</th>
    <th>Date</th>
</tr>

@foreach($logs as $log)
<tr>
    <td>{{ $log->id }}</td>
    <td>{{ $log->event }}</td>
    <td>{{ $log->message }}</td>
    <td>{{ $log->ip_address }}</td>
    <td>{{ $log->created_at }}</td>
</tr>
@endforeach

</table>

@endsection
