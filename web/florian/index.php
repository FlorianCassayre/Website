<?php

/*
 Routing file, every access passes by this pages which routes to the correct resource.
 */

use FlorianCassayre\Util\MySQLUtils;
use FlorianCassayre\Util\WebsiteType;
use Silex\Application;
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

$app['website'] = WebsiteType::WEBSITE;

$app['debug'] = \FlorianCassayre\Api\HttpUtils::isLocalhost();

// Twig templates folder
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../../templates'));


// MySQL PDO
MySQLUtils::setup($app);

// Globals
if(!$app['debug'])
{
    $app['twig']->addGlobal('basepath', 'https://florian.cassayre.me'); // Production Website
}
else
{
    $app['twig']->addGlobal('basepath', 'http://cassayre'); // Wamp server
}

$app['twig']->addGlobal('version', $app['version']);

$app['root_directory'] = __DIR__ . '/../..';
$app['contents_directory'] = $app['root_directory'] . '/contents/florian';

// Redirections
$app->before('FlorianCassayre\\Florian\\RedirectionMiddleware::handle', Application::EARLY_EVENT);

// == Begin routing ==

$app->mount('/', new FlorianCassayre\Florian\RoutingController());


// Somehow the string callback doesn't work with `error($callback)`; this is a workaround
$app->error(function (\Exception $e, Request $request, $code) use ($app)
{
    return (new FlorianCassayre\Florian\Controllers\ErrorsHandlerController())->handle($app, $e, $request, $code);
});


// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\Logger::log_request');


$app->run(); // Run the Silex application

?>