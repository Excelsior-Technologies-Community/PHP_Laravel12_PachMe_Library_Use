<?php

namespace Demo\MyPackage\Tests\TestCase;

use Demo\MyPackage\__PACKAGE_UC__ServiceProvider;
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
