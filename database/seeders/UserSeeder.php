<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use App\Models\State;
use App\Models\Wallet;
use App\Models\Currency;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\UserSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $num = 3;

        // Get random location and currency
        $state = State::inRandomOrder()->first();
        $city = City::inRandomOrder()->first();
        $currency = Currency::first();

        // Create admin user
        // $admin = User::create([
        //     'id' => Str::uuid(),
        //     'country_id' => $state->country_id,
        //     'state_id' => $state->id,
        //     'city' => 'Louis Hert',
        //     'currency_id' => $currency->id,
        //     'first_name' => 'John',
        //     'last_name' => 'Doe',
        //     'username' => 'johndoe',
        //     'email' => 'johndoe@example.com',
        //     'phone' => $faker->phoneNumber,
        //     'address' => $faker->streetAddress,
        //     'zipcode' => $faker->postcode,
        //     'ssn' => $faker->numerify('###-##-####'),
        //     'dob' => $faker->dateTimeBetween('-60 years', '-18 years'),
        //     'nationality' => $faker->country,
        //     'experience' => $faker->randomElement(['1 year', '2 years', '5 years', '10+ years']),
        //     'employed' => $faker->randomElement(['Yes', 'No']),
        //     'status' => 'active',
        //     'kyc' => 'approved',
        //     'id_number' => $faker->bothify('??######'),
        //     'front_id' => null,
        //     'back_id' => null,
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('password'),
        //     'blocked_at' => null,
        // ]);

        // Wallet::create([
        //     'id' => Str::uuid(),
        //     'user_id' => $admin->id,
        //     'balance' => 10000, // Admin gets higher balance
        // ]);

        // Create regular users with fake data
        for ($i = 0; $i < $num; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            
            $user = User::create([
                'id' => Str::uuid(),
                'country_id' => $state->country_id,
                'state_id' => $state->id,
                'city' => 'Lousi Street',
                'currency_id' => $currency->id,
                'first_name' => 'DEMO' . $firstName,
                'last_name' => 'TEST' . $lastName,
                'username' => strtolower($firstName . $lastName),
                'email' => strtolower($firstName . '.' . $lastName) . '@example.com',
                'phone' => $faker->phoneNumber,
                'address' => $faker->streetAddress,
                'zipcode' => $faker->postcode,
                'ssn' => $faker->numerify('###-##-####'),
                'dob' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'nationality' => $faker->country,
                'experience' => $faker->randomElement(['1 year', '2 years', '5 years', '10+ years']),
                'employed' => $faker->randomElement(['Yes', 'No']),
                'status' => 'active',
                'kyc' => 'pending',
                'id_number' => $faker->bothify('??######'),
                'front_id' => $faker->randomElement(['path/to/front_id.jpg', '']),
                'back_id' => $faker->randomElement(['path/to/back_id.jpg', '']),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'blocked_at' => $faker->randomElement([null, now()->subDays(rand(1, 30))]),
            ]);

            Wallet::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            UserSettings::create([
                'id' => Str::uuid(),
                'user_id' => $user->id
            ]);

            // // Store payment methods (adjust according to your storePayment method)
            // $user->storePayment('admin', []);
            // $user->storePayment('user', []);
        }

        $this->command->info('Successfully created 1 admin and '. $num .' regular users with fake data!');
    }
}
