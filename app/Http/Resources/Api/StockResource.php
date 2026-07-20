<?php
namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'product_code'        => $this->product_code,
            'product_description' => $this->product_description,
            'selling_price'       => (float) $this->selling_price,
            'buying_price'        => (float) $this->buying_price,
            'quantity'            => (int) $this->quantity,
            'tax_code'            => $this->tax_code,
            'tax_id'              => (int) $this->tax_id,
            'tax_percentage'      => (float) $this->tax_percentage,
            'hs_code'             => $this->hs_code,
        ];
    }
}
