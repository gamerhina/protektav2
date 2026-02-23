<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SuratApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_id',
        'role_id',
        'role_nama',
        'dosen_id',
        'urutan',
        'status',
        'signature_type',
        'signature_path',
        'qr_code_url',
        'catatan',
        'approved_at',
        'rejected_at',
        'stamp_x',
        'stamp_y',
        'stamp_width',
        'stamp_height',
        'stamp_page',
        'is_stamped',
        'additional_stamps',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'stamp_x' => 'integer',
        'stamp_y' => 'integer',
        'stamp_width' => 'integer',
        'stamp_height' => 'integer',
        'stamp_page' => 'integer',
        'is_stamped' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'additional_stamps' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Signature type constants
    const SIGNATURE_CANVAS = 'canvas';
    const SIGNATURE_UPLOAD = 'upload';
    const SIGNATURE_QR = 'qr';

    /**
     * Get the surat for this approval
     */
    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    /**
     * Get the role for this approval
     */
    public function role()
    {
        return $this->belongsTo(SuratRole::class, 'role_id');
    }

    /**
     * Get the approver (dosen)
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Check if approval is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approval is approved
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if approval is rejected
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if approval is ready (all previous sequence steps are approved)
     */
    public function isReady()
    {
        // Check if there are any approvals for this surat with a lower urutan that are NOT approved
        return !self::where('surat_id', $this->surat_id)
            ->where('urutan', '<', $this->urutan)
            ->where('status', '!=', self::STATUS_APPROVED)
            ->exists();
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for ordering by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    /**
     * Scope for specific dosen
     */
    public function scopeForDosen($query, $dosenId)
    {
        return $query->where('dosen_id', $dosenId);
    }

    /**
     * Get signature URL
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path) {
            return \Storage::url($this->signature_path);
        }
        return null;
    }
}
