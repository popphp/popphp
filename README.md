popphp
======

[![Build Status](https://github.com/popphp/popphp/workflows/phpunit/badge.svg)](https://github.com/popphp/popphp/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=popphp)](http://cc.popphp.org/popphp/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Applications](#applications)
  - [HTTP Applications](#setting-up-an-http-application)
  - [CLI Applications](#setting-up-a-cli-application)
  - [CLI Shortcut](#cli-shortcut)
  - [Flexible Constructor](#flexible-constructor)
* [App Helper](#app-helper)
* [Router](#router)
    - [HTTP Routes](#http-routes)
    - [CLI Routes](#cli-routes)
    - [Dynamic Routing](#dynamic-routing)
* [Controllers](#controllers)
* [Models](#models)
    - [Data Models](#data-models)
* [Modules](#modules)
    - [Custom Modules](#custom-modules)
    - [Module Manager](#module-manager)
* [Event Manager](#event-manager)
* [Service Locator](#service-locator)
* [Configuration Tips](#configuration-tips)

Overview
--------

`popphp` is the main set of core components for the [Pop PHP Framework](https://www.popphp.org/).
It provides the main Application object that can be configured to manage
and interface with the underlying core components:

* Router
* Controller
* Model
* Modules
* Event Manager
* Service Locator

[Top](#popphp)

Install
-------

Install `popphp` using Composer.

    composer require popphp/popphp

Or, require it in your composer.json file

    "require": {
        "popphp/popphp" : "^4.3.8"
    }

[Top](#popphp)

Quickstart
----------

Here's a config file for a basic HTTP web application with some routes in it:

#### app.http.php

```php
<?php
return [
    'routes' => [
        '/' => [
            'controller' => 'MyApp\Controller\IndexController',
            'action'     => 'index'
        ],
        '*' => [
            'controller' => 'MyApp\Controller\IndexController',
            'action'     => 'error'
        ]
    ]
];
```

And here's a basic `index.php` front controller that will drive the application:

#### index.php

```php
$app = new Pop\Application(include __DIR__ . '/config/app.http.php');
$app->run();
```

Any request that comes to that front controller will be routed accordingly. For example,
the request `/`:

```bash
$ curl -i -X GET http://localhost/
```

would route to and execute the `MyApp\Controller\IndexController->index` method.

Any invalid request would route to the `MyApp\Controller\IndexController->error` method. 

[Top](#popphp)

Applications
------------

#### Setting up an HTTP application

Here's an extended example of how to wire up a web application object with a configuration
file that defines some basic routes:

##### app.http.php

```php
<?php
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
The application object will parse the `routes` array and register those routes with
the application.

The `index.php` front controller for the web application would look like this:

##### index.php

```php
$app = new Pop\Application(include __DIR__ . '/config/app.http.php');
$app->run();
```

An example of a valid request to the above HTTP application would be:

```bash
$ curl -i -X GET http://localhost/edit/1001
```

An example of an invalid request would be:

```bash
$ curl -i -X GET http://localhost/bad-request
```

[Top](#popphp)

#### Setting up a CLI application

Here's an example of how to wire up a CLI-based application object with a configuration
file that defines some basic routes:

##### app.cli.php

```php
<?php
return [
    'routes' => [
        'help' => [
            'controller' => 'MyApp\Controller\ConsoleController',
            'action'     => 'help'
        ],
        'hello <name>' => [
            'controller' => 'MyApp\Controller\ConsoleController',
            'action'     => 'hello'
        ],
        '*' => [
            'controller' => 'MyApp\Controller\ConsoleController',
            'action'     => 'error'
        ]
    ]
];
```

The `app.php` front controller (or main script) for the CLI application would look like this:

##### app.php

```php
$app = new Pop\Application(include __DIR__ . '/config/app.cli.php');
$app->run();
```

As before, the actions listed in the `app.cli.php` config above will be routed to methods within the
`MyApp\Controller\ConsoleController` object, `help()` and `hello($name)` respectively. And like HTTP,
a default `error()` action can be defined to handle invalid CLI commands.

An example of a valid request to the above CLI application would be:

```bash
$ php app.php hello Nick
```

An example of an invalid request would be:

```bash
$ php app.php bad request
```

[Top](#popphp)

#### CLI Shortcut

Depending on your environment, a CLI front controller or script can be shortened to just a file basename
(without the `.php` extension), for example:

```bash
$ ./app
```

But the script and its contents would have to be properly configured, for example:

```php
#!/usr/bin/php
<?php

/* include any autoloader or other content */

$app = new Pop\Application(include __DIR__ . '/config/app.cli.php');
$app->run();
```

and set to be executable:

```bash
$ chmod 755 ./app
```

Then the CLI application can be accessed in a shortened, more concise way, like:

```bash
$ ./app hello Nick
```

[Top](#popphp)

#### Flexible Constructor

The application object has a flexible constructor that allows you to inject any of the following in
any order:

```php
$app = new Pop\Application(
    $config,     // An array, an array-like object or an instance of Pop\Config\Config
    $autoloader, // An instance of Composer\Autoload\ClassLoader
    $router,     // An instance of Pop\Router\Router
    $services,   // An instance of Pop\Service\Locator
    $events,     // An instance of Pop\Event\Manager
    $modules,    // An instance of Pop\Module\Manager
);
```


[Top](#popphp)

App Helper
----------

There is an "app helper" class that provides access to any environmental variables set in the `.env` file
as well as provides quick access to the current application object from anywhere in your application life cycle.
When an application object is created and bootstrapped, it is automatically registered with this static class.

```php
use Pop\App;

$app = App::get(); // Returns the instance of the Pop\Application object
```

At anytime in the application life cycle, you can use the API of the app helper class to access environmental
variables, like this:

```php
use Pop\App;

if (App::env('SOME_VALUE') == 'foo') {
    // Do something
}
```

#### Application Environment

The application environment variable sets what type of environment the current running app is in. Supported values
for the `APP_ENV` variable are:

- `local`
- `dev`
- `testing`
- `staging`
- `production` (or just `prod`) 

```php
use Pop\App;

if (App::isLocal()) {
    // Do something in the local environment
} else if (App::isProduction()) {
    // Do something in the production environment
}
```

#### Maintenance Mode

The `MAINTENANCE_MODE` variable can be set to either `true` or `false` to put the application into a controlled
"down" state while upgrades and/or maintenance are being performed.

```php
use Pop\App;

if (App::isDown()) {
    // Handle the app in "maintenance mode"
}
```

The full API is:

- `App::config(?string $key = null)`
- `App::name()`
- `App::url()`
- `App::env(string $key, mixed $default = null)`
- `App::environment(mixed $env = null)`
- `App::isLocal()`
- `App::isDev()`
- `App::isTesting()`
- `App::isStaging()`
- `App::isProduction()`
- `App::isDown()`
- `App::isUp()`

And the above static methods are also available on the application object instance as well:

- `$app->name()`
- `$app->url()`
- `$app->env(string $key, mixed $default = null)`
- `$app->environment(mixed $env = null)`
- `$app->isLocal()`
- `$app->isDev()`
- `$app->isTesting()`
- `$app->isStaging()`
- `$app->isProduction()`
- `$app->isDown()`
- `$app->isUp()`


[Top](#popphp)

Router
------

The router object is one of the main components of a Pop application. It serves as the gatekeeper
that routes requests to their proper controller. It works for both HTTP web applications and CLI-based
applications. The router object will auto-detect the environment and use the correct router matching
object for it.

With the `app.http.php` config above, the actions listed  will be routed to methods within the
`MyApp\Controller\IndexController` object, `index()`, `users()`, `edit($id)` and `error()` respectively.

The route `/users[/]` allows for an optional trailing slash. The route `/edit/:id` is expecting a value
that will populate the `$id` parameter that will be passed into the `edit($id)` method, such as `/edit/1001`.
Failure to have the ID segment of the URL will result in a non-match, or invalid route.

If you don't want to be so strict about the parameters passed into a method or function, you can make
the parameter optional like this: `/edit[/:id]`. The respective method signature would be `edit($id = null)`.

[Top](#popphp)

### HTTP Routes

Here is a list of possible route syntax options for HTTP applications:

|HTTP Route        |What's Expected                                                     |
|------------------|--------------------------------------------------------------------|
|/foo/:bar/:baz    |The 2 params are required                                           |
|/foo/:bar[/:baz]  |First param required, last one is optional                          |
|/foo/:bar/:baz*   |One required param, one required param that is a collection (array) |
|/foo/:bar[/:baz*] |One required param, one optional param that is a collection (array) |


[Top](#popphp)

### CLI Routes

Here is a list of possible route syntax options for CLI applications:

|CLI Route                    |What's Expected                                           |
|-----------------------------|----------------------------------------------------------|
|foo bar                      |Two commands are required                                 |
|foo bar\|baz                 |Two commands are required, the 2nd can accept 2 values    |
|foo [bar\|baz]               |The second command is optional and can accept 2 values    |
|foo \<name\> [\<email\>]     |First parameter required, 2nd parameter optional          |
|foo --name=\|-n [-e\|--email=] |First option value required, 2nd option value is optional |
|foo [--option\|-o]            |Option with both long and short formats                   |

Options are passed as the last parameter injected into the route parameters of the route method or function.
The `$options` parameter will be an array. When the options are simple flags, the values in the array are booleans:

```php
function($name, $email = null, array $options = []) { }
```

```bash
./foo -p --verbose John john@test.com
```

```php
$options = [
    'p'       => true,
    'verbose' => true,
];
```

Option values will populate the `$options` parameter in key/value pairs, like this:

```bash
./foo [-n|--name=]
```

```bash
./foo -nJohn
```

```bash
./foo --name=John
```

```php
$options = ['name' => 'John'];
```

[Top](#popphp)

### Dynamic Routing

There is support for dynamic routing for both HTTP and CLI applications. The reserved route keywords
`controller` and `action` are used to map the route to a matched controller class and respective
action method within that class. You could define a dynamic HTTP route like this:

```php
<?php
return [
    'routes' => [
        '/:controller/:action[/:param]' => [
            'prefix' => 'MyApp\Controller\\'
        ]
    ]
];
```

which will map a route like

```text
/users/edit/1001
MyApp\Controller\UsersController->edit($id)
```


A dynamic CLI route like would work in a similar fashion:

```php
<?php
return [
    'routes' => [
        'foo <controller> <action> [<param>]' => [
            'prefix' => 'MyApp\Controller\\'
        ]
    ]
];
```

which will map a route like

```text
./foo users edit 1001
MyApp\Controller\UsersController->edit($id)
```

[Top](#popphp)

Controllers
-----------

The controller object is the 'C' in the MVC design pattern and gives you the ability to encapsulate
the behavior and functionality of how the routes behave and are handled. But it should be noted that
you don't have to use a full controller object. For smaller applications, you can use anything that is
callable, like a closure. An example of that would be:

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

But, for most large-scale applications, it would be best to use a full controller object to manage the
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

    // This is the default value
    protected string $defaultAction = 'error';

    // This is the default value
    protected string $maintenanceAction = 'maintenance';


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

    public function maintenance()
    {
        // Handle requests that come in while the application is in maintenance mode
    }

}
```

[Top](#popphp)

Models
------

The model object is the 'M' in the MVC design pattern and gives you the ability to map your data to
an object that can be consumed and utilized by the other parts of you application. An abstract model
class is provided, `Pop\Model\AbstractModel`, and it represents a basic data object the acts more or
less like any array or value object. It has a single property `data`, implements `ArrayAccess`,
`Countable` and `IteratorAggregate`. Once you extend the abstract model class, you build in the logic
needed to handle the business logic in your application.

### Data Models

Going one level further, the abstract class `Pop\Model\AbstractDataModel` is also available, which provides
a tightly integrated API which some common interactions with a database and its records. The basic requirements
are that there is a model class that extends the abstract data model and a subsequent related table class
(see the `pop-db` [documentation](https://github.com/popphp/pop-db#table-class) for more info.) In the example
below, the classes `MyApp\Model\User` and `MyApp\Table\Users` are created, and by that naming convention, they
are linked together. 

```php
<?php

namespace MyApp\Table;

use Pop\Db\Record;

class Users extends Record
{

}
```

```php
<?php

namespace MyApp\Model;

use Pop\Model\AbstractModel;

class User extends AbstractDataModel
{

}
```

The available API in the data model object is:

**Static Methods**

- `fetchAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool $asArray = true): array|Collection`
- `fetch(mixed $id, bool $asArray = true): array|Record`
- `createNew(array $data, bool $asArray = true): array|Record`
- `filterBy(mixed $filters = null, mixed $select = null): static`

**Instance Methods**

- `getAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool $asArray = true): array|Collection`
- `getById(mixed $id, bool $asArray = true): array|Record`
- `create(array $data, bool $asArray = true): array|Record`
- `update(mixed $id, array $data, bool $asArray = true): array|Record`
- `replace(mixed $id, array $data, bool $asArray = true): array|Record`
- `delete(mixed $id): int`
- `remove(array $ids): int`
- `count(): int`
- `describe(bool $native = false, bool $full = false): array`
- `hasRequirements(): bool`
- `validate(array $data): bool|array`
- `filter(mixed $filters = null, mixed $select = null): AbstractDataModel`
- `select(mixed $select = null): AbstractDataModel`

**Create new**

```php
use MyApp\Model\User;

$user = User::createNew($userData);
```

**Update**

```php
use MyApp\Model\User;

$userModel = new User();
$user = $userModel->update(1, $userData);
```

The `update()` method acts like a `PATCH` call and `replace()` acts like a `PUT` call and will replace and reset all model data.

**Delete**

```php
use MyApp\Model\User;

$userModel = new User();
$userModel->delete(1);
$userModel->remove([2, 3, 4]);
```

**Fetch**

```php
use MyApp\Model\User;

$users = User::fetchAll();
$user  = User::fetch(1);
```

**Filter and sort**

```php
use MyApp\Model\User;

$users = User::filter('username LIKE myuser%')->getAll('-id', '10', 2);
```

The above call filters the search by the filter string and sorts by `ID DESC` (`-id`). Also, it sets the limit to `10`
and starts the page offset on the second page.

[Top](#popphp)

Modules
-------

Modules can be thought of as "mini-application objects" that allow you to extend the functionality
of your application. Module objects accept similar configuration parameters as an application object,
such as `routes`, `services` and `events`. Additionally, it accepts a `prefix` configuration
value as well to allow the module to register itself with the application autoloader. Here's an example
of what a module might look like and how you'd register it with an application:

**Configuration Array**

In the example below, the module configuration is passed into the application object. From there,
an instance of the base module object is created and the configuration is passed into it. The newly
created module object is then registered with the module manager within the application object.

```php
$application = new Pop\Application();

$moduleConfig = [
    'routes' => [
        '/' => [
            'controller' => 'MyModule\Controller\IndexController',
            'action'     => 'index'
        ]
    ],
    'prefix' => 'MyModule\\'
];

$application->register('my-module', $moduleConfig);
```

**Module Instance**

In the example below, a module object is created and passed directly into the application object. The
module object is then registered with the module manager within the application object.

```php
$application = new Pop\Application();

$myModule = new Pop\Module\Module([
    'name'   => 'my-module',
    'routes' => [
        '/' => [
            'controller' => 'MyModule\Controller\IndexController',
            'action'     => 'index'
        ]
    ],
    'prefix' => 'MyModule\\'
];

$application->register($myModule);
```

[Top](#popphp)

### Custom Modules

You can pass your own custom module objects into the application as well, as long as they implement
the interface `Pop\Module\ModuleInterface` provided. As the example below shows, you can create a new instance of your
custom module and pass that into the application. The benefit of
doing this is to allow you to extend the base module class and methods and provide any additional
functionality that may be needed. In doing it this way, however, you will have to register your module's
namespace prefix with the application's autoloader prior to registering the module with the application
so that the application can properly detect and load the module's source files.

```php
$application->autoloader->addPsr4('MyModule\\', __DIR__ . '/modules/mymodule/src');

$myModule = new MyModule\Module([
    'routes' => [
        '/' => [
            'controller' => 'MyModule\Controller\IndexController',
            'action'     => 'index'
        ]
    ]
]);

$application->register('myModule', $myModule);
```

[Top](#popphp)

### Module Manager

The module manager provides a way to extend the core functionality of your application. The module manager
object is really a collection object of actual module objects that serves as the bridge to integrate the
modules with the application. You can think of the module objects themselves as "mini application objects"
because, like the application object, they can take a configuration array that will wire up routes and other
settings specific to the module.

Here's an example of a way to inject a module into an application. You'll want to register the autoloader
with the application so that it can handle the appropriate loading of the module files and classes within
the application.

```php
// Using Composer's autoloader
$autoloader = require __DIR__ . '/vendor/autoload.php';

$app = new Pop\Application($autoloader, include __DIR__ . '/config/app.php');

// $myModuleConfig contains the config settings for the
// module, such as the autoload prefix and the routes
$app->register(new MyModule($myModuleConfig));
```

[Top](#popphp)

Event Manager
-------------

The event manager provides a way to hook specific events and functionality into certain points in the
application's life cycle. The default hook points with the application object are:

* app.init
* app.route.pre
* app.dispatch.pre
* app.dispatch.post
* app.error

You can simply register callable objects with the event manager to have them be called at that time
in the application's life cycle:

```php
$app->on('app.route.pre', function($application) {
    // Do some pre-route stuff
});
```

[Top](#popphp)

Service Locator
---------------

The service locator provides a way to make common services available throughout the application's
life cycle. You can set them up at the beginning of the application and call them any time during
the application's life cycle.

```php
$app->setService('foo', 'MyApp\FooService');
```

From inside a controller object:

```php
<?php
namespace MyApp\Controller;

use Pop\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        $foo = $this->application->services['foo'];
        // Do something with the 'foo' service
    }
}
```

#### Service Container

If you are in an area of the application where you don't have direct access to the
application's service locator, you can use the globally available service container:

```php
<?php
namespace MyApp\Controller;

use Pop\Service\Container;
use Pop\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        // 'default' is the default service container. Other service containers may be available.
        $foo = Container::get('default')->get('foo');
        // Do something with the 'foo' service
    }
}
```

[Top](#popphp)

Configuration Tips
------------------

In the above examples, both the application and module config arrays can have a `routes` key
set that defines the routes of the application or module. Additionally, the keys `events` and
`services` are allowed as well, so an application or module can be wired up all from the
configuration array:

```php
<?php
return [
    'routes'   => [
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
    ],
    'services' => [
        'session' => [
            'call' => 'Pop\Session\Session::getInstance'
        ]
    ],
    'events' => [
        [
            'name'     => 'app.route.post',
            'action'   => 'MyApp\Event\Foo::bootstrap',
            'priority' => 1000
        ]
    ]
];
```

The config also supports the keys `prefix`, `psr-0` and `src` for autoloading purposes.
The default is to autoload with PSR-4, unless the `psr-0` key is set to `true`.

```php
<?php
return [
    'prefix' => 'MyModule\\',
    'src'    => __DIR__ . '/../src',
];
```

#### Helper Functions

The helper functions available from the `pop-utils` component are automatically loaded within
the application object's `boostrap` call. If this is not desired, a configuration setting called
`helper_functions` (set to `false`) can be passed to prevent them from loading:

```php
$app = new Pop\Application([
    'helper_functions' => false
]);
```

[Top](#popphp)
