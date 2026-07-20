<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StockResource;
use App\Models\Stock;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StockController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Stock::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tax_code')) {
            $query->where('tax_code', strtoupper($request->tax_code));
        }

        if ($request->boolean('in_stock_only')) {
            $query->where('quantity', '>', 0);
        }

        $stocks = $query->orderBy('product_description')
                        ->paginate($request->get('per_page', 50));

        return $this->success(
            StockResource::collection($stocks),
            'Stock retrieved.',
            200,
            [
                'current_page' => $stocks->currentPage(),
                'last_page'    => $stocks->lastPage(),
                'per_page'     => $stocks->perPage(),
                'total'        => $stocks->total(),
            ]
        );
    }

    public function show(string $productCode)
    {
        $stock = Stock::where('product_code', $productCode)->first();
        if (!$stock) {
            return $this->error('Product not found.', 404);
        }
        return $this->success(new StockResource($stock));
    }
}
