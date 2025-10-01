<?php

namespace Pop\Test;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Pop\Application;
use Pop\Test\TestAsset\TestController;
use Pop\Test\TestAsset\TestController2;
use Pop\Test\TestAsset\TestHttpController;
use Pop\Test\TestAsset\TestConsoleController;

class ControllerTest extends TestCase
{

    public function setUp(): void
    {
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testSetAndGetDefaultAction()
    {
        $controller = new TestController();
        $controller->setDefaultAction('default');
        $this->assertEquals('default', $controller->getDefaultAction());
    }

    public function testSetAndGetMaintenanceAction()
    {
        $controller = new TestController();
        $controller->setMaintenanceAction('mt');
        $this->assertEquals('mt', $controller->getMaintenanceAction());
    }

    public function testDispatch()
    {
        $controller = new TestController();
        $controller->dispatch('delete');
        $controller->dispatch('edit', [1001]);
        $this->assertEquals(1001, $controller->getId());
        $controller->dispatch();
        $this->assertEquals(0, $controller->getId());
    }

    public function testDispatchException()
    {
        $this->expectException('Pop\Controller\Exception');
        $controller = new TestController();
        $controller->setDefaultAction('foo');
        $controller->dispatch('foo');
    }

    public function testMaintenance1()
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/tmp');
        $dotEnv->load();

        $controller = new TestController();
        $this->assertFalse($controller->bypassMaintenance());
        $controller->dispatch();
    }

    public function testMaintenance2()
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/tmp');
        $dotEnv->load();

        $controller = new TestController();

        $this->assertFalse($controller->bypassMaintenance());
        $controller->setBypassMaintenance(true);
        $this->assertTrue($controller->bypassMaintenance());
        $controller->dispatch();
    }

    public function testMaintenanceException()
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/tmp');
        $dotEnv->load();

        $this->expectException('Pop\Controller\Exception');
        $controller = new TestController2();
        $controller->dispatch();
    }

    public function testHttpControllerTrait()
    {
        $httpController = new TestHttpController(new Application());
        $this->assertInstanceOf('Pop\Application', $httpController->application());
        $this->assertInstanceOf('Pop\Http\Server\Request', $httpController->request());
        $this->assertInstanceOf('Pop\Http\Server\Response', $httpController->response());
    }

    public function testConsoleControllerTrait()
    {
        $consoleController = new TestConsoleController(new Application());
        $this->assertInstanceOf('Pop\Application', $consoleController->application());
        $this->assertInstanceOf('Pop\Console\Console', $consoleController->console());
    }

}
