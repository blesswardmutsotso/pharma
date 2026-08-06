@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-gear-fill me-2 text-success"></i>System Settings</h4>
            <div class="sub">Admin-only configuration</div>
        </div>
    </div>

    <div class="detail-card">
        <div class="card-title">Exchange Rates</div>
        <p class="text-muted" style="font-size:.85rem;">
            All exchange rates are measured against USD — enter how many units of the currency equal 1 USD.
            Documents and reports in a non-USD currency are converted to USD using these rates wherever amounts
            are totalled together (dashboards, analytics, sales summary, debtors ageing, revenue reports).
        </p>

        <div class="table-responsive mb-3">
            <table class="table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th class="text-end">Rate (per 1 USD)</th>
                        <th>Last Updated By</th>
                        <th>Last Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="inv-no">USD</span></td>
                        <td class="text-end">1.000000</td>
                        <td colspan="2" class="text-muted">Base currency — not configurable</td>
                    </tr>
                    @foreach (\App\Models\ExchangeRate::CONVERTIBLE_CURRENCIES as $currency)
                        @php($rate = $exchangeRates[$currency] ?? null)
                        <tr>
                            <td><span class="inv-no">{{ $currency }}</span></td>
                            <td class="text-end">{{ $rate ? number_format($rate->rate_to_usd, 6) : '—' }}</td>
                            <td>{{ $rate?->updatedBy?->name ?? '—' }}</td>
                            <td>{{ $rate?->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('admin.exchange-rates.update') }}" method="POST" class="row g-3 align-items-end" style="max-width:520px;">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <select name="currency_code" class="form-select" required>
                    @foreach (\App\Models\ExchangeRate::CONVERTIBLE_CURRENCIES as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Rate — units per 1 USD</label>
                <input type="number" step="0.000001" min="0.000001" name="rate_to_usd" class="form-control" placeholder="e.g. 30.5" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Save Rate</button>
            </div>
        </form>
    </div>

</div>
@endsection
