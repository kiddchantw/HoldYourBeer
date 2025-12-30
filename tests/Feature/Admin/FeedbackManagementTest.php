<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_feedback_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::create([
            'user_id' => $admin->id,
            'type' => Feedback::TYPE_BUG_REPORT,
            'description' => 'Test Feedback',
            'status' => Feedback::STATUS_NEW,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.feedback.index', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee('Test Feedback');
        $response->assertSee(__('feedback.admin_list_title'));
    }

    /** @test */
    public function non_admin_cannot_view_feedback_list()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)
            ->get(route('admin.feedback.index', ['locale' => 'en']));

        $response->assertForbidden(); // Or Redirect depending on implementation, middleware usually 403 or redirect
    }

    /** @test */
    public function admin_can_update_feedback_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::create([
            'type' => Feedback::TYPE_FEEDBACK,
            'description' => 'To be resolved',
            'status' => Feedback::STATUS_NEW,
            'priority' => Feedback::PRIORITY_MEDIUM,
        ]);

        $response = $this->actingAs($admin)
            ->put('/en/admin/feedback/' . $feedback->id, [
                'status' => Feedback::STATUS_RESOLVED,
                'admin_notes' => 'Fixed it',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'status' => Feedback::STATUS_RESOLVED,
            'admin_notes' => 'Fixed it',
        ]);
        
        // Assert resolved_at is set
        $this->assertNotNull($feedback->fresh()->resolved_at);
    }

    /** @test */
    public function sidebar_contains_feedback_link()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard', ['locale' => 'en']));
            
        // Assuming dashboard layout is rendered
        // $response->assertSee(__('feedback.admin_list_title')); 
        // Note: Dashboard might redirect, so we check layout view directly if possible 
        // or ensure we land on a page that extends layout.
        // Let's check the users index page which extends admin layout
        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['locale' => 'en']));
            
        $response->assertSee(route('admin.feedback.index', ['locale' => 'en']));
    }
}
