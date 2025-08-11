<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => '6d70fc2b-5bd4-4706-94ca-e7e61d807c44',
                'name' => 'Nakes Testing',
                'email' => 'nakestesting@gmail.com',
                'username' => 'nakestesting',
                'phone_number' => '0822222222',
                'email_verified_at' => null,
                'role' => 'medical_personal',
                'password' => '$2y$12$iF8EY8mpR1OsIwiAMXaWi.4w5qBLNwFKb8Mixk9i.yWTjzOgkOIV2',
                'longitude' => null,
                'latitude' => null,
                'address' => null,
                'kelurahan' => null,
                'rw' => null,
                'disease_type' => 'DM',
                'remember_token' => null,
                'created_at' => '2025-07-08 12:34:36',
                'updated_at' => '2025-07-08 12:34:36',
            ],
            [
                'id' => '86255787-dcc9-47bc-814b-e1ed1aa70af1',
                'name' => 'Pasien Testing',
                'email' => 'pasientesting@gmail.com',
                'username' => 'pasientesting',
                'phone_number' => '08333333333',
                'email_verified_at' => null,
                'role' => 'user',
                'password' => '$2y$12$N/HDf1yUiFCWTxtkf6VYaeWLo7BzlN54wEQPtTpG6JKx38kVt9Nem',
                'longitude' => '110.3822848',
                'latitude' => '-7.8184448',
                'address' => 'Jl. Kenanga Barat No. 5 RT 02 RW 03, Kel. Pedalangan, Kec. Banyumanik',
                'kelurahan' => 'pedalangan',
                'rw' => 'RW 7',
                'disease_type' => 'DM',
                'remember_token' => null,
                'created_at' => '2025-07-08 12:36:07',
                'updated_at' => '2025-07-08 12:37:22',
            ],
            [
                'id' => 'a810a75c-bb0d-4b83-afc3-4435cab5eb76',
                'name' => 'Admin Testing',
                'email' => 'admintesting@gmail.com',
                'username' => 'admintesting',
                'phone_number' => '0811111111',
                'email_verified_at' => null,
                'role' => 'admin',
                'password' => '$2y$12$S/XDfkq1LdY71dg1pp9PIuv0neVRWhIl1lMGzKiNG801KwYlVktpa',
                'longitude' => null,
                'latitude' => null,
                'address' => null,
                'kelurahan' => null,
                'rw' => null,
                'disease_type' => 'DM',
                'remember_token' => null,
                'created_at' => '2025-07-08 12:33:48',
                'updated_at' => '2025-07-08 12:33:48',
            ],
        ]);
    }
}
