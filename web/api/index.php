<?php

/*
 Routing file, every access passes by this pages which routes to the correct resource.
 */

use FlorianCassayre\Util\MySQLCredentials;
use FlorianCassayre\Util\WebsiteType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../../vendor/autoload.php'; // Loading composer libraries (Silex & Twig)

session_start(); // Sessions manager
date_default_timezone_set('Europe/Paris'); // Timezone

$app = new Silex\Application();

$app['website'] = WebsiteType::API;

$app['debug'] = \FlorianCassayre\Api\HttpUtils::isLocalhost();


// MySQL PDO
MySQLCredentials::setup($app);

$app->after(
    function (Request $request, Response $response)
    {
        $response->headers->set(
            'Content-Type',
            $response->headers->get('Content-Type') . '; charset=utf-8'
        );

        return $response;
    }
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