<?php

namespace FlorianCassayre\Florian\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorsHandlerController
{
    public function handle(Application $app, \Exception $e, Request $request, $code)
    {
        switch($code)
        {
            case 404:
            case 403:
                return $app['twig']->render('errors/' . $code . '.html.twig');
            default:
                return $app['twig']->render('errors/error.html.twig');
        }
    }
}