<?php

namespace Pop\Test;

use Pop\App;
use Pop\Application;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class RealAppTest extends TestCase
{

    protected ?Application $app = null;

    public function setUp(): void
    {
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $dotEnv = Dotenv::createImmutable(__DIR__);
        $dotEnv->load();

        $this->app = new Application([
            'foo' => 'bar'
        ]);
    }

    public function testHasApp()
    {
        $this->assertTrue(App::has());
        $this->assertInstanceOf('Pop\Application', App::get());
    }

    public function testConfig()
    {
        $this->assertIsArray(App::config());
        $this->assertEquals('bar', App::config('foo'));
    }

    public function testName()
    {
        $this->assertEquals('Pop', App::name());
        $this->assertEquals('Pop', $this->app->name());
    }

    public function testUrl()
    {
        $this->assertEquals('http://localhost', App::url());
        $this->assertEquals('http://localhost', $this->app->url());
    }

    public function testEnvironment()
    {
        $this->assertEquals('local', App::environment());
        $this->assertTrue(App::environment('local'));
        $this->assertEquals('local', $this->app->environment());
        $this->assertTrue($this->app->environment('local'));
    }

    public function testEnvironmentAliasMethods()
    {
        $this->assertEquals('local', App::environment());
        $this->assertTrue(App::isLocal());
        $this->assertFalse(App::isDev());
        $this->assertFalse(App::isTesting());
        $this->assertFalse(App::isStaging());
        $this->assertFalse(App::isProduction());
        $this->assertEquals('local', $this->app->environment());
        $this->assertTrue($this->app->isLocal());
        $this->assertFalse($this->app->isDev());
        $this->assertFalse($this->app->isTesting());
        $this->assertFalse($this->app->isStaging());
        $this->assertFalse($this->app->isProduction());
    }

    public function testMaintenanceMode()
    {
        $this->assertEquals('local', App::environment());
        $this->assertFalse(App::isUp());
        $this->assertTrue(App::isDown());
        $this->assertEquals('local', $this->app->environment());
        $this->assertFalse($this->app->isUp());
        $this->assertTrue($this->app->isDown());
    }

    public function testTrue()
    {
        $this->assertTrue(App::env('TEST_TRUE'));
        $this->assertTrue($this->app->env('TEST_TRUE'));
    }

    public function testFalse()
    {
        $this->assertFalse(App::env('TEST_FALSE'));
        $this->assertFalse($this->app->env('TEST_FALSE'));
    }

    public function testEmpty()
    {
        $this->assertEmpty(App::env('TEST_EMPTY'));
    }

    public function testNull()
    {
        $this->assertNull(App::env('TEST_NULL'));
    }

    public function testDefault()
    {
        $this->assertEquals(123, App::env('NO_VALUE', 123));
    }

    public function testIsSecretRequest()
    {
        $_GET['secret'] = '123456';
        $this->assertFalse(App::isSecretRequest());
    }

}
