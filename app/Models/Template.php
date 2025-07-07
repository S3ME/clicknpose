<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'layout_json',
    ];

    protected $casts = [
        'layout_json' => 'array',
    ];

    public function photoSessions()
    {
        return $this->hasMany(PhotoSession::class);
    }
}
