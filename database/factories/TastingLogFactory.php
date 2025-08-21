<?php

namespace Database\Factories;

use App\Models\TastingLog;
use App\Models\UserBeerCount;
use Illuminate\Database\Eloquent\Factories\Factory;

class TastingLogFactory extends Factory
{
    protected $model = TastingLog::class;

    public function definition()
    {
        return [
            'user_beer_count_id' => UserBeerCount::factory(),
            'action' => $this->faker->randomElement(['initial', 'increment', 'decrement']),
            'note' => $this->faker->sentence,
            'tasted_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
