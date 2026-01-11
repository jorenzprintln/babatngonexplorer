<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
        'place_id',
        'type',
        'reason',
        'description',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['status_color', 'type_label'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'reviewing' => 'blue',
            'resolved' => 'green',
            'dismissed' => 'gray',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'review' => 'Review Report',
            'place' => 'Place Report',
            'user' => 'User Report',
            'other' => 'Other Report',
            default => 'Report',
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', 'reviewing');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function markAsReviewing(): void
    {
        $this->update(['status' => 'reviewing']);
    }

    public function resolve(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $adminId,
            'resolved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function dismiss(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'dismissed',
            'resolved_by' => $adminId,
            'resolved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}