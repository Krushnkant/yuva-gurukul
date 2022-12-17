<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'role' => 1,
            'zone_id' => 2,
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => '',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'decrypted_password' => '123456789',
        ]);
        User::create([
            'role' => 2,
            'zone_id' => 2,
            'first_name' => 'Swami',
            'middle_name' => 'Male',
            'last_name' => '',
            'gender' => 2,
            'email' => 'subadmin1@gmail.com',
            'password' => Hash::make('123456789'),
            'decrypted_password' => '123456789',
            'is_delete' => 1,
        ]);
        User::create([
            'role' => 2,
            'zone_id' => 2,
            'first_name' => 'Swami',
            'middle_name' => 'female',
            'last_name' => '',
            'email' => 'subadmin2@gmail.com',
            'password' => Hash::make('123456789'),
            'decrypted_password' => '123456789',
            'is_delete' => 1,
        ]);
    }
}
