<?php

namespace Pop\Test;

use PHPUnit\Framework\TestCase;
use Pop\Middleware\Manager;


class ManagerTest extends TestCase
{

    public function testSetAndRemoveItems()
    {
        $manager = new Manager();
        $manager->setItems(['Test1Middleware', 'Test2Middleware']);
        $manager->addItems(['Test3Middleware', 'Test4Middleware']);
        $manager->addItem('Test5Middleware');
        $manager->new_item = 'Test6Middleware';
        $this->assertTrue($manager->hasItems());
        $this->assertEquals(6, $manager->count());
        $this->assertEquals('Test6Middleware', $manager->getItem('new_item'));
        $manager->removeItem('new_item');
        $this->assertCount(5, $manager->getItems());
    }

    public function testSetAndRemoveHandlers()
    {
        $manager = new Manager(['Test1Middleware', 'Test2Middleware']);
        $manager->addHandlers(['Test3Middleware', 'Test4Middleware']);
        $manager->addHandler('Test5Middleware');
        $manager->new_handler = 'Test6Middleware';
        $this->assertTrue($manager->hasHandlers());
        $this->assertTrue($manager->hasHandler('new_handler'));
        $this->assertEquals(6, $manager->count());
        $this->assertEquals('Test6Middleware', $manager->getHandler('new_handler'));
        $manager->removeHandler('new_handler');
        $this->assertCount(5, $manager->getHandlers());
    }

}
