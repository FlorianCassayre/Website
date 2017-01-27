<?php

namespace FlorianCassayre;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorsHandlerController
{
    public function handle(\Exception $e, Application $app, Request $request, $code)
    {
        // TODO
        $args = array(
            'http_code' => $code,
            'error_title' => Response::$statusTexts[$code]
        );
        switch ($code)
        {
            case 404:
            case 418:
                return $app['twig']->render('errors/' . $code . '.html.twig', $args);
            default:
                return $app['twig']->render('errors/error.html.twig', $args);
        }
    }
}