<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shopNames = [
            '全聯福利中心',
            '家樂福',
            '7-11',
            '全家便利商店',
            'OK超商',
            '萊爾富',
            '好市多',
            '大潤發',
            '頂好超市',
            '美廉社',
        ];

        return [
            'name' => fake()->randomElement($shopNames) . ' ' . fake()->city(),
        ];
    }
}
