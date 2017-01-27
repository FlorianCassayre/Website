<?php

namespace FlorianCassayre\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ScreenshotsController
{
    //const SCREENSHOTS_LOCATION = '/home/florian/screenshots/';
    const SCREENSHOTS_LOCATION = 'D:/';
    const EXTENSION = 'png';

    public function screenshot(Application $app, $id)
    {
        try
        {
            if(basename($id) !== $id) // File name not valid
                throw new \InvalidArgumentException();

            $split = explode('.', $id);

            if(count($split) > 1 && $split[count($split) - 1] === self::EXTENSION && file_exists(self::SCREENSHOTS_LOCATION . $id)) // {id}.png
            {
                unset($split[count($split) - 1]);
                $file_without = join('.', $split);
                return $app->redirect($app['url_generator']->generate('screenshots', array('id' => $file_without)), Response::HTTP_MOVED_PERMANENTLY);
            }
            else
            {
                if(file_exists(self::SCREENSHOTS_LOCATION . $id . '.png'))
                {
                    $path = self::SCREENSHOTS_LOCATION . $id . '.png';

                    return new Response(file_get_contents($path), Response::HTTP_OK, array('Content-Type' => 'image/png', 'Content-length' => filesize($path)));
                }
                else
                    throw new \DomainException();
            }
        }
        catch(\Exception $ex)
        {
            return new Response('Capture introuvable.', Response::HTTP_NOT_FOUND);
        }
    }
}