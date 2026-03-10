<?php

namespace Database\Factories;

use App\Models\Test;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    protected $model = Test::class;

    public function definition()
    {
        $fakerJa = FakerFactory::create('ja_JP');

        return [
            'name' => $fakerJa->lastName . ' ' . $fakerJa->firstName,
        ];
    }
}
