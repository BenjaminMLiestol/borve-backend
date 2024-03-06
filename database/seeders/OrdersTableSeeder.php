<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $firstUserId = DB::table('users')->value('user_id');
        $customerIds = DB::table('customers')->pluck('customer_id')->toArray();

        foreach ($customerIds as $customerId) {
            for ($i = 0; $i < 5; $i++) {
                DB::table('orders')->insert([
                    'order_id' => $faker->uuid,
                    'customer_id' => $customerId,
                    'price' => $faker->randomFloat(2, 50, 500),
                    'address_from' => $faker->streetAddress,
                    'address_to' => $faker->streetAddress,
                    'start_time' => $faker->dateTimeBetween('-1 month', '+1 month'),
                    'status' => $faker->randomElement([0, 1]), // Assuming status 0 for pending, 1 for completed
                    'time_spent' => $faker->numberBetween(10, 120), // Time spent in minutes
                    'km_driven' => $faker->numberBetween(5, 50), // Distance driven in kilometers
                    'comment' => $faker->sentence,
                    'passenger_count' => $faker->numberBetween(1, 4), // Number of passengers
                    'created_by' => $firstUserId, // Set created_by to the first user's ID
                    'created_at' => now(),
                    'updated_at' => now(),
                    'completed_at' => $faker->randomElement([$faker->dateTimeBetween('-1 month', 'now'), null]), // Completion date or null
                ]);
            }
        }
    }
}
