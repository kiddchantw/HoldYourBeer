<?php

namespace Tests\Unit\Models;

use App\Models\Beer;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserBeerCountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $count = UserBeerCount::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $count->user);
        $this->assertEquals($user->id, $count->user->id);
    }

    #[Test]
    public function it_belongs_to_a_beer()
    {
        $beer = Beer::factory()->create();
        $count = UserBeerCount::factory()->create(['beer_id' => $beer->id]);

        $this->assertInstanceOf(Beer::class, $count->beer);
        $this->assertEquals($beer->id, $count->beer->id);
    }
}
