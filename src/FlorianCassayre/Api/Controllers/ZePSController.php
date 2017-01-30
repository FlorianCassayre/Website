<?php

namespace FlorianCassayre\Api\Controllers;

use FlorianCassayre\Util\Settings;
use Silex\Application;

class ZePSController
{
    const HASHING_ALGORITHM = 'sha256';

    public function list_stations(Application $app)
    {
        return self::internal_list($app, false);
    }

    public function list_stations_with_network(Application $app)
    {
        return self::internal_list($app, true);
    }

    private function internal_list(Application $app, $with_network)
    {
        $bool = self::str_boolean($with_network);
        return $app->json(json_decode(self::request('list' . ' ' . $bool)));
    }

    public function colors(Application $app)
    {
        return $app->json(json_decode(self::request('colors')));
    }

    public function path(Application $app, $from, $to)
    {
        return self::path_internal($app, $from, $to, true, true);
    }

    public function path_with_parameters(Application $app, $from, $to)
    {
        return self::path_internal($app, $from, $to, isset($_GET['official']), isset($_GET['accessible']));
    }

    private function path_internal(Application $app, $from, $to, $official, $accessible)
    {
        $from_int = intval($from);
        $to_int = intval($to);
        $official_bool = self::str_boolean($official);
        $accessible_bool = self::str_boolean($accessible);

        if(is_numeric($from) && is_numeric($to) && $from_int >= 0 && $to_int >= 0)
        {
            return $app->json(json_decode(self::request('pathfinder' . ' ' . $from_int . ' ' . $to_int . ' ' . $official_bool . ' ' . $accessible_bool)));
        }
        else
        {
            return $app->json((object) array('result' => 'failed', 'time' => 0, 'source' => 'user'));
        }
    }

    public function version(Application $app)
    {
        $hash = hash_file(self::HASHING_ALGORITHM, Settings::ZEPS_JAR_FILE);

        return $app->json((object) array('version' => '1.1-SNAPSHOT', self::HASHING_ALGORITHM => $hash));
    }

    private function str_boolean($bool)
    {
        return $bool ? 'true' : 'false';
    }

    private function request($parameters)
    {
        $locale = 'fr_FR.utf-8';
        setlocale(LC_ALL, $locale);
        putenv('LC_ALL='.$locale);

        if(preg_match('/^[A-z0-9 ]*$/', $parameters))
        {
            $cmd = 'java -jar ' . Settings::ZEPS_JAR_FILE . ' ' . $parameters;

            return utf8_encode(exec($cmd));
        }
        else
        {
            throw new \InvalidArgumentException('Illegal input: ' . $parameters);
        }
    }
}