<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiscussionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('discussions')->insert([
            [
                'id' => '23971c12-4087-41f1-8651-1779fc40b1ce',
                'title' => 'Hipertensi',
                'created_at' => Carbon::parse('2025-07-07 20:48:08'),
                'updated_at' => Carbon::parse('2025-07-07 20:48:08'),
            ],
            [
                'id' => '3fb2a508-2bff-4812-b9ed-c4a8b6a0fc56',
                'title' => 'Kesehatan Mental',
                'created_at' => Carbon::parse('2025-07-07 20:48:21'),
                'updated_at' => Carbon::parse('2025-07-07 20:48:21'),
            ],
            [
                'id' => 'bd1caa78-302e-4854-818a-c27299f2d806',
                'title' => 'Diabetes Melitus',
                'created_at' => Carbon::parse('2025-07-07 20:48:16'),
                'updated_at' => Carbon::parse('2025-07-07 20:48:16'),
            ],
        ]);
    }
}
