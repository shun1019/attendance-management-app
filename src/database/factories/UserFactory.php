<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition()
    {
        /**
         * 一般ユーザーを作成
         */
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 0,
        ];
    }

    /**
     * メール未認証状態のユーザーを生成
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * 管理者ユーザーを作成
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 1,
            ];
        });
    }
}
