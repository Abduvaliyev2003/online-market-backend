<?php

namespace Database\Seeders;

use App\Models\Value;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Value::create([
            'attribut_id' => 1,
            'name' => 'Qizil'
        ]);
        Value::create([
            'attribut_id' => 1,
            'name' => 'Oq'
        ]);
        Value::create([
            'attribut_id' => 1,
            'name' => 'Qora'
        ]);
        Value::create([
            'attribut_id' => 2,
            'name' => '3 sm'
        ]);
        Value::create([
            'attribut_id' => 2,
            'name' => 'mdf'
        ]);
        Value::create([
            'attribut_id' => 2,
            'name' => 'mdf'
        ]);
    }
}
