@extends('layouts.app')

@section('title', 'Edit Client')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil-square me-2 text-success"></i>Edit Client</h4>
            <div class="sub">{{ $client->name }}</div>
        </div>
        <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Client</a>
    </div>

    <form action="{{ route('clients.update', $client) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')
        @include('clients._form', ['client' => $client])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
