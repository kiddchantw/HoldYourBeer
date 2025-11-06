<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = Feedback::getTypes();
        $priorities = Feedback::getPriorities();
        $statuses = Feedback::getStatuses();

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement($types),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(3),
            'priority' => fake()->randomElement($priorities),
            'status' => fake()->randomElement($statuses),
            'email' => null,
            'name' => null,
            'admin_notes' => null,
            'resolved_at' => null,
            'url' => fake()->optional()->url(),
            'browser' => fake()->optional()->userAgent(),
            'device' => fake()->optional()->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S23', 'iPad Pro', 'MacBook Pro']),
            'os' => fake()->optional()->randomElement(['iOS 17.1', 'Android 14', 'Windows 11', 'macOS 14']),
            'ip_address' => fake()->ipv4(),
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the feedback is from an anonymous user.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
        ]);
    }

    /**
     * Indicate that the feedback is a bug report.
     */
    public function bugReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Feedback::TYPE_BUG_REPORT,
            'title' => 'Bug: ' . fake()->sentence(),
            'priority' => fake()->randomElement([Feedback::PRIORITY_HIGH, Feedback::PRIORITY_CRITICAL]),
            'url' => fake()->url(),
            'browser' => fake()->userAgent(),
        ]);
    }

    /**
     * Indicate that the feedback is a feature request.
     */
    public function featureRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Feedback::TYPE_FEATURE_REQUEST,
            'title' => 'Feature Request: ' . fake()->sentence(),
            'priority' => fake()->randomElement([Feedback::PRIORITY_LOW, Feedback::PRIORITY_MEDIUM]),
        ]);
    }

    /**
     * Indicate that the feedback is general feedback.
     */
    public function generalFeedback(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Feedback::TYPE_FEEDBACK,
            'title' => fake()->sentence(),
            'priority' => Feedback::PRIORITY_MEDIUM,
        ]);
    }

    /**
     * Indicate that the feedback has a specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'resolved_at' => $status === Feedback::STATUS_RESOLVED ? now() : null,
        ]);
    }

    /**
     * Indicate that the feedback is new.
     */
    public function asNew(): static
    {
        return $this->withStatus(Feedback::STATUS_NEW);
    }

    /**
     * Indicate that the feedback is in review.
     */
    public function inReview(): static
    {
        return $this->withStatus(Feedback::STATUS_IN_REVIEW);
    }

    /**
     * Indicate that the feedback is in progress.
     */
    public function inProgress(): static
    {
        return $this->withStatus(Feedback::STATUS_IN_PROGRESS);
    }

    /**
     * Indicate that the feedback is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Feedback::STATUS_RESOLVED,
            'resolved_at' => now(),
            'admin_notes' => 'This issue has been resolved.',
        ]);
    }

    /**
     * Indicate that the feedback has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Feedback::PRIORITY_HIGH,
        ]);
    }

    /**
     * Indicate that the feedback has critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Feedback::PRIORITY_CRITICAL,
        ]);
    }
}
