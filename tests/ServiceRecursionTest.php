<?php

namespace Pop\Test;

use Pop\Service\Locator;

class ServiceRecursionTest extends \PHPUnit_Framework_TestCase
{

    public function testRecursionLoop()
    {
        $this->setExpectedException('Pop\Service\Exception');
        $services = new Locator();
        $services->set('service1', function($locator) {
            return $locator->get('service2');
        });
        $services->set('service2', function($locator) {
            return $locator->get('service1');
        });

        $result = $services->get('service1');
    }

}