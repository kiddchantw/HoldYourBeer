<?php

namespace Tests\Feature\Api\V1;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feedback Controller API Tests
 *
 * Tests the user feedback mechanism including:
 * - Anonymous feedback submission
 * - Authenticated user feedback submission
 * - Bug report system
 * - Feature request collection
 * - Admin feedback management
 */
class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_submit_anonymous_feedback()
    {
        $data = [
            'type' => 'feedback',
            'title' => 'Great app!',
            'description' => 'I really love using this application. Keep up the good work!',
            'priority' => 'low',
            'email' => 'anonymous@example.com',
            'name' => 'Anonymous User',
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'type_label',
                    'title',
                    'description',
                    'priority',
                    'priority_label',
                    'status',
                    'status_label',
                    'display_name',
                    'created_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'type' => 'feedback',
                    'title' => 'Great app!',
                    'status' => 'new',
                ],
                'message' => '感謝您的回饋！我們會盡快處理。',
            ]);

        $this->assertDatabaseHas('feedback', [
            'type' => 'feedback',
            'title' => 'Great app!',
            'email' => 'anonymous@example.com',
            'name' => 'Anonymous User',
            'user_id' => null,
        ]);
    }

    #[Test]
    public function it_can_submit_bug_report_from_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'type' => 'bug_report',
            'title' => 'Login button not working',
            'description' => 'When I tap the login button on my iPhone, nothing happens.',
            'priority' => 'high',
            'url' => 'https://example.com/login',
            'browser' => 'Safari 17.1',
            'device' => 'iPhone 15 Pro',
            'os' => 'iOS 17.1',
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'bug_report',
                    'title' => 'Login button not working',
                    'priority' => 'high',
                    'status' => 'new',
                ],
            ]);

        $this->assertDatabaseHas('feedback', [
            'type' => 'bug_report',
            'title' => 'Login button not working',
            'user_id' => $user->id,
            'priority' => 'high',
        ]);
    }

    #[Test]
    public function it_can_submit_feature_request()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'type' => 'feature_request',
            'title' => 'Add dark mode',
            'description' => 'It would be great to have a dark mode option for better viewing at night.',
            'priority' => 'medium',
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'feature_request',
                    'type_label' => '功能建議',
                ],
            ]);

        $this->assertDatabaseHas('feedback', [
            'type' => 'feature_request',
            'title' => 'Add dark mode',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_requires_email_for_anonymous_feedback()
    {
        $data = [
            'type' => 'feedback',
            'title' => 'Test feedback',
            'description' => 'This is a test feedback without email.',
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_validates_feedback_fields()
    {
        $data = [
            'type' => 'invalid_type',
            'title' => '', // Empty title
            'description' => 'Short', // Too short
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'title', 'description']);
    }

    #[Test]
    public function authenticated_user_can_list_their_feedback()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create feedback for the user
        Feedback::factory()->count(3)->create(['user_id' => $user->id]);

        // Create feedback for another user (should not appear)
        Feedback::factory()->count(2)->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/feedback');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_can_filter_feedback_by_type()
    {
        $user = User::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'type' => 'bug_report',
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'type' => 'feature_request',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/feedback?type=bug_report');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['type' => 'bug_report'],
                ],
            ]);
    }

    #[Test]
    public function it_can_filter_feedback_by_status()
    {
        $user = User::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'status' => 'new',
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'status' => 'resolved',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/feedback?status=new');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['status' => 'new'],
                ],
            ]);
    }

    #[Test]
    public function user_can_view_their_own_feedback_details()
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/feedback/{$feedback->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                ],
            ]);
    }

    #[Test]
    public function user_cannot_view_other_users_feedback()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/feedback/{$feedback->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => '無權訪問此回饋。',
            ]);
    }

    #[Test]
    public function admin_can_view_all_feedback()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        Feedback::factory()->count(5)->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/feedback');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function non_admin_cannot_view_all_feedback()
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/feedback');

        $response->assertStatus(403)
            ->assertJson([
                'message' => '只有管理員可以查看所有回饋。',
            ]);
    }

    #[Test]
    public function admin_can_update_feedback_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::factory()->create(['status' => 'new']);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/feedback/{$feedback->id}", [
            'status' => 'in_progress',
            'admin_notes' => 'We are working on this issue.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'in_progress',
                ],
                'message' => '回饋已更新。',
            ]);

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'status' => 'in_progress',
            'admin_notes' => 'We are working on this issue.',
        ]);
    }

    #[Test]
    public function marking_as_resolved_sets_resolved_at_timestamp()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::factory()->create(['status' => 'in_progress']);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/feedback/{$feedback->id}", [
            'status' => 'resolved',
            'admin_notes' => 'Issue has been fixed in version 2.0.',
        ]);

        $response->assertStatus(200);

        $feedback->refresh();
        $this->assertNotNull($feedback->resolved_at);
        $this->assertEquals('resolved', $feedback->status);
    }

    #[Test]
    public function non_admin_cannot_update_feedback_status()
    {
        $user = User::factory()->create(['role' => 'user']);
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/feedback/{$feedback->id}", [
            'status' => 'resolved',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => '只有管理員可以更新回饋狀態。',
            ]);
    }

    #[Test]
    public function admin_can_delete_feedback()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/v1/feedback/{$feedback->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => '回饋已刪除。',
            ]);

        $this->assertDatabaseMissing('feedback', [
            'id' => $feedback->id,
        ]);
    }

    #[Test]
    public function non_admin_cannot_delete_feedback()
    {
        $user = User::factory()->create(['role' => 'user']);
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/feedback/{$feedback->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => '只有管理員可以刪除回饋。',
            ]);
    }

    #[Test]
    public function it_captures_ip_address_automatically()
    {
        $data = [
            'type' => 'feedback',
            'title' => 'Test feedback',
            'description' => 'This is a test to check IP capture.',
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/v1/feedback', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('feedback', [
            'title' => 'Test feedback',
        ]);

        $feedback = Feedback::where('title', 'Test feedback')->first();
        $this->assertNotNull($feedback->ip_address);
    }

    #[Test]
    public function it_supports_pagination()
    {
        $user = User::factory()->create();

        Feedback::factory()->count(25)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/feedback?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    #[Test]
    public function admin_can_filter_unresolved_feedback()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Feedback::factory()->count(3)->create(['status' => 'new']);
        Feedback::factory()->count(2)->create(['status' => 'resolved']);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/feedback?unresolved=1');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
