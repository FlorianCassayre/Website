<?php

namespace FlorianCassayre\Util;

use Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider;
use Silex\Application;

class MySQLUtils
{
    public static function setup(Application $app)
    {
        $app->register(
            new PDOServiceProvider('pdo'),
            array(
                'pdo.server'   => array(
                    'driver'   => 'mysql',
                    'host'     => $app['config']['mysql']['host'],
                    'dbname'   => $app['config']['mysql']['database'],
                    'port'     => $app['config']['mysql']['port'],
                    'user'     => $app['config']['mysql']['user'],
                    'password' => $app['config']['mysql']['password'],
                ),
                'pdo.options' => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
                ),
                'pdo.attributes' => array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ),
            )
        );
    }
}