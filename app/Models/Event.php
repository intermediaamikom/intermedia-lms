<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
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

    public function division() {
      return $this->belongsTo(Division::class);
    }

    public function attendances() {
      return $this->hasMany(Attendance::class);
    }

    public function users() {
      return $this->belongsToMany(User::class, 'attendances')->withPivot('certificate_link', 'is_competence', 'final_project_link');
    }
}
