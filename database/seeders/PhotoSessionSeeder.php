<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\PhotoSession;
use Illuminate\Support\Str;

class PhotoSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = Template::first();

        PhotoSession::create([
            'session_code'  => Str::random(10),
            'template_id'   => $template->id,
            'status'        => 'in_progress',
        ]);
    }
}
