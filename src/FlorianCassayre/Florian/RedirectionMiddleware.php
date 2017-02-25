<?php

namespace FlorianCassayre\Florian;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class RedirectionMiddleware
{
    private static $redirections = array(

        // Short URLs
        '/uuid' => '/tools/minecraft/uuid',
        '/enchanting' => '/tools/minecraft/enchanting',
        '/ping' => '/tools/minecraft/ping',
        '/projects' => '/realisations',

        // Old URLs
        '/netherrail' => 'https://zeps.carrade.eu',

    );

    public static function handle(Request $request, Application $app)
    {
        // Props to @AmauryCarrade
        if (array_key_exists($request->getPathInfo(), self::$redirections))
        {
            $query_string = $request->getQueryString();
            return $app->redirect($request->getBaseUrl() . self::$redirections[$request->getPathInfo()] . ($query_string != null ? '?' . $query_string : ''), 301);
        }

        return null;
    }
}