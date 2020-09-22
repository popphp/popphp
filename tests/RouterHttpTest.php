<?php

namespace Pop\Test;

use Pop\Router\Match\Http;
use PHPUnit\Framework\TestCase;
use Pop\Router\Router;
use Pop\Router\Route;

class RouterHttpTest extends TestCase
{

    public function testHttpRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpRouteWithSlash()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo/' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpRouteWithOptionalSlash()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo[/]' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpPreparedRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo/1';
        $routes = [
            '/foo/:id' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpNoRouteFound()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/bar' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertFalse($http->hasRoute());

        ob_start();
        $http->noRouteFound(false);
        $result = ob_get_clean();

        $this->assertContains('Page Not Found', $result);
    }

    public function testHttpDefaultRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '*' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testNamedRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new Http());
        $router->addRoute('/user/:id', function($id) {
            echo 'User: ' . $id;
        })->name('user');

        $router->addRoute('/user/show/:id*', function($id) {
            echo 'User: ' . $id;
        })->name('user.show');

        Route::setRouter($router);

        $userObject      = new \stdClass();
        $userObject->id  = 1;
        $usersObject     = new \stdClass();
        $usersObject->id = [1, 2, 3];

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('/user/1', Route::url('user', ['id' => 1]));
        $this->assertEquals('/user/show/1/2/3', Route::url('user.show', ['id' => [1, 2, 3]]));
        $this->assertEquals('/user/1', Route::url('user', $userObject));
        $this->assertEquals('/user/show/1/2/3', Route::url('user.show', $usersObject));
    }

    public function testNamedRouteFqdn()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';
        $_SERVER['HTTP_HOST']     = 'www.domain.com';

        $router = new Router(null, new Http());
        $router->addRoute('/user/:id', function($id) {
            echo 'User: ' . $id;
        })->name('user');

        Route::setRouter($router);

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('http://www.domain.com/user/1', Route::url('user', ['id' => 1], true));
    }

    public function testNamedRouteException()
    {
        $this->expectException('Pop\Router\Exception');

        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new Http());

        Route::setRouter($router);

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('/user/1', Route::url('user', ['id' => 1]));
    }

    public function testNamedRouteNotHttpException()
    {
        $this->expectException('Pop\Router\Exception');

        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new \Pop\Router\Match\Cli());

        Route::setRouter($router);
        $this->assertEquals('/user', $router->getUrl('user'));
    }

}
