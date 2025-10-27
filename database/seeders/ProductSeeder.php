<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $categories = [
            'Smartphone',
            'Laptop',
            'Televisi',
            'Kamera',
            'Aksesoris',
            'Tablet',
            'Headphone',
            'Speaker',
            'Smartwatch',
            'Monitor'
        ];

        $brands = [
            'Samsung',
            'Apple',
            'Sony',
            'Asus',
            'HP',
            'Acer',
            'Xiaomi',
            'Lenovo',
            'LG',
            'Canon',
            'Nikon',
            'JBL'
        ];

        $products = [];
        $sales = [];

        // 🔹 Generate 1000+ Produk
        for ($i = 1; $i <= 1000; $i++) {
            $category = $faker->randomElement($categories);
            $brand = $faker->randomElement($brands);

            $products[] = [
                'name' => "{$brand} {$category} " . strtoupper(Str::random(4)),
                'category' => $category,
                'price' => $faker->numberBetween(100000, 25000000),
                'stock' => $faker->numberBetween(10, 1000),
                'sold' => 0,
                'description' => $faker->sentence(12),
                'image' => "https://placehold.co/300x300.png?text=" . urlencode($brand),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ];
        }

        // Masukkan produk ke database
        DB::table('products')->insert($products);

        // Ambil semua id produk
        $productIds = DB::table('products')->pluck('id')->toArray();

        // 🔹 Generate Data Penjualan
        foreach ($productIds as $productId) {
            // Setiap produk punya 1–10 transaksi acak
            $numSales = rand(1, 10);

            for ($j = 0; $j < $numSales; $j++) {
                $quantity = $faker->numberBetween(1, 10);
                $price = DB::table('products')->where('id', $productId)->value('price');

                $sales[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'total' => $quantity * $price,
                    'sale_date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Masukkan penjualan ke database dalam batch untuk efisiensi
        $chunks = array_chunk($sales, 500);
        foreach ($chunks as $chunk) {
            DB::table('sales')->insert($chunk);
        }

        // 🔹 Update total produk terjual
        $soldCounts = DB::table('sales')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->get();

        foreach ($soldCounts as $sold) {
            DB::table('products')
                ->where('id', $sold->product_id)
                ->update(['sold' => $sold->total_sold]);
        }
    }
}
