<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoSession extends Model
{
    protected $fillable = [
        'session_code',
        'template_id',
        'status',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function printLog()
    {
        return $this->hasOne(PrintLog::class);
    }
}
