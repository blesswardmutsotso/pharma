@extends('layouts.app')

@section('title', 'Quotation ' . $quotation->quote_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-ruled me-2 text-success"></i>Quotation {{ $quotation->quote_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF</a>
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Client</div><div class="value">{{ $quotation->client?->name }}</div></div>
            <div><div class="label">Quote Date</div><div class="value">{{ $quotation->quote_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Valid Until</div><div class="value">{{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</div></div>
            <div>
                <div class="label">Converted Sales Order</div>
                <div class="value">
                    @if ($quotation->convertedSalesOrder)
                        <a href="{{ route('sales-orders.show', $quotation->convertedSalesOrder) }}">{{ $quotation->convertedSalesOrder->so_number }}</a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($quotation->canBeConverted())
        <div class="mb-4">
            <form action="{{ route('quotations.convert', $quotation) }}" method="POST"
                  data-confirm="Convert {{ $quotation->quote_number }} into a draft sales order?" data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-arrow-right-circle me-1"></i>Convert to Sales Order</button>
            </form>
        </div>
    @endif

    <div class="table-card">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-list-ul me-1 text-success"></i>Line Items
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
