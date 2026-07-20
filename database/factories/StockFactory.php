<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        $sellingPrice = $this->faker->randomFloat(2, 1, 50);

        return [
            'product_code' => strtoupper($this->faker->unique()->bothify('PRD-####')),
            'product_description' => $this->faker->words(3, true),
            'buying_price' => $sellingPrice * 0.7,
            'selling_price' => $sellingPrice,
            'quantity' => 0,
            'tax_code' => 'EX',
            'tax_id' => 1,
            'tax_percentage' => 0.0,
            'tax_amount' => 0.0,
            'sales_amount_with_tax' => $sellingPrice,
            'hs_code' => '00000000',
            'reorder_point' => 0,
            'reorder_qty' => 0,
            'requires_batch_tracking' => true,
        ];
    }
}
