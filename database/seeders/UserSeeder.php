<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Emmanuel Elioth Lulandala',
                'email' => 'elulandala@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Secretary',
            ],
            [
                'name' => 'Kanti Ambrose Kimario',
                'email' => 'kantkim2011@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Chairman',
            ],
            [
                'name' => 'Sigfred A Ngereza',
                'email' => 'sigfridngereza@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Investment and Savings Officer',
            ],
            [
                'name' => 'James C. Magesa',
                'email' => 'magesaj58@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Loan and Social Welfare Officer',
            ],
            [
                'name' => 'David Ngungila',
                'email' => 'davidngungila@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'IT and Marketing Officer (Admin)',
            ],
            [
                'name' => 'Lukelo Neno',
                'email' => 'lukelohimself12@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Marketing Officer',
            ],
            [
                'name' => 'Witness Clement Sam',
                'email' => 'witneysam21@gmail.com',
                'password' => Hash::make('password'),
                'position' => 'Accountant',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}
