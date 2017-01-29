<?php

namespace FlorianCassayre\Api;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class RoutingController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'FlorianCassayre\\Api\\Controllers\\MainPageController::homepage');

        return $controllers;
    }
}