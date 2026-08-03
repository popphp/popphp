<?php

namespace Pop\Test;

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
        // dispatch()'s body now lives in Dispatch\AbstractDispatcher, so a bare
        // "not defined" throw resolves to Pop\Dispatch\Exception, not
        // Pop\Controller\Exception - a deliberate, accepted narrow BC break.
        $this->expectException('Pop\Dispatch\Exception');
        $controller = new TestController();
        $controller->setDefaultAction('foo');
        $controller->dispatch('foo');
    }

    public function testBypassMaintenanceDefaultsFalseAndCanBeSet()
    {
        // Maintenance-mode enforcement itself is exercised at the Application
        // level now (Application::run() consults this via Dispatch\MaintenanceInterface),
        // so this is just the getter/setter contract - no MAINTENANCE_MODE env
        // involvement needed, unlike the two tests this replaced.
        $controller = new TestController();
        $this->assertFalse($controller->bypassMaintenance());

        $controller->setBypassMaintenance(true);
        $this->assertTrue($controller->bypassMaintenance());
    }

    public function testDispatchMaintenanceRunsMaintenanceAction()
    {
        $controller = new TestController();
        $controller->dispatchMaintenance();
        $this->assertEquals(0, $controller->getId());
    }

    public function testDispatchMaintenanceExceptionWhenActionNotDefined()
    {
        // Maintenance-mode enforcement now lives in Application::run() (calling
        // dispatchMaintenance() directly), not in AbstractController::dispatch()
        // itself - so this exercises Dispatch\MaintenanceTrait's own throw path,
        // and the exception is now Pop\Dispatch\Exception (this logic's new,
        // shared home), not Pop\Controller\Exception.
        $this->expectException('Pop\Dispatch\Exception');
        $controller = new TestController2();
        $controller->dispatchMaintenance();
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
