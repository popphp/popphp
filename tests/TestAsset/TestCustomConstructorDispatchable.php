<?php

namespace Pop\Test\TestAsset;

use Pop\Application;
use Pop\Dispatch\AbstractDispatcher;

class TestCustomConstructorDispatchable extends AbstractDispatcher
{

    public ?string $label = null;

    public function __construct(Application $application, string $label = 'default')
    {
        parent::__construct($application);
        $this->label = $label;
    }

    public function index()
    {
        echo 'index';
    }

}
