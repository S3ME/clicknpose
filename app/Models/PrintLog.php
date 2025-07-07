<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintLog extends Model
{
    protected $table = 'prints';

    protected $fillable = [
        'session_id',
        'printed_at',
        'printer_name',
        'status',
    ];

    public function session()
    {
        return $this->belongsTo(PhotoSession::class);
    }
}
