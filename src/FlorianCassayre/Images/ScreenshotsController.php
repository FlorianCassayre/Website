<?php

namespace FlorianCassayre\Images;

use FlorianCassayre\Util\Settings;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ScreenshotsController
{
    const EXTENSION = '.png';

    public function screenshot(Application $app, $id)
    {
        try
        {
            if(basename($id) !== $id) // File name not valid
                throw new \InvalidArgumentException();

            $name_without = self::removeSuffix($id);
            $path_without = Settings::SCREENSHOTS_FOLDER . $name_without;

            if($id !== $name_without) // {id}.png
            {
                if(file_exists($path_without . self::EXTENSION))
                    return $app->redirect($app['url_generator']->generate('screenshots', array('id' => $name_without)), Response::HTTP_MOVED_PERMANENTLY);
                else
                    throw new \DomainException();
            }
            else
            {
                if(file_exists(Settings::SCREENSHOTS_FOLDER . $id . self::EXTENSION))
                {
                    $path = Settings::SCREENSHOTS_FOLDER . $id . self::EXTENSION;

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

    private function removeSuffix($str)
    {
        return preg_replace('/\.png$/', '', $str);
    }
}