<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function feedback_form_is_displayed_on_profile_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('profile.edit', ['locale' => 'en']));

        $response->assertStatus(200);
        // Check for presence of feedback form related texts
        $response->assertSee(__('feedback.submit_title'));
    }

    /** @test */
    public function unauthenticated_user_cannot_access_profile_page()
    {
        $response = $this->get(route('profile.edit', ['locale' => 'en']));

        $response->assertRedirect(route('localized.login', ['locale' => 'en']));
    }

    /** @test */
    public function user_can_submit_feedback()
    {
        $user = User::factory()->create();

        $data = [
            'type' => Feedback::TYPE_BUG_REPORT,
            'description' => 'This is a test bug report.',
        ];

        $response = $this->actingAs($user)
            ->post(route('feedback.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('status', __('feedback.messages.submit_success'));

        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'type' => Feedback::TYPE_BUG_REPORT,
            'description' => 'This is a test bug report.',
            'status' => Feedback::STATUS_NEW,
            'priority' => Feedback::PRIORITY_MEDIUM, // Validation of default priority
        ]);
    }

    /** @test */
    public function feedback_submission_requires_validation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('feedback.store'), [
                'type' => '',
                'description' => '',
            ]);

        $response->assertSessionHasErrors(['type', 'description']);
    }

    /** @test */
    public function submission_auto_captures_technical_info()
    {
        $user = User::factory()->create();

        $data = [
            'type' => Feedback::TYPE_FEATURE_REQUEST,
            'description' => 'Please add dark mode.',
        ];

        // Simulate request with specific agent and IP
        $response = $this->actingAs($user)
            ->withServerVariables([
                'REMOTE_ADDR' => '123.123.123.123',
                'HTTP_USER_AGENT' => 'TestBrowser/1.0',
            ])
            ->post(route('feedback.store'), $data);

        $this->assertDatabaseHas('feedback', [
            'ip_address' => '123.123.123.123',
            'browser' => 'TestBrowser/1.0', // Note: Simple request might not parse browser fully if using agent parser, but let's check explicit or raw
        ]);
    }
}
