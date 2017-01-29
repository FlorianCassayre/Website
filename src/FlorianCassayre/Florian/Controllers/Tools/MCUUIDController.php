<?php

namespace FlorianCassayre\Florian\Controllers\Tools;

use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftErrorException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftInvalidInputException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftRateLimitException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownInputException;
use FlorianCassayre\Api\Minecraft\MinecraftUsernames;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MCUUIDController
{
    public function uuid_home(Application $app, Request $request)
    {
        if ($request->query->has('input'))
        {
            $input = $request->query->get('input');
            if ($input)
                return $app->redirect($app['url_generator']->generate('tools.minecraft.uuid.result', array(
                    'input' => $input
                )));
            else
                return $app->redirect($app['url_generator']->generate('tools.minecraft.uuid'));
        }

        return $app['twig']->render('tools/uuid.html.twig', array(
            'is_empty' => true
        ));
    }

    public function uuid(Application $app, Request $request, $input)
    {
        $result = null;
        $error = null;

        try
        {
            $result = MinecraftUsernames::getInformations($input);

            $result->changes = array_reverse($result->changes);
        }
        catch (MinecraftInvalidInputException $ex)
        {
            $error = 'invalid_input';
        }
        catch (MinecraftUnknownInputException $ex)
        {
            $error = 'unknown_input';
        }
        catch (MinecraftRateLimitException $ex)
        {
            $error = 'rate_limit';
        }
        catch (MinecraftErrorException $ex)
        {
            $error = 'error';
        }

        return $app['twig']->render('tools/uuid.html.twig', array(
            'is_empty' => false,
            'input' => $input,
            'success' => $error == null,
            'error' => $error,
            'result' => $result
        ));
    }
}