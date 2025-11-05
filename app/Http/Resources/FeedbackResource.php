<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'status' => $this->status,
            'status_label' => $this->status_label,

            // Display information
            'display_name' => $this->display_name,
            'status_badge_color' => $this->status_badge_color,
            'priority_badge_color' => $this->priority_badge_color,

            // User relationship (only show if user is authenticated or is admin)
            'user' => $this->when(
                $this->user_id && ($request->user()?->id === $this->user_id || $this->isAdmin($request)),
                fn() => [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ]
            ),

            // Anonymous user contact info (only show to owner or admin)
            'contact' => $this->when(
                !$this->user_id && ($this->canView($request)),
                fn() => [
                    'email' => $this->email,
                    'name' => $this->name,
                ]
            ),

            // Admin information (only show to admins)
            'admin_notes' => $this->when(
                $this->isAdmin($request),
                $this->admin_notes
            ),

            // Technical metadata (only show to owner or admin)
            'metadata' => $this->when(
                $this->canView($request),
                fn() => [
                    'url' => $this->url,
                    'browser' => $this->browser,
                    'device' => $this->device,
                    'os' => $this->os,
                    'ip_address' => $this->when($this->isAdmin($request), $this->ip_address),
                    'custom' => $this->metadata,
                ]
            ),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }

    /**
     * Check if the current user can view this feedback
     */
    private function canView(Request $request): bool
    {
        return $this->isAdmin($request) ||
               $request->user()?->id === $this->user_id ||
               ($request->ip() === $this->ip_address && !$this->user_id);
    }

    /**
     * Check if the current user is admin
     */
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }
}
