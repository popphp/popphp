popphp
======

[![Build Status](https://travis-ci.org/popphp/popphp.svg?branch=master)](https://travis-ci.org/popphp/popphp)

OVERVIEW
--------
`popphp` is the main set of core components for the Pop PHP Framework.
It provides the main Application object that can be configured to manage
and interface with the underlying core components:
 
* Router
* Controller
* Module Manager
* Event Manager
* Service Locator

`popphp` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `popphp` using Composer.

    composer require popphp/popphp

## BASIC USAGE

* [The Application Object](#the-application-object)
* [The Router Object](#the-router-object)
* [The Controller Object](#the-controller-object)
* [The Module Manager](#the-module-manager)
* [The Event Manager](#the-event-manager)
* [The Service Locator](#the-service-locator)

### The Application Object 

Here's a simple example of wiring a web application object with a
configuration file that defines some basic routes:

###### application.php

```php
return [
    'routes' => [
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
    ]
];
```

Then you can use `include` to push the configuration array into the application object.
The application object will parse the `routes` array and register those routes with the application.

###### index.php

```php
$app = new Pop\Application(include __DIR__ . '/config/application.php');
$app->run();
```

[Top](#basic-usage)

### The Router Object

The router object is one of the main components of a Pop application. It serves as the gatekeeper
that routes requests to their proper controller.

With the `application.php` config above, the actions listed  will be routed to methods within the
`MyApp\Controller\IndexController` object, `index()`, `users()`, `edit($id)` and `error()` respectively.

The route '/users[/]' allows for an optional trailing slash. The route '/edit/:id' is expecting a value
that will populate the variable $id that will be passed into the `edit($id)` method, such as '/edit/1001'.
Failure to have the ID segment of the URL will result in an non-match, or invalid route.

If you don't want to be so strict about the parameters passed into a method or function, you can make
the parameter optional like this: '/edit[/:id]'. The respective method would then look like this:
`edit($id = null)`.

Here is a list of possible route options for web applications:

|Web Route                      |What's Expected                                                    |
|-------------------------------|-------------------------------------------------------------------|
|/foo/:bar/:baz                 |All 3 params are required                                          |
|/foo[/:bar][/:baz]             |First param required, last two are optional                        |
|/foo/:bar[/:baz]               |First two params required, last one is optional                    |
|/foo/:bar/:baz[/:some][/:other]|Two required, two optional                                         |
|/foo/:bar/:baz*                |One required param, one required param that is a collection (array)|
|/foo/:bar[/:baz*]              |One required param, one optional param that is a collection (array)|

And here is a list of possible route options for CLI applications:

|CLI Route                          |What's Expected                                                     |
|-----------------------------------|--------------------------------------------------------------------|
|foo bar                            |All 2 params are required                                           |
|foo [bar\|baz]                     |First param required, 2nd param has optional 2 values               |
|foo -o1 [-o2]                      |First param required, 1st option required, 2nd option optional      |
|foo --option1\|-o1 [--option2\|-o2]|First param required, 1st option required, 2nd option optional      |
|foo \<name\> [\<email\>]           |First param required, 1st value required, 2nd value optional        |
|foo --name= [--email=]             |First param required, 1st opt value required, 2nd opt value optional|

##### Routing for a CLI application

###### application.php

```php
return [
    'routes' => [
        'help' => [
            'controller' => 'MyApp\Controller\IndexController',
            'action'     => 'help'
        ],
        'hello <name>' => [
            'controller' => 'MyApp\Controller\IndexController',
            'action'     => 'hello'
        ]
    ]
];
```

###### index.php

```php
$app = new Pop\Application(include __DIR__ . '/config/application.php');
$app->run();
```

As before, the actions listed in the `application.php` config above will be routed to methods within the
`MyApp\Controller\IndexController` object, `help()` and `hello($name)` respectively.

### The Controller Object

The controller object is the 'C' in the MVC design pattern and gives you the ability to encapsulate
the behavior and functionality of how the routes behave and are handled. But it should be noted that
you don't have to use a full Controller Object. For smaller applications, you can use closures as well.
An example of that would be:

```php
use Pop\Application;
use Pop\Router\Router;

$routes = [
    '/hello' => [
        'controller' => function() {
            echo 'Hello World';
        }
    ],
    '/hello/:name' => [
        'controller' => function($name) {
            echo 'Hello ' . $name;
        }
    ]
];

$app = new Application(new Router($routes));
$app->run();
```

But, for most larger applications, it would be best to use a full controller object to manage the
overall behavior or what is to happen for specific routes. The base controller object is an abstract
controller class `Pop\Controller\AbstractController`, which implements `Pop\Controller\ControllerInterface`.
The base functionality is fairly simple and allows you to build and structure your controller as needed.
The only base functionality wired in is a `dispatch` method that handles the actual dispatching of
the appropriate method and also the default action methods to set up what happens with a route/method
isn't matched (typically used for error handling.)

Let's take a look at what the `MyApp\Controller\IndexController` class from the above web example
might look like:

```php
<?php

namespace MyApp\Controller;

use Pop\Controller\AbstractController;

class IndexController extends AbstractController
{

    public function index()
    {
        // Do something for the index page
    }

    public function users()
    {
        // Do something for the users page
    }

    public function edit($id)
    {
        // Edit user with $id
    }

    public function error()
    {
        // Handle a non-match route request
    }

}
```

