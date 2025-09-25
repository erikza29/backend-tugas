<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loker extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul', 'deskripsi', 'lokasi', 'gaji', 'deadline', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusPekerjaans()
    {
        return $this->hasMany(\App\Models\status_pekerjaan::class);
    }
}
