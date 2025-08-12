<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Beer;
use App\Models\UserBeerCount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserBeerCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test user
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $this->command->warn('Test user (test@example.com) not found. Please register first.');
            return;
        }

        // Get brands and beers
        $guinness = Brand::where('name', 'Guinness')->first();
        $brewdog = Brand::where('name', 'Brewdog')->first();
        $asahi = Brand::where('name', 'Asahi')->first();

        if (!$guinness || !$brewdog || !$asahi) {
            $this->command->warn('Brands not found. Please run BrandSeeder first.');
            return;
        }

        // Get or create beers
        $guinnessDraught = Beer::where('brand_id', $guinness->id)
            ->where('name', 'Draught')
            ->first();

        $brewdogPunkIPA = Beer::firstOrCreate([
            'brand_id' => $brewdog->id,
            'name' => 'Punk IPA'
        ], [
            'style' => 'IPA'
        ]);

        $asahiSuperDry = Beer::where('brand_id', $asahi->id)
            ->where('name', 'Super Dry')
            ->first();

        // Create user beer counts as specified in feature
        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $guinnessDraught->id,
        ], [
            'count' => 5,
            'last_tasted_at' => Carbon::parse('2025-08-10 20:00:00'),
        ]);

        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $brewdogPunkIPA->id,
        ], [
            'count' => 3,
            'last_tasted_at' => Carbon::parse('2025-08-12 21:00:00'),
        ]);

        UserBeerCount::updateOrCreate([
            'user_id' => $user->id,
            'beer_id' => $asahiSuperDry->id,
        ], [
            'count' => 8,
            'last_tasted_at' => Carbon::parse('2025-08-11 18:00:00'),
        ]);

        $this->command->info('User beer counts seeded successfully for test@example.com');
        $this->command->info('Expected order by last_tasted_at desc: Punk IPA, Super Dry, Draught');
    }
}