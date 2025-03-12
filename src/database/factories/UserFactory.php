<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->lastName . ' ' . $this->faker->firstName, // 漢字の名前を生成
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // パスワードをハッシュ化
        ];
    }
}
