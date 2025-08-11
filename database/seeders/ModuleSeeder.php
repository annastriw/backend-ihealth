<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modules')->insert([
            [
                'id' => '19a0b8da-6e51-4b00-9996-859a7a7739e1',
                'name' => 'Modul Hipertensi',
                'description' => null,
                'type' => 'HT',
                'created_at' => Carbon::create(2025, 7, 6, 8, 9, 11),
                'updated_at' => Carbon::create(2025, 7, 6, 8, 9, 11),
            ],
            [
                'id' => '81e62cfd-3e46-488f-87e0-85be2d525e73',
                'name' => 'Modul Diabetes Melitus',
                'description' => null,
                'type' => 'DM',
                'created_at' => Carbon::create(2025, 7, 6, 8, 8, 52),
                'updated_at' => Carbon::create(2025, 7, 6, 8, 8, 52),
            ],
            [
                'id' => 'c32e3ae9-8869-45f2-932f-e8657a14b481',
                'name' => 'Modul Kesehatan Mental',
                'description' => null,
                'type' => 'KM',
                'created_at' => Carbon::create(2025, 7, 6, 8, 9, 1),
                'updated_at' => Carbon::create(2025, 7, 6, 8, 9, 1),
            ],
        ]);
    }
}
