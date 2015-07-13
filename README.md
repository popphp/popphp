popphp
======

[![Build Status](https://travis-ci.org/popphp/popphp.svg?branch=master)](https://travis-ci.org/popphp/popphp)

OVERVIEW
--------
`popphp` is the main set of core components for the Pop PHP Framework.
It provides the main application object that can be configured to manage
and interface with the underlying core components:
 
* Controller
* Event Manager
* Module Manager
* Router
* Service Locator

`popphp` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `popphp` using Composer.

    composer require popphp/popphp

BASIC USAGE
-----------

### Wiring a web application and routes 

```php
$routes = [
    '/' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'index'
    ],
    '/users[/]' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'users'
    ],
    '/edit/:id' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'edit'
    ],
    '*' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'error'
    ]
];

$app = new Pop\Application(new Pop\Router\Router($routes));
$app->run();
```

The actions listed above will be routed to methods within the controller object,
`index()`, `users()`, `edit($id)` and `error()` respectively.

### Wiring a CLI application and routes

```php
$routes = [
    'help' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'help'
    ],
    'hello <name>' => [
        'controller' => 'MyApp\Controller\IndexController',
        'action'     => 'hello'
    ]
];

$app = new Pop\Application(new Pop\Router\Router($routes));
$app->run();
```

The actions listed above will be routed to methods within the controller object,
`help()` and `hello($name)` respectively.


