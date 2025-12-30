<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'feedback';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'priority',
        'status',
        'admin_notes',
        'resolved_at',
        'browser',
        'device',
        'os',
        'ip_address',
        'metadata',
        'source',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'type' => 'feedback',
        'status' => 'new',
        'priority' => 'medium',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Feedback types
     */
    public const TYPE_FEEDBACK = 'feedback';
    public const TYPE_BUG_REPORT = 'bug_report';
    public const TYPE_FEATURE_REQUEST = 'feature_request';

    /**
     * Feedback statuses
     */
    public const STATUS_NEW = 'new';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Priority levels
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    /**
     * Get all available types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_FEEDBACK,
            self::TYPE_BUG_REPORT,
            self::TYPE_FEATURE_REQUEST,
        ];
    }

    /**
     * Get all types with labels
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_FEEDBACK => __('General Feedback'),
            self::TYPE_BUG_REPORT => __('Bug Report'),
            self::TYPE_FEATURE_REQUEST => __('Feature Request'),
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_IN_REVIEW,
            self::STATUS_IN_PROGRESS,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * Get all statuses with labels
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_NEW => __('New'),
            self::STATUS_IN_REVIEW => __('In Review'),
            self::STATUS_IN_PROGRESS => __('In Progress'),
            self::STATUS_RESOLVED => __('Resolved'),
            self::STATUS_CLOSED => __('Closed'),
            self::STATUS_REJECTED => __('Rejected'),
        ];
    }

    /**
     * Get all available priorities
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_CRITICAL,
        ];
    }

    /**
     * Get all priorities with labels
     */
    public static function getPriorityLabels(): array
    {
        return [
            self::PRIORITY_LOW => __('Low'),
            self::PRIORITY_MEDIUM => __('Medium'),
            self::PRIORITY_HIGH => __('High'),
            self::PRIORITY_CRITICAL => __('Critical'),
        ];
    }

    /**
     * Get the user that submitted the feedback
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include feedback of a given type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include feedback with a given status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include feedback with a given priority
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include unresolved feedback
     */
    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_NEW,
            self::STATUS_IN_REVIEW,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Scope a query to only include resolved feedback
     */
    public function scopeResolved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ]);
    }

    /**
     * Scope a query to order by priority
     */
    public function scopeOrderByPriority($query, string $direction = 'desc')
    {
        $priorities = [
            self::PRIORITY_CRITICAL => 4,
            self::PRIORITY_HIGH => 3,
            self::PRIORITY_MEDIUM => 2,
            self::PRIORITY_LOW => 1,
        ];

        return $query->orderByRaw(
            "CASE
                WHEN priority = 'critical' THEN 4
                WHEN priority = 'high' THEN 3
                WHEN priority = 'medium' THEN 2
                WHEN priority = 'low' THEN 1
            END " . $direction
        );
    }

    /**
     * Mark feedback as resolved
     */
    public function markAsResolved(?string $adminNotes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    /**
     * Check if feedback is from authenticated user
     */
    public function isFromAuthenticatedUser(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Get display name (user name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? 'Anonymous';
    }

    /**
     * Get display email (user email)
     */
    public function getDisplayEmailAttribute(): string
    {
        return $this->user?->email ?? 'N/A';
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypeLabels()[$this->type] ?? $this->type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityLabels()[$this->priority] ?? $this->priority;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_IN_REVIEW => 'yellow',
            self::STATUS_IN_PROGRESS => 'purple',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_CLOSED => 'gray',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_CRITICAL => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_LOW => 'green',
            default => 'gray',
        };
    }
}
