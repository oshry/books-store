<?php

require realpath(dirname(__FILE__).'/..').'/env.php';
require DOCROOT.'vendor/autoload.php';

define('APP_START_TIME', microtime(TRUE));
define('APP_START_MEMORY', memory_get_usage());

ob_start();
set_exception_handler([ 'Src\Delivery\Exception', 'handler' ]);
set_error_handler([ 'Src\Delivery\Exception', 'error_handler' ]);
register_shutdown_function([ 'Src\Delivery\Exception', 'shutdown_handler' ]);

$config = include APPPATH.'config/database.php';
$app = new Src\AppManager($config);

// Load app-specific routes
$routes = include APPPATH.'config/routes.php';
echo (new Src\Delivery\Request([
		'app' => $app,
		'base_url' => '/srv/http',
		'routes' => $routes,
		'template_dir' => APPPATH.'template'
	]))
	->execute();
