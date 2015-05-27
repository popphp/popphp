<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;

class TestController extends AbstractController
{

    public $foo = null;
    public $id = null;

    public function __construct($foo = null)
    {
        $this->foo = $foo;
    }

    public function edit($id)
    {
        $this->id = $id;
    }

    public function help()
    {
        return 'help';
    }

}