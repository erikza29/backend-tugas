<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class status_kerja extends Model
{
    use HasFactory;

    protected $table = 'status_kerjas'; // <-- kasih tahu nama tabel

    protected $fillable = [
        'user_id',
        'loker_id',
        'status',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Loker
    public function loker()
    {
        return $this->belongsTo(Loker::class, 'loker_id');
    }
}
