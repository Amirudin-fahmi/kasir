<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class; // Tambahkan ini

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'gender' => fake()->randomElement(['male', 'female']),
            'phone' => fake()->phoneNumber(),
            'birthday' => fake()->date(),
            'total_price' => fake()->randomFloat(2, 30000, 50000),
            'payment_method_id' => PaymentMethod::inRandomOrder()->first()?->id ?? 1, // Menggunakan ID yang valid
            'paid_amount' => fake()->randomFloat(2, 10000, 100000),
            'change_amount' => fake()->randomFloat(2, 0, 10000),
            'note' => fake()->sentence(),
        ];
    }
}
