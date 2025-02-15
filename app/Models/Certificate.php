<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates_attendance';

    protected $fillable = ['user_id', 'event_id', 'certificate_pdf'];
}
