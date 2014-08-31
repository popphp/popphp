<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pop\Db;

$sql = new Pop\Db\Sql(new Db\Adapter\Mysql([
    'database' => 'phirecms',
    'username' => 'phire',
    'password' => '12cms34'
]), 'users');

$rg = new Db\Row\Gateway($sql, 'id');
//$rg->find(4);
//$rg->delete();
//print_r($rg);