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
        'tanggal_lulus_manual',
        'tanggal_mulai_kuliah_manual',
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
            'tanggal_lulus_manual' => 'date',
            'tanggal_mulai_kuliah_manual' => 'date',
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

    /**
     * Helper to get start date of study (1 August of the NPM's entry year)
     */
    public function getTanggalMulaiKuliah()
    {
        if ($this->tanggal_mulai_kuliah_manual) {
            return \Carbon\Carbon::parse($this->tanggal_mulai_kuliah_manual)->startOfDay();
        }

        if (strlen($this->npm) >= 2) {
            $year2Digit = substr($this->npm, 0, 2);
            if (is_numeric($year2Digit)) {
                $year = 2000 + intval($year2Digit);
                return \Carbon\Carbon::createFromDate($year, 8, 1)->startOfDay();
            }
        }
        return null;
    }

    /**
     * Helper to get the graduation/comprehensive exam date (actual or manual fallback)
     */
    public function getTanggalLulus()
    {
        if ($this->tanggal_lulus_manual) {
            return \Carbon\Carbon::parse($this->tanggal_lulus_manual)->startOfDay();
        }

        // Default: Date of completed Ujian Skripsi (US)
        $ujSkripsi = $this->seminars()
            ->whereHas('seminarJenis', function ($query) {
                $query->where('kode', 'US');
            })
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($ujSkripsi && $ujSkripsi->tanggal) {
            return \Carbon\Carbon::parse($ujSkripsi->tanggal)->startOfDay();
        }

        // Fallback: Most recent completed seminar
        $lastCompleted = $this->seminars()
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($lastCompleted && $lastCompleted->tanggal) {
            return \Carbon\Carbon::parse($lastCompleted->tanggal)->startOfDay();
        }

        return null;
    }

    /**
     * Helper to determine if student graduated on time (<= 4 years / 48 months)
     */
    public function isLulusTepatWaktu()
    {
        $startDate = $this->getTanggalMulaiKuliah();
        $endDate = $this->getTanggalLulus();

        if ($startDate && $endDate) {
            return $endDate->lessThanOrEqualTo($startDate->copy()->addYears(4));
        }

        return null;
    }
}
