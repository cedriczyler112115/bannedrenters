<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        if (
            $app['config']->get('database.default') === 'mysql'
            && $app['config']->get('database.connections.mysql.database') === 'bannedrenters'
        ) {
            throw new RuntimeException(
                'Tests cannot run against the live bannedrenters database. Use bannedrenters_testing.'
            );
        }

        return $app;
    }
}
