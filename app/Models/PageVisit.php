<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_invitation_id',
        'source',
        'ip_address',
        'user_agent',
    ];

    public function guestInvitation(): BelongsTo
    {
        return $this->belongsTo(GuestInvitation::class);
    }
}
