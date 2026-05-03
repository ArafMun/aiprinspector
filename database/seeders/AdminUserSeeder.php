<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'arafatmunna58@gmail.com'],
            [
                'name' => 'Md. Arafat Uddin',
                'password' => Hash::make('abumuaz21'),
                'is_admin' => true,
            ]
        );

        // Assign admin role to the user
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole && !$user->roles()->where('role_id', $adminRole->id)->exists()) {
            $user->roles()->attach($adminRole->id);
        }
    }
}
