<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            "storeName"=>"KOMBATE Damelan",
            "storePhone"=>"92691421",
            "storeAddress"=>"Lome, tokoin",
            "adminPhone"=>"12345678",
            "adminPassword"=>Hash::make("password"),
        ]);
    }
}
