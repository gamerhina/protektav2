<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratComment extends Model
{
    protected $fillable = ['surat_id', 'user_id', 'user_type', 'message'];

    public function user()
    {
        return $this->morphTo();
    }
}
