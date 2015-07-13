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

## BASIC USAGE

* [The Application Object](#the-application-object)
* [The Router Object](#the-router-object)
* [A Controller Object](#a-controller-object)
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

|Route Web Definition           |What's Expected                                                    |
|-------------------------------|-------------------------------------------------------------------|
|/foo/:bar/:baz                 |All 3 params are required                                          |
|/foo[/:bar][/:baz]             |First param required, last two are optional                        |
|/foo/:bar[/:baz]               |First two params required, last one is optional                    |
|/foo/:bar/:baz[/:some][/:other]|Two required, two optional                                         |
|/foo/:bar/:baz*                |One required param, one required param that is a collection (array)|
|/foo/:bar[/:baz*]              |One required param, one optional param that is a collection (array)|

And here is a list of possible route options for CLI applications:

|Route CLI Definition             |What's Expected                                                     |
|---------------------------------|--------------------------------------------------------------------|
|foo bar                          |All 2 params are required                                           |
|foo [bar|baz]                    |First param required, 2nd param has optional 2 values               |
|foo -o1 [-o2]                    |First param required, 1st option required, 2nd option optional      |
|foo --option1|-o1 [--option2|-o2]|First param required, 1st option required, 2nd option optional      |
|foo <name> [<email>]             |First param required, 1st value required, 2nd value optional        |
|foo --name= [--email=]           |First param required, 1st opt value required, 2nd opt value optional|

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



