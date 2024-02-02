<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Division extends Model
{
    use HasFactory;

    protected $guarded = [
      'id'
    ];

    protected static function boot()
    {
      parent::boot();

      static::creating(function ($model) {
        if (!($model->getKey())) {
          $model->{$model->getKeyName()} = (string) Str::uuid();
        }
      });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
      return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    public function users() {
      return $this->hasMany(User::class);
    }

    public function events() {
      return $this->hasMany(Event::class);
    }
}
