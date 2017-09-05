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
        if($this->imageExist($id, $app['config']['screenshots_folder']))
        {
            if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Twitterbot') === 0 || ($app['debug'] && isset($_GET['twitter']))) // Twitter cards
            {
                $network = 'twitter';
            }
            else if(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit/') === 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Facebot') === 0) || ($app['debug'] && isset($_GET['facebook']))) // Facebook cards
            {
                $network = 'facebook';
            }
            else if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'WhatsApp/') === 0 || ($app['debug'] && isset($_GET['whatsapp']))) // Whatsapp preview
            {
                $network = 'whatsapp';
            }
            else // Normal client
            {
                return $this->screenshot_raw($app, $id);
            }

            $timestamp = filemtime($app['config']['screenshots_folder'] . $this->removeSuffix($id) . self::EXTENSION);
            $date = date("d/m/Y à H:i:s.", $timestamp);
            $id_without = $this->removeSuffix($id);

            return $app['twig']->render('special/image_card.html.twig', array(
                'id' => $id_without,
                'network' => $network,
                'date' => $date,
                'timestamp' => $timestamp
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

            $name_without = $this->removeSuffix($id);
            $path_without = $app['config']['screenshots_folder'] . $name_without;

            if($id !== $name_without) // {id}.png
            {
                if(file_exists($path_without . self::EXTENSION))
                    return $app->redirect($app['url_generator']->generate('screenshot', array('id' => $name_without)), Response::HTTP_MOVED_PERMANENTLY);
                else
                    throw new \DomainException();
            }
            else
            {
                if(file_exists($app['config']['screenshots_folder'] . $id . self::EXTENSION))
                {
                    $path = $app['config']['screenshots_folder'] . $id . self::EXTENSION;

                    return new Response(file_get_contents($path), Response::HTTP_OK, array('Content-Type' => 'image/png', 'Content-length' => filesize($path)));
                }
                else
                    throw new \DomainException();
            }
        }
        catch(\Exception $ex)
        {
            return $this->createDefaultMessage();
        }
    }

    private function createDefaultMessage()
    {
        return new Response('Capture introuvable.', Response::HTTP_NOT_FOUND);
    }

    private function imageExist($id, $folder)
    {
        return basename($id) === $id && file_exists($folder . $this->removeSuffix($id) . self::EXTENSION);
    }

    private function removeSuffix($str)
    {
        return preg_replace('/\.png$/', '', $str);
    }
}