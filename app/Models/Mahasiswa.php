<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Mahasiswa extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'mahasiswa';

    protected $fillable = [
        'nama',
        'npm',
        'email',
        'wa',
        'hp',
        'foto',
        'pembimbing_akademik_id',
        'password',
    ];

    /**
     * Relationship with Academic Advisor (PA)
     */
    public function pembimbingAkademik()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_akademik_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $guard_name = 'mahasiswa';

    /**
     * Relationship with seminars
     */
    public function seminars()
    {
        return $this->hasMany(Seminar::class, 'mahasiswa_id');
    }

    /**
     * Relationship with DosenMahasiswa (for bimbingan)
     */
    public function dosenMahasiswa()
    {
        return $this->hasMany(DosenMahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relationship to get dosen pembimbing for this mahasiswa
     */
    public function dosenPembimbing()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_mahasiswa', 'mahasiswa_id', 'dosen_id');
    }
}
