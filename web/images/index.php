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

$app['website'] = WebsiteType::IMAGES;

$app['debug'] = FlorianCassayre\Util\Settings::DEBUG;


// MySQL PDO
MySQLCredentials::setup($app);


// == Begin routing ==

$app->get('/{id}', 'FlorianCassayre\\Images\\ScreenshotsController::screenshot')->bind('screenshots');

if(!$app['debug'])
{
    $app->error(function (\Exception $e, Request $request, $code) use ($app)
    {
        if($code == 404)
            return 'Capture introuvable.';
        else
            return 'Erreur interne';
    });
}

// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\AccessLogger::log_request');


$app->run(); // Run the Silex application

?>