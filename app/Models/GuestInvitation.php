<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_name',
        'share_code',
        'share_message',
        'share_link',
        'shared_by',
    ];

    public function getRouteKeyName(): string
    {
        return 'share_code';
    }

    public function visits(): HasMany
    {
        return $this->hasMany(PageVisit::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(GuestWish::class);
    }
}
