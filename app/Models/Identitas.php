<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Identitas extends Model
{
    /** @use HasFactory<\Database\Factories\IdentitasFactory> */
    use HasFactory;

    protected $table = 'identitas';

    protected $fillable = [
        'penugasan_id',
        'jml_mahasiswa',
        'jml_dosen',
        'jml_tendik',
        'jml_prodi',
        'jml_ukm',
        'jml_fakultas',
        'jml_ormawa',
        'jml_agama',
        'visi',
        'misi',
        'legal_documents',
        'is_verified',
        'admin_note',
    ];

    protected $casts = [
        'legal_documents' => 'array',
        'is_verified' => 'boolean',
    ];

    public function penugasan()
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    public function agamas()
    {
        return $this->hasMany(Agama::class, 'identitas_id');
    }
}
