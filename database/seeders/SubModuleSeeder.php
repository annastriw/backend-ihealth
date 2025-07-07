<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sub_modules')->insert([
            [
                'id' => '39683694-c50b-4823-9cc9-91090af12e70',
                'module_id' => 'c32e3ae9-8869-45f2-932f-e8657a14b481', // Kesehatan Mental
                'name' => 'Materi Kesehatan Mental',
                'description' => null,
                'created_at' => Carbon::create(2025, 7, 7, 18, 4, 54),
                'updated_at' => Carbon::create(2025, 7, 7, 18, 32, 51),
            ],
            [
                'id' => 'c2fd4be2-dee2-48d6-8b78-a0b302f2e692',
                'module_id' => '19a0b8da-6e51-4b00-9996-859a7a7739e1', // Hipertensi
                'name' => 'Materi Hipertensi',
                'description' => null,
                'created_at' => Carbon::create(2025, 7, 7, 18, 4, 28),
                'updated_at' => Carbon::create(2025, 7, 7, 18, 4, 28),
            ],
            [
                'id' => 'c8bb5c60-c1f1-4597-b01a-84a04c3c83fe',
                'module_id' => '81e62cfd-3e46-488f-87e0-85be2d525e73', // Diabetes Melitus
                'name' => 'Materi Diabetes Melitus',
                'description' => null,
                'created_at' => Carbon::create(2025, 7, 7, 18, 4, 45),
                'updated_at' => Carbon::create(2025, 7, 7, 18, 4, 45),
            ],
        ]);
    }
}
