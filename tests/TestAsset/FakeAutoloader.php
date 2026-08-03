<?php

namespace Pop\Test\TestAsset;

/**
 * Deliberately named to look like an autoloader by the old class-name-substring
 * heuristic (stripos on 'classload'/'autoload') without being a real
 * Composer\Autoload\ClassLoader - used to prove Application's constructor now
 * detects the autoloader by type, not by name.
 */
class FakeAutoloader
{

    public function add()
    {
    }

    public function addPsr4()
    {
    }

}
