<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Dosen extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'dosen';

    protected $fillable = [
        'nama',
        'nip',
        'email',
        'wa',
        'hp',
        'foto',
        'password',
    ];

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

    protected $guard_name = 'dosen';

    /**
     * Relationship with seminars (as P1)
     */
    public function seminarsP1()
    {
        return $this->hasMany(Seminar::class, 'p1_dosen_id');
    }

    /**
     * Relationship with seminars (as P2)
     */
    public function seminarsP2()
    {
        return $this->hasMany(Seminar::class, 'p2_dosen_id');
    }

    /**
     * Relationship with seminars (as pembahas)
     */
    public function seminarsPembahas()
    {
        return $this->hasMany(Seminar::class, 'pembahas_dosen_id');
    }

    /**
     * Relationship with nilai
     */
    public function nilai()
    {
        return $this->hasMany(SeminarNilai::class, 'dosen_id');
    }

    /**
     * Relationship with signatures
     */
    public function signatures()
    {
        return $this->hasMany(SeminarSignature::class, 'dosen_id');
    }

    /**
     * Relationship with DosenMahasiswa (for bimbingan)
     */
    public function dosenMahasiswa()
    {
        return $this->hasMany(DosenMahasiswa::class, 'dosen_id');
    }

    /**
     * Relationship to get mahasiswa that this dosen is supervising
     */
    public function mahasiswaBimbingan()
    {
        return $this->belongsToMany(Mahasiswa::class, 'dosen_mahasiswa', 'dosen_id', 'mahasiswa_id');
    }
    public function mahasiswaBimbinganAkademik()
    {
        return $this->hasMany(Mahasiswa::class, 'pembimbing_akademik_id');
    }
}
