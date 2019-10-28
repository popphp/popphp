<?php

namespace Pop\Test;

use Pop\Router\Match\Http;
use PHPUnit\Framework\TestCase;

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

}