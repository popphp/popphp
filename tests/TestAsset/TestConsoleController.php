<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;
use Pop\Dispatch\ConsoleTrait;

class TestConsoleController extends AbstractController
{

    use ConsoleTrait;

    public function help()
    {
        echo 'help';
    }

}
