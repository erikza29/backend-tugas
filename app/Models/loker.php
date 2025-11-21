<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loker extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'lokasi',
        'gaji',
        'deadline_value',
        'deadline_unit',
        'deadline_end',
        'status',
        'user_id',
        'gambar',
        'gambar_url'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusPekerjaans()
    {
        return $this->hasMany(status_pekerjaan::class);
    }



}
