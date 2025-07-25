<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PhotoSession extends Model
{
    protected $fillable = [
        'session_code',
        'template_id',
        'status',
        'final_image_path',
        'download_slug',
    ];

    // Relasi ke Template
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    // Relasi ke Foto-foto dalam sesi
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    // Relasi ke log cetak
    public function printLog()
    {
        return $this->hasOne(PrintLog::class);
    }

    // Optional: Auto-generate slug saat membuat model
    protected static function booted()
    {
        static::creating(function ($session) {
            $session->download_slug = Str::random(10);
        });
    }
}
