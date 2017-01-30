<?php

namespace FlorianCassayre\Florian\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class NotYetAvailableController
{
    public function not_yet(Application $app)
    {
        return new Response($app['twig']->render('general/not_yet.html.twig'), Response::HTTP_NOT_FOUND);
    }
}