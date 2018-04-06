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

        $controllers->get('/realisations', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::projects')->bind('projects');

        $controllers->get('/api', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::api')->bind('api');

        $controllers->get('/brand', 'FlorianCassayre\\Florian\\Controllers\\MainPagesController::brand')->bind('brand');

        $controllers->get('/articles/{id}', 'FlorianCassayre\\Florian\\Controllers\\ArticlesController::article')->bind('articles');

        $controllers->get('/tools/minecraft/uuid', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCUUIDController::uuid_home')->bind('tools.minecraft.uuid');
        $controllers->get('/tools/minecraft/uuid/{input}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCUUIDController::uuid')->bind('tools.minecraft.uuid.result');

        $controllers->get('/tools/minecraft/enchanting', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_home')->bind('tools.minecraft.enchanting');
        $controllers->get('/tools/minecraft/enchanting/combinations', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_combinations_home')->bind('tools.minecraft.enchanting.combinations');
        $controllers->get('/tools/minecraft/enchanting/combinations/{type}/{material}/{levels}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_combinations')->bind('tools.minecraft.enchanting.combinations.result');
        $controllers->get('/tools/minecraft/enchanting/combinations/{type}/{material}/{levels}/{known_enchantment}/{known_enchantment_level}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_combinations_conditional')->bind('tools.minecraft.enchanting.combinations.result.conditional');
        $controllers->get('/tools/minecraft/enchanting/constraints', 'FlorianCassayre\\Florian\\Controllers\\Tools\\MCEnchantingController::enchanting_constraints')->bind('tools.minecraft.enchanting.constraints');

        //$controllers->get('/tools/baccalaureat', 'FlorianCassayre\\Florian\\Controllers\\Tools\\BaccalaureatCalculatorController::baccalaureat')->bind('tools.baccalaureat');

        $controllers->get('/tools/minecraft/zcraft/tracker', 'FlorianCassayre\\Florian\\Controllers\\Tools\\ZcraftTrackerController::tracker_home')->bind('tools.minecraft.zcraft.tracker');
        $controllers->get('/tools/minecraft/zcraft/tracker/image/{name}', 'FlorianCassayre\\Florian\\Controllers\\Tools\\ZcraftTrackerController::tracker_image')->bind('tools.minecraft.zcraft.tracker.image');

        $controllers->get('/tools/minecraft/ping', 'FlorianCassayre\\Florian\\Controllers\\NotYetAvailableController::not_yet')->bind('tools.minecraft.ping'); // TODO

        $controllers->get('/ktz/6', 'FlorianCassayre\\Florian\\Controllers\\StaticContentController::ktz6')->bind('ktz.6');

        $controllers->get('/comptebon', 'FlorianCassayre\\Florian\\Controllers\\StaticContentController::comptebon')->bind('comptebon');

        return $controllers;
    }
}