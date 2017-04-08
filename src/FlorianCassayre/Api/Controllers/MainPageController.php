<?php

namespace FlorianCassayre\Api\Controllers;

use Silex\Application;

class MainPageController
{
    public function homepage(Application $app)
    {
        return $app->json((object) array(
            'title' => 'Florian Cassayre\'s API',
            'version' => $app['version']['commit'],
            'updated' => $app['version']['date'],
            'documentation' => 'https://florian.cassayre.me/api'
        ));
    }
}