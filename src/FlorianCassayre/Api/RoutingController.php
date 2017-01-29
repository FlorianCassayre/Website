<?php

namespace FlorianCassayre\Api;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class RoutingController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('', 'FlorianCassayre\\Api\\Controllers\\MainPageController::homepage');

        $controllers->get('/minecraft/head/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head');
        $controllers->get('/minecraft/head/{size}/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head');
        $controllers->get('/minecraft/helmet/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head_with_helmet');
        $controllers->get('/minecraft/helmet/{size}/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head_with_helmet');

        return $controllers;
    }
}