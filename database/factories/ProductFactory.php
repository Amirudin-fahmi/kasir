<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true); // Nama unik dengan 3 kata
        return [
            'name' => $name,
            'slug' => Product::generateUniqueSlug($name),
            'category_id' => fake()->numberBetween(1, 2),
            'stock' => fake()->numberBetween(1000, 10000),
            'price' => fake()->numberBetween(2000, 4000),
            'is_active' => fake()->boolean(),
            'image' => fake()->imageUrl(),
            'barcode' => fake()->unique()->ean13(), // Barcode harus unik
            'description' => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
