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
            'name' => 'User 210402',
            'email' => '210402@example.com',
            'employee_id' => '210402',
            'password' => app('hash')->make('east210402'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'User 230501',
            'email' => '230501@example.com',
            'employee_id' => '230501',
            'password' => app('hash')->make('east230501'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'User 191230',
            'email' => '191230@example.com',
            'employee_id' => '191230',
            'password' => app('hash')->make('east191230'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'User 191105',
            'email' => '191105@example.com',
            'employee_id' => '191105',
            'password' => app('hash')->make('east191105'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
            'name' => 'User 241101',
            'email' => '241101@example.com',
            'employee_id' => '241101',
            'password' => app('hash')->make('east241101'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
