#!/usr/bin/php
<?php
/**
 * Pop PHP Framework PHP CLI script (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Cli
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 *
 * Possible arguments
 *
 * ./pop check             Check the current configuration for required dependencies
 * ./pop help              Display this help
 * ./pop build file.php    Build an application based on the build file specified
 * ./pop show              Show project install instructions
 * ./pop version           Display version of Pop PHP Framework
 */

set_time_limit(0);

require_once __DIR__  . '/../vendor/autoload.php';

$cli = new \Pop\Cli($argv);
