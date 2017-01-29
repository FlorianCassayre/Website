<?php

namespace FlorianCassayre\Api\Controllers;

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
                return $app->json((object) array('error' => 'internal_error'));
        }
    }
}