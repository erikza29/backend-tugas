<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class profil extends Model
{
    use HasFactory;

    protected $fillable = [
        'pekerja_id',
        'nama',
        'deskripsi',
        'gambar_url',
        'rating'
    ];

    public function pekerja()
    {
        return $this->belongsTo(User::class, 'pekerja_id');
    }
    
}
