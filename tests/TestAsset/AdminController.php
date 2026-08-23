<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;

class AdminController extends AbstractController
{

    public $id = null;

    public function users($action = null, $id = null)
    {
        $this->id = $id;
    }

    public function index()
    {
        $this->id = 0;
    }

}
