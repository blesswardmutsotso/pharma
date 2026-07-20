<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-' . $this->faker->unique()->numerify('####'),
            'supplier_id' => Supplier::factory(),
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
            'notes' => $this->faker->sentence(),
            'requested_by' => 1,
        ];
    }
}
