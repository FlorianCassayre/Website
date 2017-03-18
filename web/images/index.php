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

$app['website'] = WebsiteType::IMAGES;

$app['debug'] = \FlorianCassayre\Api\HttpUtils::isLocalhost();


// Twig templates folder
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../../templates'));


// MySQL PDO
MySQLUtils::setup($app);


// == Begin routing ==

$app->get('/{id}', 'FlorianCassayre\\Images\\ScreenshotsController::screenshot')->bind('screenshot');
$app->get('/{id}/raw', 'FlorianCassayre\\Images\\ScreenshotsController::screenshot_raw')->bind('screenshot.raw');


$app->error(function (\Exception $e, Request $request, $code) use ($app)
{
    if($code == 404 || $code == 405)
        return 'Capture introuvable.';
    else
        if(!$app['debug'])
            return 'Erreur interne';
        else
            return null;
});

// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\Logger::log_request');


$app->run(); // Run the Silex application

?>