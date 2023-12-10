<?php

namespace Pop\Test;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{

    public function testSetAndGetDefaultAction()
    {
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->setDefaultAction('default');
        $this->assertEquals('default', $controller->getDefaultAction());
    }

    public function testSetAndGetMaintenanceAction()
    {
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->setMaintenanceAction('mt');
        $this->assertEquals('mt', $controller->getMaintenanceAction());
    }

    public function testDispatch()
    {
        $controller = $this->getMockForAbstractClass(
            '\Pop\Controller\AbstractController', [], '', false, false, true, ['error', 'login', 'user']
        );
        $controller->expects($this->once())->method('error');
        $controller->expects($this->once())->method('login');
        $controller->expects($this->once())->method('user');
        $controller->dispatch('login');
        $controller->dispatch('user', [1001]);
        $controller->dispatch();
    }

    public function testDispatchException()
    {
        $this->expectException('Pop\Controller\Exception');
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->setDefaultAction('foo');
        $controller->dispatch('foo');
    }

    public function testMaintenance()
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/tmp');
        $dotEnv->load();

        $controller = $this->getMockForAbstractClass(
            '\Pop\Controller\AbstractController', [], '', false, false, true, ['maintenance', 'error', 'login', 'user']
        );
        $controller->expects($this->atMost(2))->method('maintenance');
        $controller->dispatch();
    }

    public function testMaintenanceException()
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/tmp');
        $dotEnv->load();

        $this->expectException('Pop\Controller\Exception');
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->dispatch();
    }

}
