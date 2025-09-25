<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class status_pekerjaan extends Model
{
        use HasFactory;

    protected $fillable = [
        'user_id',
        'loker_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loker()
    {
        return $this->belongsTo(Loker::class);
    }
    
}
