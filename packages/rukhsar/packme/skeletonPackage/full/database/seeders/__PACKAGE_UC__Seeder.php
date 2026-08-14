<?php

namespace :VendorName\:PackageName\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class __PACKAGE_UC__Seeder extends Seeder
{
    public function run(): void
    {
        DB::table(':package_name__table')->insert([
            [
                'name' => 'Sample :package_name 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sample :package_name 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sample :package_name 3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
