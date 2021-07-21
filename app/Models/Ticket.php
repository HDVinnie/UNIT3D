<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $dates = [
        'closed_at',
        'reminded_at',
    ];

    public function scopeStatus($query, $status)
    {
        if ($status === 'all') {
            return $query;
        }

        if ($status === 'closed') {
            return $query->whereNotNull('closed_at');
        }

        if ($status === 'open') {
            return $query->whereNull('closed_at');
        }
    }

    public function scopeStale($query)
    {
        return $query->with(['comments' => function ($query) {
            $query->orderBy('id', 'desc');
        }, 'comments.user'])
            ->has('comments')
            ->where('reminded_at', '<', \strtotime('+ 3 days'))
            ->orWhereNull('reminded_at');
    }

    public static function checkForStaleTickets(): void
    {
        $open_tickets = self::status('open')
            ->whereNotNull('staff_id')
            ->get();

        foreach ($open_tickets as $open_ticket) {
            Comment::checkForStale($open_ticket);
        }
    }

    /**
     * Belongs To A User (Created).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => 'System',
            'id'       => '1',
        ]);
    }

    /**
     * Belongs To A Staff User (Assigned).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Belongs To A Ticket Priority.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TicketPriority::class);
    }

    /**
     * Belongs To A Ticket Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }

    /**
     * Has Many Ticket Attachments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * Has Many Comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
