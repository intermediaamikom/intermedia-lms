<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'user_id',
        'file_path',
        'status',
        'points',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function setFilePathAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['file_path'] = $value;
        } else {
            $this->attributes['file_path'] = $value->store('uploads', 'public');
        }
    }
}
