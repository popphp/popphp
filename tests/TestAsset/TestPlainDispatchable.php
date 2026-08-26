<?php

namespace Pop\Test\TestAsset;

use Pop\Dispatch\AbstractDispatcher;

class TestPlainDispatchable extends AbstractDispatcher
{

    public function index()
    {
        echo 'index';
    }

}
