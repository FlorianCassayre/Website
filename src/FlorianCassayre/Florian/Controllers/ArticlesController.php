<?php

namespace FlorianCassayre\Florian\Controllers;


use Silex\Application;

class ArticlesController
{
    private static $articles = array(
        'crypto',
        'js',
        'float',
        'random',
        'youtube',
        'pins',
        'genealogy',
        'publibike'
        /*'genealogy',
        'riddles',
        'scala-java'*/
    );

    public function article(Application $app, $id)
    {
        if(in_array($id, self::$articles))
        {
            return $app['twig']->render('articles/' . $id . '.html.twig', array());
        }
        else
        {
            $app->abort(404);
            return null;
        }
    }
}