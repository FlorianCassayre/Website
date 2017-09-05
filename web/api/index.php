<?php

/*
 Routing file, every access passes by this pages which routes to the correct resource.
 */

use FlorianCassayre\Util\MySQLUtils;
use FlorianCassayre\Util\WebsiteType;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../../vendor/autoload.php'; // Loading composer libraries (Silex & Twig)

session_start(); // Sessions manager
date_default_timezone_set('Europe/Paris'); // Timezone

$app = new Silex\Application();

$app['config'] = array();
if (file_exists(__DIR__ . '/../../config.php'))
{
    $app['config'] = include(__DIR__ . '/../../config.php');
}

$app['version'] = array();
if (file_exists(__DIR__ . '/../../version.php'))
{
    $app['version'] = include(__DIR__ . '/../../version.php');
}

$app['website'] = WebsiteType::API;

$app['debug'] = \FlorianCassayre\Api\HttpUtils::isLocalhost();


// MySQL PDO
MySQLUtils::setup($app);

$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$app['swiftmailer.options'] = array(
    'host' => 'mx.zoho.eu',
    'port' => '25',
    'username' => 'florian@cassayre.me',
    'password' => 'Fl31361335Ca',
    'encryption' => 'tls',
    'auth_mode' => 'login' // DL5QTjnhse5YuLEr
);

// == Begin routing ==

$app->mount('/', new FlorianCassayre\Api\RoutingController());


$app->error(function (\Exception $e, Request $request, $code) use ($app)
{
    return (new FlorianCassayre\Api\Controllers\ErrorsHandlerController())->handle($app, $e, $request, $code);
});


// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\Logger::log_request');


$app->run(); // Run the Silex application

?>