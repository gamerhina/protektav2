<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarComment extends Model
{
    use HasFactory;

    protected $fillable = ['seminar_id', 'user_id', 'user_type', 'message', 'is_internal'];

    public function user()
    {
        return $this->morphTo();
    }

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
