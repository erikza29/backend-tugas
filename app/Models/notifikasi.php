<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notifikasi extends Model
{
use HasFactory;

    protected $fillable = ['yangreting_id', 'target_id', 'loker_id', 'rating'];

    public function pemberi()
    {
        return $this->belongsTo(User::class, 'yangreting_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function loker()
    {
        return $this->belongsTo(Loker::class);
    }
}
