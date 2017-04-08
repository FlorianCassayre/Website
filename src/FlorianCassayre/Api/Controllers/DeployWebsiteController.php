<?php

namespace FlorianCassayre\Api\Controllers;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DeployWebsiteController
{
    public function deploy(Application $app, Request $request)
    {
        $hookSecret = $app['config']['pull_secret'];

        $rawPost = null;
        if($hookSecret !== null)
        {
            if(!isset($_SERVER['HTTP_X_HUB_SIGNATURE']))
            {
                return $app->json((object) array('error' => 'signature_header_missing'), 400);
            } elseif(!extension_loaded('hash'))
            {
                return $app->json((object) array('error' => 'extension_missing'), 400);
            }

            list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');

            if(!in_array($algo, hash_algos(), true))
            {
                return $app->json((object) array('error' => 'unsupported_hash_algorithm'), 400);
            }

            $rawPost = file_get_contents('php://input');

            if($hash !== hash_hmac($algo, $rawPost, $hookSecret))
            {
                return $app->json((object) array('error' => 'secret_not_match'), 400);
            }
        };

        if(!isset($_SERVER['HTTP_CONTENT_TYPE']))
        {
            return $app->json((object) array('error' => 'content_type_header_missing'), 400);
        } elseif(!isset($_SERVER['HTTP_X_GITHUB_EVENT']))
        {
            return $app->json((object) array('error' => 'event_header_missing'), 400);
        }
        switch($_SERVER['HTTP_CONTENT_TYPE'])
        {
            case 'application/json':
                $json = $rawPost ?: file_get_contents('php://input');
                break;
            case 'application/x-www-form-urlencoded':
                $json = $_POST['payload'];
                break;
            default:
                return $app->json((object) array('error' => 'unsupported_content_type'), 400);
        }

        // $payload = json_decode($json);

        $commands = array(
            'cd ' . $app['config']['pull_folder'],
            'git pull'
        );

        foreach($commands as $command)
        {
            shell_exec($command);
        }

        return $app->json((object) array('result' => 'ok'));
    }
}