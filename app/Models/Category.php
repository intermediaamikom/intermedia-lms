<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'kategori'];

    public function user()
    {
        return $this->belongsToMany(User::class, 'table_categories')->withPivot('event_id');
    }
}
