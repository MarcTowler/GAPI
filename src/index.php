<?php
/**
 * Index of the API
 *
 * @package		API
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2017 Marc Towler
 * @license		https://github.com/Design-Develop-Realize/api/blob/master/LICENSE.md
 * @link		https://api.itslit.uk
 * @since		Version 0.1
 * @filesource
 */
namespace API;
error_reporting(E_ALL);
//error_reporting(0);
include_once('../vendor/autoload.php');

use API\Library;

$timer = new Library\Logger();

$timer->start();

$router = new Library\Router();

$con = '\\API\\Controllers\\' . ucfirst($router->getController());

$controller = new $con();


echo $controller->{$router->getMethod()}();

$timer->end();
