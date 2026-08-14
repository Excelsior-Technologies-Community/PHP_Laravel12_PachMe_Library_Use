<?php

namespace :VendorName\:PackageName\Database\Seeders;

use Illuminate\Database\Seeder;
use :VendorName\:PackageName\Models\__PACKAGE_UC__;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(__PACKAGE_UC__Seeder::class);
    }
}
