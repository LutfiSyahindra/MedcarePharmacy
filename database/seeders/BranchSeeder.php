<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\BranchModel;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BranchModel::create([
            'code' => 'CB001',
            'name' => 'Apotek Pusat',
            'address' => 'Jl. Merdeka No.1',
            'phone' => '08123456789',
            'email' => 'pusat@apotek.com',
        ]);
    }
}
