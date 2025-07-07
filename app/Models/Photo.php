<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = [
        'session_id',
        'sequence',
        'file_path',
        'retaken',
    ];

    public function session()
    {
        return $this->belongsTo(PhotoSession::class);
    }
}
