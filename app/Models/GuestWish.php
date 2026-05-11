<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestWish extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_invitation_id',
        'guest_name',
        'attendance_status',
        'message',
    ];

    public function guestInvitation(): BelongsTo
    {
        return $this->belongsTo(GuestInvitation::class);
    }
}
