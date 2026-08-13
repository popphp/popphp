<?php

namespace Pop\Test\TestAsset;

use Pop\Controller\AbstractController;

class TestBypassMaintenanceController extends AbstractController
{

    public function __construct()
    {
        $this->setBypassMaintenance(true);
    }

    public function help()
    {
        echo 'help';
    }

    public function maintenance()
    {
        echo 'maintenance';
    }

}
