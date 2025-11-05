<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group V1 - Feedback
 *
 * APIs for user feedback, bug reports, and feature requests (Version 1)
 */
class FeedbackController extends Controller
{
    /**
     * Submit new feedback
     *
     * Submit feedback, bug report, or feature request. Anonymous submissions are allowed.
     *
     * @unauthenticated
     *
     * @bodyParam type string required Type of feedback. Must be one of: feedback, bug_report, feature_request. Example: bug_report
     * @bodyParam title string required Title of the feedback. Maximum 255 characters. Example: Login button not working on mobile
     * @bodyParam description string required Detailed description. Minimum 10 characters. Example: When I tap the login button on my iPhone, nothing happens. I've tried multiple times.
     * @bodyParam priority string optional Priority level. Must be one of: low, medium, high, critical. Defaults to medium. Example: high
     * @bodyParam email string optional Email address (required for anonymous users). Example: user@example.com
     * @bodyParam name string optional Name of the submitter. Example: John Doe
     * @bodyParam url string optional URL where the issue occurred. Example: https://example.com/login
     * @bodyParam browser string optional Browser information. Example: Safari 17.1
     * @bodyParam device string optional Device information. Example: iPhone 15 Pro
     * @bodyParam os string optional Operating system. Example: iOS 17.1
     * @bodyParam metadata object optional Additional metadata as key-value pairs.
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "type": "bug_report",
     *     "type_label": "錯誤回報",
     *     "title": "Login button not working on mobile",
     *     "description": "When I tap the login button on my iPhone, nothing happens.",
     *     "priority": "high",
     *     "priority_label": "高",
     *     "status": "new",
     *     "status_label": "新建",
     *     "display_name": "John Doe",
     *     "status_badge_color": "blue",
     *     "priority_badge_color": "orange",
     *     "contact": {
     *       "email": "user@example.com",
     *       "name": "John Doe"
     *     },
     *     "metadata": {
     *       "url": "https://example.com/login",
     *       "browser": "Safari 17.1",
     *       "device": "iPhone 15 Pro",
     *       "os": "iOS 17.1",
     *       "custom": null
     *     },
     *     "created_at": "2025-11-05T10:00:00+00:00",
     *     "updated_at": "2025-11-05T10:00:00+00:00",
     *     "resolved_at": null
     *   },
     *   "message": "感謝您的回饋！我們會盡快處理。"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["請輸入標題"],
     *     "description": ["描述至少需要 10 個字元"]
     *   }
     * }
     */
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Add user_id if authenticated
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        // Create feedback
        $feedback = Feedback::create($validated);

        return response()->json([
            'data' => new FeedbackResource($feedback),
            'message' => '感謝您的回饋！我們會盡快處理。',
        ], 201);
    }

    /**
     * List user's feedback
     *
     * Get a list of feedback submitted by the authenticated user. Supports filtering and pagination.
     *
     * @authenticated
     *
     * @queryParam type string optional Filter by feedback type. Must be one of: feedback, bug_report, feature_request. Example: bug_report
     * @queryParam status string optional Filter by status. Must be one of: new, in_review, in_progress, resolved, closed, rejected. Example: new
     * @queryParam per_page integer optional Number of items per page. Default: 15. Example: 20
     * @queryParam page integer optional Page number. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "bug_report",
     *       "type_label": "錯誤回報",
     *       "title": "Login button not working",
     *       "status": "in_progress",
     *       "status_label": "處理中",
     *       "created_at": "2025-11-05T10:00:00+00:00"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Feedback::query()->where('user_id', $request->user()->id);

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        // Order by priority and creation date
        $query->orderByPriority()->latest();

        // Paginate
        $perPage = min($request->input('per_page', 15), 100);
        $feedback = $query->paginate($perPage);

        return FeedbackResource::collection($feedback);
    }

    /**
     * Get feedback details
     *
     * Retrieve detailed information about a specific feedback item.
     *
     * @urlParam id integer required The feedback ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "type": "bug_report",
     *     "type_label": "錯誤回報",
     *     "title": "Login button not working on mobile",
     *     "description": "Detailed description here...",
     *     "priority": "high",
     *     "priority_label": "高",
     *     "status": "in_progress",
     *     "status_label": "處理中",
     *     "created_at": "2025-11-05T10:00:00+00:00"
     *   }
     * }
     *
     * @response 403 {
     *   "message": "無權訪問此回饋。"
     * }
     *
     * @response 404 {
     *   "message": "找不到此回饋。"
     * }
     */
    public function show(Request $request, Feedback $feedback): JsonResponse
    {
        // Check permission: owner, admin, or anonymous user from same IP
        if (!$this->canViewFeedback($request, $feedback)) {
            return response()->json([
                'message' => '無權訪問此回饋。',
            ], 403);
        }

        return response()->json([
            'data' => new FeedbackResource($feedback),
        ]);
    }

    /**
     * Update feedback status (Admin only)
     *
     * Update feedback status and add admin notes. Only accessible by administrators.
     *
     * @authenticated
     *
     * @urlParam id integer required The feedback ID. Example: 1
     * @bodyParam status string optional New status. Must be one of: new, in_review, in_progress, resolved, closed, rejected. Example: in_progress
     * @bodyParam admin_notes string optional Admin notes. Example: We are working on this issue.
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "in_progress",
     *     "status_label": "處理中",
     *     "admin_notes": "We are working on this issue."
     *   },
     *   "message": "回饋已更新。"
     * }
     *
     * @response 403 {
     *   "message": "只有管理員可以更新回饋狀態。"
     * }
     */
    public function update(Request $request, Feedback $feedback): JsonResponse
    {
        // Only admins can update
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => '只有管理員可以更新回饋狀態。',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:' . implode(',', Feedback::getStatuses())],
            'admin_notes' => ['nullable', 'string'],
        ]);

        // If marking as resolved, set resolved_at timestamp
        if (isset($validated['status']) && $validated['status'] === Feedback::STATUS_RESOLVED) {
            $feedback->markAsResolved($validated['admin_notes'] ?? null);
        } else {
            $feedback->update($validated);
        }

        return response()->json([
            'data' => new FeedbackResource($feedback),
            'message' => '回饋已更新。',
        ]);
    }

    /**
     * Delete feedback (Admin only)
     *
     * Permanently delete a feedback item. Only accessible by administrators.
     *
     * @authenticated
     *
     * @urlParam id integer required The feedback ID. Example: 1
     *
     * @response 200 {
     *   "message": "回饋已刪除。"
     * }
     *
     * @response 403 {
     *   "message": "只有管理員可以刪除回饋。"
     * }
     */
    public function destroy(Request $request, Feedback $feedback): JsonResponse
    {
        // Only admins can delete
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => '只有管理員可以刪除回饋。',
            ], 403);
        }

        $feedback->delete();

        return response()->json([
            'message' => '回饋已刪除。',
        ]);
    }

    /**
     * List all feedback (Admin only)
     *
     * Get a list of all feedback with filtering options. Only accessible by administrators.
     *
     * @authenticated
     *
     * @queryParam type string optional Filter by feedback type. Example: bug_report
     * @queryParam status string optional Filter by status. Example: new
     * @queryParam priority string optional Filter by priority. Example: high
     * @queryParam unresolved boolean optional Show only unresolved items. Example: 1
     * @queryParam per_page integer optional Number of items per page. Default: 15. Example: 20
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "bug_report",
     *       "title": "Login issue",
     *       "status": "new",
     *       "priority": "high",
     *       "created_at": "2025-11-05T10:00:00+00:00"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 42
     *   }
     * }
     *
     * @response 403 {
     *   "message": "只有管理員可以查看所有回饋。"
     * }
     */
    public function adminIndex(Request $request): JsonResponse|AnonymousResourceCollection
    {
        // Only admins can view all feedback
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => '只有管理員可以查看所有回饋。',
            ], 403);
        }

        $query = Feedback::query()->with('user');

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        if ($request->boolean('unresolved')) {
            $query->unresolved();
        }

        // Order by priority and creation date
        $query->orderByPriority()->latest();

        // Paginate
        $perPage = min($request->input('per_page', 15), 100);
        $feedback = $query->paginate($perPage);

        return FeedbackResource::collection($feedback);
    }

    /**
     * Check if user can view this feedback
     */
    private function canViewFeedback(Request $request, Feedback $feedback): bool
    {
        return $this->isAdmin($request) ||
               $request->user()?->id === $feedback->user_id ||
               ($request->ip() === $feedback->ip_address && !$feedback->user_id);
    }

    /**
     * Check if current user is admin
     */
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }
}
