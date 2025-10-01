<?php

namespace Pop\Test\TestAsset;

class TestEvent
{

    public function __construct()
    {

    }

    public function bar()
    {
        return 456;
    }

    public static function baz()
    {
        return 789;
    }

    public static function foo()
    {
        return 123;
    }

    public static function test($param)
    {
        return $param;
    }

}
