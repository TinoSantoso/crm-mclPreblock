<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
            'name' => 'Rifqi Alhamidi',
            'email' => '210402@example.com',
            'employee_id' => '210402',
            'password' => app('hash')->make('east210402'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'Muhammad Amin',
            'email' => '230501@example.com',
            'employee_id' => '230501',
            'password' => app('hash')->make('east230501'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'Rangga Putra',
            'email' => '191230@example.com',
            'employee_id' => '191230',
            'password' => app('hash')->make('east191230'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'Dewi Efrina',
            'email' => '191105@example.com',
            'employee_id' => '191105',
            'password' => app('hash')->make('east191105'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'Heru HANDOKO',
            'email' => '241101@example.com',
            'employee_id' => '241101',
            'password' => app('hash')->make('east241101'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
