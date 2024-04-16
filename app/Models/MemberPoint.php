<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPoint extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function (MemberPoint $memberPoint) {
            $totalPoint = self::where('user_id', $memberPoint->user_id)->sum('point');
            User::where('id', $memberPoint->user_id)->update(['total_point' => $totalPoint]);
        });

        static::deleted(function (MemberPoint $memberPoint) {
            $totalPoint = self::where('user_id', $memberPoint->user_id)->sum('point');
            User::where('id', $memberPoint->user_id)->update(['total_point' => $totalPoint]);
        });

        static::updated(function (MemberPoint $memberPoint) {
            $totalPoint = self::where('user_id', $memberPoint->user_id)->sum('point');
            User::where('id', $memberPoint->user_id)->update(['total_point' => $totalPoint]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
