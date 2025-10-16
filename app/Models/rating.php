<?php

namespace App\Models;
use App\Models\User;
use App\Models\Loker;
use Illuminate\Database\Eloquent\Model;

class rating extends Model
{
    protected $fillable = [
        'yangreting_id',
        'target_id',
        'loker_id',
        'rating'
    ];

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
        return $this->belongsTo(Loker::class, 'loker_id');
    }
}
