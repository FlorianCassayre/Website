<?php

/*
 Routing file, every access passes by this pages which routes to the correct resource.
 */

use Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider;
use FlorianCassayre\Util\MySQLCredentials;
use FlorianCassayre\Util\WebsiteType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../vendor/autoload.php'; // Loading composer libraries (Silex & Twig)

session_start(); // Sessions manager
date_default_timezone_set('Europe/Paris'); // Timezone

$app = new Silex\Application();

$app['website'] = WebsiteType::API;

$app['debug'] = FlorianCassayre\Util\Settings::DEBUG;


// MySQL PDO
$app->register(
    new PDOServiceProvider('pdo'),
    array(
        'pdo.server'   => array(
            'driver'   => 'mysql',
            'host'     => MySQLCredentials::MYSQL_ADDRESS,
            'dbname'   => MySQLCredentials::MYSQL_DB,
            'port'     => MySQLCredentials::MYSQL_PORT,
            'user'     => MySQLCredentials::MYSQL_USER,
            'password' => MySQLCredentials::MYSQL_PASSWORD,
        ),
        'pdo.options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
        ),
        'pdo.attributes' => array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ),
    )
);


// == Begin routing ==

$app->mount('/', new FlorianCassayre\Api\RoutingController());

if(!$app['debug'])
{
    $app->error(function (\Exception $e, Request $request, $code) use ($app)
    {
        return (new FlorianCassayre\Api\Controllers\ErrorsHandlerController())->handle($app, $e, $request, $code);
    });
}

// == End routing ==


// Logs
$app->after('FlorianCassayre\\Util\\AccessLogger::log_request');


$app->run(); // Run the Silex application

?>