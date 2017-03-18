<?php

namespace FlorianCassayre\Api\Controllers;

use FlorianCassayre\Util\Settings;
use Silex\Application;

class ZePSController
{
    const HASHING_ALGORITHM = 'sha256';

    public function list_stations(Application $app)
    {
        return $this->internal_list($app, false);
    }

    public function list_stations_with_network(Application $app)
    {
        return $this->internal_list($app, true);
    }

    private function internal_list(Application $app, $with_network)
    {
        $bool = $this->str_boolean($with_network);
        return $app->json(json_decode($this->request($app, 'list' . ' ' . $bool)));
    }

    public function colors(Application $app)
    {
        return $app->json(json_decode($this->request($app, 'colors')));
    }

    public function path(Application $app, $from, $to)
    {
        $from_int = intval($from);
        $to_int = intval($to);
        $official_bool = $this->str_boolean(isset($_GET['official']));
        $accessible_bool = $this->str_boolean(isset($_GET['accessible']));

        if(is_numeric($from) && is_numeric($to) && $from_int >= 0 && $to_int >= 0)
        {
            return $app->json(json_decode($this->request($app, 'pathfinder' . ' ' . $from_int . ' ' . $to_int . ' ' . $official_bool . ' ' . $accessible_bool)));
        }
        else
        {
            return $app->json((object) array('result' => 'failed', 'time' => 0, 'source' => 'user'));
        }
    }

    public function version(Application $app)
    {
        $hash = hash_file(self::HASHING_ALGORITHM, $app['config']['zeps_jar']);

        return $app->json((object) array('version' => '1.1-SNAPSHOT', self::HASHING_ALGORITHM => $hash));
    }

    private function str_boolean($bool)
    {
        return $bool ? 'true' : 'false';
    }

    private function request(Application $app, $parameters)
    {
        $locale = 'fr_FR.utf-8';
        setlocale(LC_ALL, $locale);
        putenv('LC_ALL=' . $locale);

        if(preg_match('/^[A-z0-9 ]*$/', $parameters))
        {
            $cmd = 'java -jar ' . $app['config']['zeps_jar'] . ' ' . $parameters;

            if(!$app['debug'])
                return exec($cmd);
            else
                return utf8_encode(exec($cmd));
        }
        else
        {
            throw new \InvalidArgumentException('Illegal input: ' . $parameters);
        }
    }
}