<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;

class TestController extends AbstractController
{

    public function help()
    {
        echo 'help';
    }

}