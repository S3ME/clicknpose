<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhotoSession;
use App\Models\Photo;

class PhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = PhotoSession::all();

        foreach ($sessions as $session) {
            for ($i = 1; $i <= 4; $i++) {
                Photo::create([
                    'session_id' => $session->id,
                    'sequence'   => $i,
                    'file_path'  => "photos/dummy-{$i}.jpg",
                    'retaken'    => 0,
                ]);
            }
        }
    }
}
