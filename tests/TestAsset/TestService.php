<?php

namespace Pop\Test\TestAsset;

class TestService {

    public $foo = null;

    public function __construct($var = null)
    {
        $this->foo = $var;
    }

    public function bar($var = null)
    {
        return (null !== $var) ? $var : 456;
    }

    public static function baz()
    {
        return 789;
    }

    public static function foo($var)
    {
        return $var;
    }

}