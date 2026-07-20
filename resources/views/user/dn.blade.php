@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h2>Debit Note #{{ $debitNote->id }}</h2>
        <div>
            <a href="{{ route('debit_note.download', $debitNote->id) }}" class="btn btn-success">Download PDF</a>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <table class="table table-bordered">
        <tr><th>Original Invoice Number</th><td>{{ $debitNote->original_invoice }}</td></tr>
        <tr><th>Created At</th><td>{{ $debitNote->created_at }}</td></tr>
    </table>
</div>
@endsection
