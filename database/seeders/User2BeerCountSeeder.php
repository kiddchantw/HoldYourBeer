<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Beer;
use App\Models\UserBeerCount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class User2BeerCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 為 user_id = 2 的使用者建立測試資料
        $user = User::find(2);
        
        if (!$user) {
            $this->command->warn('User with ID 2 not found.');
            return;
        }

        // 取得或建立品牌
        $guinness = Brand::firstOrCreate(['name' => 'Guinness']);
        $brewdog = Brand::firstOrCreate(['name' => 'Brewdog']);
        $asahi = Brand::firstOrCreate(['name' => 'Asahi']);
        $heineken = Brand::firstOrCreate(['name' => 'Heineken']);

        // 取得或建立啤酒
        $guinnessDraught = Beer::firstOrCreate([
            'brand_id' => $guinness->id,
            'name' => 'Draught'
        ], ['style' => 'Stout']);

        $brewdogPunkIPA = Beer::firstOrCreate([
            'brand_id' => $brewdog->id,
            'name' => 'Punk IPA'
        ], ['style' => 'IPA']);

        $asahiSuperDry = Beer::firstOrCreate([
            'brand_id' => $asahi->id,
            'name' => 'Super Dry'
        ], ['style' => 'Lager']);

        $heinekenOriginal = Beer::firstOrCreate([
            'brand_id' => $heineken->id,
            'name' => 'Original'
        ], ['style' => 'Lager']);

        // 建立使用者啤酒消費記錄
        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $guinnessDraught->id,
        ], [
            'count' => 5,
            'last_tasted_at' => Carbon::now()->subDays(2),
        ]);

        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $brewdogPunkIPA->id,
        ], [
            'count' => 3,
            'last_tasted_at' => Carbon::now()->subDays(1),
        ]);

        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $asahiSuperDry->id,
        ], [
            'count' => 8,
            'last_tasted_at' => Carbon::now()->subDays(3),
        ]);

        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $heinekenOriginal->id,
        ], [
            'count' => 2,
            'last_tasted_at' => Carbon::now()->subDays(5),
        ]);

        $this->command->info('User beer counts seeded successfully for user ID: ' . $user->id);
    }
}
