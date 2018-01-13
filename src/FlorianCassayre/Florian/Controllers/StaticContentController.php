<?php

namespace FlorianCassayre\Florian\Controllers;


use Silex\Application;

class StaticContentController
{
    public function ktz6(Application $app)
    {
        return $app['twig']->render('static/ktz6.html.twig', array());
    }

    public function comptebon(Application $app)
    {
        return$app['twig']->render('static/comptebon.html.twig', array());
    }
}