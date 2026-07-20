<style>
table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px; font-weight: bold; color: #000; }
th { background: #d0d0d0; color: #000; font-weight: 800; padding: 8px; border: 1px solid #000; }
td { padding: 7px 8px; border: 1px solid #555; font-weight: bold; color: #000; }
tr:nth-child(even) td { background: #f5f5f5; }
</style>
<table>
    <thead>
        <tr>
            <th>Sale ID</th>
            <th>User</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->user->name ?? 'N/A' }}</td>
                <td>{{ $sale->amount }}</td>
                <td>{{ $sale->currency }}</td>
                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
    <i class="bi bi-exclamation-circle-fill"></i>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
