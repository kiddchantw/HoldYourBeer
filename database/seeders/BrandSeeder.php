<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Beer;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create brands as mentioned in the feature spec
        $guinness = Brand::create(['name' => 'Guinness']);
        $brewdog = Brand::create(['name' => 'Brewdog']);
        $asahi = Brand::create(['name' => 'Asahi']);

        // Create some beers for Brewdog (as mentioned in feature spec)
        Beer::create([
            'brand_id' => $brewdog->id,
            'name' => 'Hazy Jane',
            'style' => 'IPA'
        ]);

        Beer::create([
            'brand_id' => $brewdog->id,
            'name' => 'Punk IPA',
            'style' => 'IPA'
        ]);

        // Create some beers for Guinness
        Beer::create([
            'brand_id' => $guinness->id,
            'name' => 'Draught',
            'style' => 'Stout'
        ]);

        Beer::create([
            'brand_id' => $guinness->id,
            'name' => 'Extra Stout',
            'style' => 'Stout'
        ]);

        // Create some beers for Asahi
        Beer::create([
            'brand_id' => $asahi->id,
            'name' => 'Super Dry',
            'style' => 'Lager'
        ]);
    }
}