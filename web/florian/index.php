<?php

/*
 Routing file, every access passes by this pages which routes to the correct resource.
 */

use FlorianCassayre\Util\MySQLCredentials;
use FlorianCassayre\Util\WebsiteType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../vendor/autoload.php'; // Loading composer libraries (Silex & Twig)

session_start(); // Sessions manager
date_default_timezone_set('Europe/Paris'); // Timezone

$app = new Silex\Application();

$app['website'] = WebsiteType::WEBSITE;

$app['debug'] = FlorianCassayre\Util\Settings::DEBUG;

// Twig templates folder
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../../templates'));


// MySQL PDO
MySQLCredentials::setup($app);



// Globals
if(!$app['debug'])
{
    $app['twig']->addGlobal('basepath', 'http://florian.cassayre.me'); // Production Website
}
else
{
    $app['twig']->addGlobal('basepath', 'http://cassayre'); // Wamp server
}


// == Begin routing ==

$app->mount('/', new FlorianCassayre\Florian\RoutingController());


if(!$app['debug'])
{
    // Somehow the string callback doesn't work with `error($callback)`; this is a workaround
    $app->error(function (\Exception $e, Request $request, $code) use ($app)
    {
        return (new FlorianCassayre\Florian\Controllers\ErrorsHandlerController())->handle($app, $e, $request, $code);
    });
}

// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\AccessLogger::log_request');


$app->run(); // Run the Silex application

?>