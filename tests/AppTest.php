<?php

namespace Pop\Test;

use Pop\App;
use PHPUnit\Framework\TestCase;
use Pop\Application;
use Pop\Service\Locator;

class AppTest extends TestCase
{

    public function testConfig()
    {
        $this->assertNull(App::config());
    }

    public function testRouter()
    {
        $this->assertNull(App::router());
    }

    public function testServices1()
    {
        $this->assertNull(App::services());
    }

    public function testEvents1()
    {
        $this->assertNull(App::events());
    }

    public function testModules1()
    {
        $this->assertNull(App::modules());
    }

    public function testServices2()
    {
        App::set(new Application(new Locator()));
        $this->assertInstanceOf('Pop\Service\Locator', App::services());
    }

    public function testEvents2()
    {
        $this->assertInstanceOf('Pop\Event\Manager', App::events());
    }

    public function testModules2()
    {
        $this->assertInstanceOf('Pop\Module\Manager', App::modules());
    }

    public function testAutoloader()
    {
        $this->assertNull(App::autoloader());
    }

}
