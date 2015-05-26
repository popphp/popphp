<?php

namespace Pop\Test;

use Pop\Controller\AbstractController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testSetAndGetDefaultAction()
    {
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->setDefaultAction('default');
        $this->assertEquals('default', $controller->getDefaultAction());
    }

    public function testDispatch()
    {
        $controller = $this->getMockForAbstractClass(
            '\Pop\Controller\AbstractController', [], '', false, false, false, ['error', 'login', 'user']
        );
        $controller->expects($this->once())->method('error');
        $controller->expects($this->once())->method('login');
        $controller->expects($this->once())->method('user');
        $controller->dispatch('login');
        $controller->dispatch('user', [1001]);
        $controller->dispatch();
    }

    /**
     * @expectedException \Pop\Controller\Exception
     */
    public function testDispatchException()
    {
        $controller = $this->getMockForAbstractClass('\Pop\Controller\AbstractController');
        $controller->setDefaultAction('foo');
        $controller->dispatch('foo');
    }

}