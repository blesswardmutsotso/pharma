@extends('layouts.app')

@section('title', 'New Client')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-people me-2 text-success"></i>New Client</h4>
            <div class="sub">Add a customer to the master list</div>
        </div>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Clients</a>
    </div>

    <form action="{{ route('clients.store') }}" method="POST" class="form-card">
        @csrf
        @include('clients._form', ['client' => null])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Client</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
