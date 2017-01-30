<?php

namespace FlorianCassayre\Api\Controllers;

use FlorianCassayre\Util\Logger;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ErrorsHandlerController
{
    public function handle(Application $app, \Exception $e, Request $request, $code)
    {
        switch($code)
        {
            case 404:
                return $app->json((object) array('error' => 'route_not_found'));
            default:
            {
                Logger::log_error($app, $e, $request, $code);

                if(!$app['debug'])
                    return $app->json((object) array('error' => 'internal_error'));
                else
                    return null;
            }
        }
    }
}