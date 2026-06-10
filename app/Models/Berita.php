<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Berita extends Model
{
    protected $table = 'beritas';

    protected $fillable = [
        'judul',
        'konten',
        'gambar',
        'tanggal',
        'is_published',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
