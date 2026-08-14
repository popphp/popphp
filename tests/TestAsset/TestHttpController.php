<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;
use Pop\Dispatch\HttpTrait;

class TestHttpController extends AbstractController
{

    use HttpTrait;

    public function help()
    {
        echo 'help';
    }

}
