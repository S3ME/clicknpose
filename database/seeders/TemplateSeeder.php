<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Template::create([
            'name'          => 'Default 4-Strip Template',
            'image_path'    => 'templates/template1.png',
            'layout_json'   => json_encode([
                ["x" => 100, "y" => 70, "width" => 510, "height" => 380],
                ["x" => 100, "y" => 545, "width" => 510, "height" => 380],
                ["x" => 100, "y" => 1020, "width" => 510, "height" => 380],
                ["x" => 100, "y" => 1495, "width" => 510, "height" => 380],
            ])
        ]);
    }
}
