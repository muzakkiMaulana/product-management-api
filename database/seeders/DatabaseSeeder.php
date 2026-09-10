<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::firstOrCreate(['username' => 'catalog_seed'], ['password' => Str::random(64)]);

            foreach ($this->products() as [$title, $price, $category]) {
                $product = Product::firstOrNew(['title' => $title, 'created_by_id' => $user->id]);

                if ($product->exists) {
                    continue;
                }

                $product->fill([
                    'title' => $title,
                    'price' => $price,
                    'category' => $category,
                    'description' => 'Sample product for API testing.',
                    'images' => ['https://placehold.co/600x400/png?text='.rawurlencode($title)],
                ]);
                $product->created_by = $user->username;
                $product->created_by_id = $user->id;
                $product->save();
            }
        });

        Cache::forever('products:version', (string) Str::uuid());
    }

    private function products(): array
    {
        return [
            ['Cotton Shirt', '49.90', 'Clothes'],
            ['Denim Shirt', '69.50', 'Clothes'],
            ['Linen Shirt', '89.00', 'Clothes'],
            ['Wool Jacket', '159.90', 'Clothes'],
            ['Casual Trousers', '79.00', 'Clothes'],
            ['Wireless Keyboard', '129.90', 'Electronics'],
            ['USB Mouse', '29.95', 'Electronics'],
            ['Desk Speaker', '99.00', 'Electronics'],
            ['HD Monitor', '249.00', 'Electronics'],
            ['USB Cable', '9.90', 'Electronics'],
            ['Office Chair', '199.90', 'Furniture'],
            ['Oak Desk', '299.00', 'Furniture'],
            ['Coffee Table', '149.50', 'Furniture'],
            ['Bookshelf', '179.00', 'Furniture'],
            ['Bedside Cabinet', '119.00', 'Furniture'],
        ];
    }
}
