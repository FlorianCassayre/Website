<?php

namespace FlorianCassayre\Florian\Controllers\Tools;


use Silex\Application;

class BaccalaureatCalculatorController
{
    public function baccalaureat(Application $app)
    {
        return $app['twig']->render('tools/baccalaureat.html.twig');
    }
}