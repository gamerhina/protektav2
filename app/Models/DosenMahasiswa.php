<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenMahasiswa extends Model
{
    protected $fillable = [
        'dosen_id',
        'mahasiswa_id',
        'jenis_pembimbing'
    ];

    protected $table = 'dosen_mahasiswa';

    /**
     * Relationship with Dosen
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Relationship with Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
