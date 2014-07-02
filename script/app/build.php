<?php

return new Pop\Config([
    'application' => [
        'name'    => 'HelloWorld',
        'base'    => __DIR__ . '/../../',
        'docroot' => __DIR__ . '/../../public'
    ],
    'databases' => [
        'helloworld' => [
            'type'     => 'Sqlite',
            'database' => '.hthelloworld.sqlite',
/*
            'type'     => 'Mysql',
            'database' => 'helloworld',
            'host'     => 'localhost',
            'username' => 'hello',
            'password' => '12world34',
*/
            'prefix'   => 'pop_',
            'default'  => true
        ]
    ],
    'forms' => [
        'login' => [
            'username' => [
                'type'       => 'text',
                'label'      => 'Username:',
                'required'   => true,
                'attributes' => ['size' => 40],
                'validators' => 'AlphaNumeric()'
            ],
            'password' => [
                'type'       => 'password',
                'label'      => 'Password:',
                'required'   => true,
                'attributes' => ['size' => 40],
                'validators' => ['NotEmpty()', 'LengthGt(6)']
            ],
            'submit' => [
                'type'       => 'submit',
                'value'      => 'LOGIN'
            ]
        ]
    ],
    'controllers' => [
        '/' => [
            'index'   => 'index.phtml',
            'about'   => 'about.phtml',
            'contact' => 'contact.phtml',
            'error'   => 'error.phtml'
        ]
    ],
    'models' => [
        'User'
    ]
]);
