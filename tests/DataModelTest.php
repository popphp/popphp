<?php

namespace Pop\Test;

use PHPUnit\Framework\TestCase;
use Pop\Db\Db;
use Pop\Db\Record;
use Pop\Test\TestAsset\Model\User;

class DataModelTest extends TestCase
{

    public function setUp(): void
    {
        Record::setDb(Db::sqliteConnect([
            'database' => __DIR__ . '/database/.htpopdb.sqlite'
        ]));
    }

    public function testCreate()
    {
        $user      = User::createNew([
            'username' => 'testuser1',
            'email'    => 'testuser1@test.com'
        ]);

        $this->assertEquals('testuser1', $user['username']);
        $this->assertEquals('testuser1@test.com', $user['email']);
        $this->assertEquals(1, $user['id']);

        Record::db()->disconnect();
    }

    public function testDescribe()
    {
        $userModel = new User();
        $this->assertCount(3, $userModel->describe());
        $this->assertCount(2, $userModel->describe('id, username'));
    }

    public function testGetAll()
    {
        $users = User::fetchAll();

        $this->assertEquals('testuser1', $users[0]['username']);
        $this->assertEquals('testuser1@test.com', $users[0]['email']);
        $this->assertEquals(1, $users[0]['id']);
        $this->assertEquals(1, (new User())->count());

        Record::db()->disconnect();
    }

    public function testCountAndFilters1()
    {
        $userModel = new User();
        $count     = $userModel->filter('username LIKE testuser1%', ['id', 'username'])->count();
        $users     = $userModel->getAll();

        $this->assertEquals(1, $count);
        $this->assertEquals('testuser1', $users[0]['username']);
        $this->assertFalse(isset($users[0]['email']));
        $this->assertEquals(1, $users[0]['id']);

        Record::db()->disconnect();
    }

    public function testCountAndFilters2()
    {
        $users = User::filterBy('username LIKE testuser1%', ['id', 'username'])->getAll();

        $this->assertEquals('testuser1', $users[0]['username']);
        $this->assertFalse(isset($users[0]['email']));
        $this->assertEquals(1, $users[0]['id']);

        Record::db()->disconnect();
    }

    public function testCountAndFiltersClear()
    {
        $userModel = new User();
        $count     = $userModel->filter(null, null)->count();
        $users     = $userModel->getAll();

        $this->assertEquals(1, $count);
        $this->assertEquals('testuser1', $users[0]['username']);
        $this->assertEquals('testuser1@test.com', $users[0]['email']);
        $this->assertEquals(1, $users[0]['id']);

        Record::db()->disconnect();
    }

    public function testGetById()
    {
        $user = User::fetch(1);

        $this->assertEquals('testuser1', $user['username']);
        $this->assertEquals('testuser1@test.com', $user['email']);
        $this->assertEquals(1, $user['id']);

        Record::db()->disconnect();
    }

    public function testUpdate()
    {
        $userModel = new User();
        $user      = $userModel->update(1, [
            'username' => 'testuser2',
            'email'    => 'testuser2@test.com'
        ]);

        $this->assertEquals('testuser2', $user['username']);
        $this->assertEquals('testuser2@test.com', $user['email']);
        $this->assertEquals(1, $user['id']);

        Record::db()->disconnect();
    }

    public function testReplace()
    {
        $userModel = new User();
        $user      = $userModel->replace(1, [
            'username' => 'testuser3',
        ]);

        $this->assertEquals('testuser3', $user['username']);
        $this->assertNull($user['email']);
        $this->assertEquals(1, $user['id']);

        Record::db()->disconnect();
    }

    public function testRemove()
    {
        $userModel = new User();
        $users      = $userModel->remove([1]);
        $this->assertEquals(1, $users);

        Record::db()->disconnect();
    }

    public function testNoDelete()
    {
        $userModel = new User();
        $users      = $userModel->delete(1);
        $this->assertEquals(0, $users);

        Record::db()->disconnect();
    }

    public function testGetOffsetAndLimit()
    {
        $userModel = new User();
        $offsetLimit1 = $userModel->getOffsetAndLimit(2, 10);
        $offsetLimit2 = $userModel->getOffsetAndLimit(null, 10);
        $this->assertEquals(10, $offsetLimit1['offset']);
        $this->assertEquals(10, $offsetLimit1['limit']);
        $this->assertEquals(null, $offsetLimit2['offset']);
        $this->assertEquals(10, $offsetLimit2['limit']);
    }

    public function testOrderBy()
    {
        $userModel = new User();
        $this->assertEquals('id DESC', $userModel->getOrderBy('-id'));
        $this->assertEquals('id ASC, username ASC', $userModel->getOrderBy('id,username'));
    }

}