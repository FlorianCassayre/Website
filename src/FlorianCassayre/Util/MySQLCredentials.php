<?php

namespace FlorianCassayre\Util;

use Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider;
use Silex\Application;

class MySQLCredentials
{
    const MYSQL_USER = 'root';
    const MYSQL_PASSWORD = '';
    const MYSQL_DB = 'cassayre';

    const MYSQL_ADDRESS = 'localhost'; // Default address (localhost)
    const MYSQL_PORT = 3306; // Default port


    public static function setup(Application $app)
    {
        $app->register(
            new PDOServiceProvider('pdo'),
            array(
                'pdo.server'   => array(
                    'driver'   => 'mysql',
                    'host'     => MySQLCredentials::MYSQL_ADDRESS,
                    'dbname'   => MySQLCredentials::MYSQL_DB,
                    'port'     => MySQLCredentials::MYSQL_PORT,
                    'user'     => MySQLCredentials::MYSQL_USER,
                    'password' => MySQLCredentials::MYSQL_PASSWORD,
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