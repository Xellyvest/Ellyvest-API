<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            CountryStateCityTableSeeder::class, // php artisan db:seed --class=CountryStateCityTableSeeder
            // CurrencySeeder::class, // php artisan db:seed --class=CurrenciesSeeder
            UserSeeder::class, // php artisan db:seed --class=UserSeeder
            AssetSeeder::class, // php artisan db:seed --class=AssetSeeder
            SavingsAccountSeeder::class, // php artisan db:seed --class=SavingsAccountSeeder  php artisan migrate:refresh --path=/database/migrations/2025_02_03_090958_create_savings_accounts_table.php
            AdminSeeder::class, // php artisan db:seed --class=AdminSeeder
            ArticleSeeder::class, // php artisan db:seed --class=ArticleSeeder  php artisan migrate:refresh --path=/database/migrations/2025_02_01_140812_create_transactions_table.php
            NationalitiesSeeder::class, // php artisan db:seed --class=NationalitiesSeeder  php artisan migrate:refresh --path=/database/migrations/2025_02_03_003138_create_assets_table.php
        ]);

        // php artisan migrate:refresh --path=/database/migrations/2025_02_03_003138_create_assets_table.php
        // php artisan migrate:refresh --path=/database/migrations/2025_03_05_095306_create_positions_table.php
        // php artisan migrate:refresh --path=/database/migrations/2025_02_07_112325_create_trades_table.php

        // php artisan migrate:refresh --path=/database/migrations/2025_10_13_210914_create_bank_accounts_table.php
    }
}
