<?php

namespace FlorianCassayre\Florian\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ZcraftTrackerController
{
    private static $images = array(
        'v6.png',
        'v6_nether.png',
        'v6_end.png'
    );

    public function tracker_home(Application $app)
    {
        return $app['twig']->render('tools/ztracker.html.twig');
    }

    public function tracker_image(Application $app, $name)
    {
        if(in_array($name, self::$images)) {
            assert(preg_match("/[a-z0-9_]+\\.png/", $name));

            $path = $app['config']['ztracker_folder'] . $name;

            if(file_exists($path)) {
                return new Response(file_get_contents($path), Response::HTTP_OK, array('Content-Type' => 'image/png', 'Content-length' => filesize($path)));
            } else {
                $app->abort(404);
                return null;
            }
        } else {
            $app->abort(404);
            return null;
        }
    }

}