<?php

namespace FlorianCassayre\Florian;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class RoutingController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::homepage')->bind('homepage');

        $controllers->get('/donation', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::donation')->bind('donation');

        $controllers->get('/contact', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::contact')->bind('contact');
        $controllers->post('/contact', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::contact_submit')->bind('contact.submit');

        $controllers->get('/articles', 'FlorianCassayre\\Florian\\Controllers\\NotYetAvailableController::not_yet')->bind('articles'); // TODO

        $controllers->get('/realisations', 'FlorianCassayre\\Florian\\Controllers\\NotYetAvailableController::not_yet')->bind('projects'); // TODO

        $controllers->get('/api', 'FlorianCassayre\\Florian\\Controllers\\NotYetAvailableController::not_yet')->bind('api'); // TODO

        $controllers->get('/tools/minecraft/uuid', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCUUIDController::uuid_home')->bind('tools.minecraft.uuid');
        $controllers->get('/tools/minecraft/uuid/{input}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCUUIDController::uuid')->bind('tools.minecraft.uuid.result');

        $controllers->get('/tools/minecraft/enchanting', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_home')->bind('tools.minecraft.enchanting');
        $controllers->get('/tools/minecraft/enchanting/{type}/{material}/{levels}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting')->bind('tools.minecraft.enchanting.result');
        $controllers->get('/tools/minecraft/enchanting/{type}/{material}/{levels}/{known_enchantment}/{known_enchantment_level}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_conditional')->bind('tools.minecraft.enchanting.result.conditional');

        $controllers->get('/tools/minecraft/ping', 'FlorianCassayre\\Florian\\Controllers\\NotYetAvailableController::not_yet')->bind('tools.minecraft.ping'); // TODO

        return $controllers;
    }
}