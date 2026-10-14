<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    private const TESTING_DATABASE = 'platform_testing';

    protected function setUpTraits()
    {
        $this->ensureTestingDatabase();

        return parent::setUpTraits();
    }

    private function ensureTestingDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($database !== self::TESTING_DATABASE) {
            throw new RuntimeException("テスト用DB以外に接続しようとしています: {$database}");
        }
    }
}