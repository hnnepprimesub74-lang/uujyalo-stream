<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@uujyalostream.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '9800000000',
            ]
        );

        $netflix = Product::firstOrCreate(['slug' => 'netflix'], ['name' => 'Netflix', 'is_active' => true]);
        $spotify = Product::firstOrCreate(['slug' => 'spotify'], ['name' => 'Spotify', 'is_active' => true]);

        $netflixPlans = [
            ['name' => 'Mobile', 'slug' => 'netflix-mobile', 'duration_days' => 30, 'price' => 299, 'monthly_cost' => 3.99, 'sort_order' => 1],
            ['name' => 'Basic', 'slug' => 'netflix-basic', 'duration_days' => 30, 'price' => 499, 'monthly_cost' => 6.99, 'sort_order' => 2],
            ['name' => 'Standard', 'slug' => 'netflix-standard', 'duration_days' => 30, 'price' => 799, 'monthly_cost' => 15.49, 'sort_order' => 3],
            ['name' => 'Premium', 'slug' => 'netflix-premium', 'duration_days' => 30, 'price' => 1199, 'monthly_cost' => 22.99, 'sort_order' => 4],
        ];

        foreach ($netflixPlans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['product_id' => $netflix->id, 'is_active' => true])
            );
        }

        $spotifyPlans = [
            ['name' => 'Individual', 'slug' => 'spotify-individual', 'duration_days' => 30, 'price' => 399, 'monthly_cost' => 11.99, 'sort_order' => 1],
            ['name' => 'Family', 'slug' => 'spotify-family', 'duration_days' => 30, 'price' => 699, 'monthly_cost' => 19.99, 'sort_order' => 2],
        ];

        foreach ($spotifyPlans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['product_id' => $spotify->id, 'is_active' => true])
            );
        }
    }
}
