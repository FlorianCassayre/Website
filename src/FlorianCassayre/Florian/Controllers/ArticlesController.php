<?php

namespace FlorianCassayre\Florian\Controllers;

use PDO;
use Silex\Application;

class ArticlesController
{
    public function articles(Application $app)
    {
        $sql = 'SELECT * FROM articles ORDER BY timestamp DESC';

        $sth = $app['pdo']->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $articles = array();

        foreach($results as $row)
        {
            $obj = (object) array(
                'date_published' => $row['timestamp'],
                'date_modified' => $row['last_edit'],
                'title' => $row['title'],
                'content' => $row['content']
            );

            array_push($articles, $obj);
        }


        return $app['twig']->render('general/articles.html.twig', array(
            'articles' => $articles
        ));
    }
}