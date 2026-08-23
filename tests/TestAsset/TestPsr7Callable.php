<?php

namespace Pop\Test\TestAsset;

class TestPsr7Callable
{

    public static function respond()
    {
        return new FakeResponse(200);
    }

}
