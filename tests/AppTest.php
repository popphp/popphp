<?php

namespace Pop\Test;

use Pop\App;
use PHPUnit\Framework\TestCase;
use Pop\Application;
use Pop\Service\Locator;

class AppTest extends TestCase
{

    public function tearDown(): void
    {
        // App::$application is a private static with no public unset - reset
        // it via reflection after every test so these tests don't depend on
        // declaration order (App::set() has no counterpart to undo it).
        $reflection = new \ReflectionClass(App::class);
        $property   = $reflection->getProperty('application');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

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
        App::set(new Application());
        $this->assertInstanceOf('Pop\Event\Manager', App::events());
    }

    public function testModules2()
    {
        App::set(new Application());
        $this->assertInstanceOf('Pop\Module\Manager', App::modules());
    }

    public function testServicesByKey()
    {
        App::set(new Application(new Locator(['foo' => function() { return 123; }])));
        $this->assertEquals(123, App::services('foo'));
        $this->assertNull(App::services('missing'));
    }

    public function testEventsByKey()
    {
        App::set(new Application());
        App::events()->on('foo', function() { return 123; });
        $this->assertInstanceOf('SplPriorityQueue', App::events('foo'));
        $this->assertNull(App::events('missing'));
    }

    public function testModulesByKey()
    {
        $module = new \Pop\Module\Module();
        $module->setName('foo');

        App::set(new Application());
        App::modules()->register($module);
        $this->assertInstanceOf('Pop\Module\Module', App::modules('foo'));
        $this->assertNull(App::modules('missing'));
    }

    public function testAutoloader()
    {
        $this->assertNull(App::autoloader());
    }

}
