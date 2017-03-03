<?php

namespace FlorianCassayre\Florian\Controllers;

use FlorianCassayre\Util\Logger;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ErrorsHandlerController
{
    public function handle(Application $app, \Exception $e, Request $request, $code)
    {
        switch($code)
        {
            case 403:
            case 404:
            case 405:
                return $app['twig']->render('errors/' . $code . '.html.twig');
            default:
            {
                Logger::log_error($app, $e, $request, $code);

                if(!$app['debug'])
                    return $app['twig']->render('errors/error.html.twig');
                else
                    return null;
            }

        }
    }
}