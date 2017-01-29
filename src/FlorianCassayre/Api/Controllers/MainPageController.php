<?php

namespace FlorianCassayre\Api\Controllers;

use Silex\Application;

class MainPageController
{
    public function homepage(Application $app)
    {
        return $app->json((object) array(
            'title' => 'Florian Cassayre\'s API',
            'version' => '1.0',
            'documentation' => 'http://florian.cassayre.me/api'
        ));
    }
}