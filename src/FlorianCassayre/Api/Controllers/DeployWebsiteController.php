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

        $commands = array(
            'cd ' . $app['config']['pull_folder'],
            'git pull'
        );

        $outputs = array();

        foreach($commands as $command)
        {
            $out = [];
            exec($command . ' 2>&1', $out);
            array_push($outputs, $out);
        }

        return $app->json((object) array('result' => 'ok', 'outputs' => $outputs));
    }
}