<?php

namespace FlorianCassayre\Util;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessLogger
{
    public static function log_request(Request $request, Response $response, Application $app)
    {
        if(isset($app['website']) && !empty($app['website']))
            $site = $app['website'];
        else
            $site = '?';

        $method = $_SERVER['REQUEST_METHOD'];
        $url = $request->getPathInfo();
        $code = $response->getStatusCode();
        $session = session_id();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if(!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $sql = 'INSERT INTO log_access (site, method, url, code, ipv4, session, user_agent) VALUES (:site, :method, :url, :code, :ipv4, :session, :user_agent)';

        $sth = $app['pdo']->prepare($sql);
        $sth->bindParam(':site', $site);
        $sth->bindParam(':method', $method);
        $sth->bindParam(':url', $url);
        $sth->bindParam(':code', $code);
        $sth->bindParam(':ipv4', $ip);
        $sth->bindParam(':session', $session);
        $sth->bindParam(':user_agent', $user_agent);
        $sth->execute();
    }
}