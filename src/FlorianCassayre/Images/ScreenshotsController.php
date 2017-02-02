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
        if(self::imageExist($id))
        {
            if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Twitterbot') === 0 || ($app['debug'] && isset($_GET['twitter']))) // Twitter cards
            {
                $network = 'twitter';
            }
            else if(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit/') === 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Facebot') === 0) || ($app['debug'] && isset($_GET['facebook']))) // Facebook cards
            {
                $network = 'facebook';
            }
            else // Normal client
            {
                return self::screenshot_raw($app, $id);
            }

            $date = date("d/m/Y à H:i:s.", filemtime(Settings::SCREENSHOTS_FOLDER . self::removeSuffix($id) . self::EXTENSION));
            $id_without = self::removeSuffix($id);

            return $app['twig']->render('special/image_card.html.twig', array(
                'id' => $id_without,
                'network' => $network,
                'date' => $date
            ));
        }
        else
        {
            return self::createDefaultMessage();
        }
    }

    public function screenshot_raw(Application $app, $id)
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
                    return $app->redirect($app['url_generator']->generate('screenshot', array('id' => $name_without)), Response::HTTP_MOVED_PERMANENTLY);
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
            return self::createDefaultMessage();
        }
    }

    private function createDefaultMessage()
    {
        return new Response('Capture introuvable.', Response::HTTP_NOT_FOUND);
    }

    private function imageExist($id)
    {
        return basename($id) === $id && file_exists(Settings::SCREENSHOTS_FOLDER . self::removeSuffix($id) . self::EXTENSION);
    }

    private function removeSuffix($str)
    {
        return preg_replace('/\.png$/', '', $str);
    }
}