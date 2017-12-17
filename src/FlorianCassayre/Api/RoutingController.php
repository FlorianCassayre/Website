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

        $controllers->post('/task/deploy', 'FlorianCassayre\\Api\\Controllers\\DeployWebsiteController::deploy');

        $controllers->post('/task/mermoz', 'FlorianCassayre\\Api\\Controllers\\MissingTeachersController::scrap');

        $controllers->post('/task/publibike', 'FlorianCassayre\\Api\\Controllers\\PublibikeController::scrap');

        $controllers->get('/minecraft/head/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head');
        $controllers->get('/minecraft/head/{size}/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head');
        $controllers->get('/minecraft/helmet/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head_with_helmet');
        $controllers->get('/minecraft/helmet/{size}/{input}', 'FlorianCassayre\\Api\\Controllers\\MinecraftHeadsController::head_with_helmet');

        $controllers->get('/minecraft/zeps/list', 'FlorianCassayre\\Api\\Controllers\\ZePSController::list_stations');
        $controllers->get('/minecraft/zeps/list/network', 'FlorianCassayre\\Api\\Controllers\\ZePSController::list_stations_with_network');
        $controllers->get('/minecraft/zeps/colors', 'FlorianCassayre\\Api\\Controllers\\ZePSController::colors');
        $controllers->get('/minecraft/zeps/path/{from}/{to}', 'FlorianCassayre\\Api\\Controllers\\ZePSController::path');
        $controllers->get('/minecraft/zeps/version', 'FlorianCassayre\\Api\\Controllers\\ZePSController::version');

        $controllers->get('/mermoz/list', 'FlorianCassayre\\Api\\Controllers\\MissingTeachersController::list_teachers');
        $controllers->get('/mermoz/missing', 'FlorianCassayre\\Api\\Controllers\\MissingTeachersController::list_missing');

        return $controllers;
    }
}