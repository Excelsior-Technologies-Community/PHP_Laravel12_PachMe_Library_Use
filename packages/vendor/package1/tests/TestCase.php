<?php

namespace Vendor\Package1\Tests\TestCase;

use Vendor\Package1\__PACKAGE_UC__ServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            __PACKAGE_UC__ServiceProvider::class,
        ];
    }
}
