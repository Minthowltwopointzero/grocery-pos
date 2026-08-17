<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@grocerypos.test',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'cashier'],
            [
                'name' => 'Cashier One',
                'email' => 'cashier@grocerypos.test',
                'password' => Hash::make('cashier123'),
                'role' => 'cashier',
                'is_active' => true,
            ]
            
        );
        
        User::updateOrCreate(
            ['username' => 'mark'],
            [
                'name' => 'mark',
                'email' => 'mark@grocerypos.test',
                'password' => Hash::make('mark123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
