<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;

class TestController2 extends AbstractController
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

    public function delete()
    {
        $this->id = 0;
    }

    public function help()
    {
        echo 'help';
    }

    public function error()
    {
        $this->id = 0;
    }

    public function getId()
    {
        return $this->id;
    }

}
