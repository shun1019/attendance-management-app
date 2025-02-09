<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 1, // 管理者
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '一般ユーザー',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 0, // 一般ユーザー
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
