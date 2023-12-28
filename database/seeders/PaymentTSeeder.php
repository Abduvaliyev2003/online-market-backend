<?php

namespace Database\Seeders;

use App\Models\PaymentT;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentT::create([
            'title' => "Payme"
        ]);
        PaymentT::create([
            'title' => "Click"
        ]);
        PaymentT::create([
            'title' => "Uzum"
        ]);
        PaymentT::create([
            'title' => "Наличные"
        ]);
    }
}
