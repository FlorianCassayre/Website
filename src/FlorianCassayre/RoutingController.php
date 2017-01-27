<?php

namespace FlorianCassayre;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class RoutingController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'FlorianCassayre\\Controllers\\MainPagesController::homepage')->bind('homepage');

        $controllers->get('/tools/minecraft/uuid', 'FlorianCassayre\\Controllers\\Tools\\MCUUIDController::uuid_home')->bind('tools.minecraft.uuid');
        $controllers->get('/tools/minecraft/uuid/{input}', 'FlorianCassayre\\Controllers\\Tools\\MCUUIDController::uuid')->bind('tools.minecraft.uuid.result');

        $controllers->get('/tools/minecraft/test', 'FlorianCassayre\\Controllers\\Tools\\TestToolController::test')->bind('tools.misc.test');

        $controllers->get('/tools/minecraft/test2', 'FlorianCassayre\\Controllers\\Tools\\TestToolController::test2')->bind('tools.misc.test2');

        $controllers->get('/tools/minecraft/enchanting', 'FlorianCassayre\\Controllers\\Tools\\MCEnchantingController::enchanting_home')->bind('tools.minecraft.enchanting');
        $controllers->get('/tools/minecraft/enchanting/{type}/{material}/{levels}', 'FlorianCassayre\\Controllers\\Tools\\MCEnchantingController::enchanting')->bind('tools.minecraft.enchanting.result');
        $controllers->get('/tools/minecraft/enchanting/{type}/{material}/{levels}/{known_enchantment}/{known_enchantment_level}', 'FlorianCassayre\\Controllers\\Tools\\MCEnchantingController::enchanting_conditional')->bind('tools.minecraft.enchanting.result.conditional');


        $controllers->get('/screenshots/{id}', 'FlorianCassayre\\Controllers\\ScreenshotsController::screenshot')->bind('screenshots');

        return $controllers;
    }
}