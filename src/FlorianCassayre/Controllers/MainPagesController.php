<?php

namespace FlorianCassayre\Controllers;

use Silex\Application;

class MainPagesController
{
    public function homepage(Application $app)
    {
        return $app['twig']->render('homepage.html.twig', array());
    }
}