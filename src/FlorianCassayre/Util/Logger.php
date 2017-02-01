<?php

namespace FlorianCassayre\Util;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Logger
{
    public static function log_request(Request $request, Response $response, Application $app)
    {
        if(isset($app['website']) && !empty($app['website']))
            $site = $app['website'];
        else
            $site = '?';

        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $code = $response->getStatusCode();
        $session = session_id();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

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
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
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

    public static function log_error(Application $app, \Exception $exception, Request $request, $code)
    {
        if(isset($app['website']) && !empty($app['website']))
            $site = $app['website'];
        else
            $site = '?';

        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $stacktrace = self::jTraceEx($exception);

        $sql = 'INSERT INTO log_errors (site, method, url, code, stacktrace) VALUES (:site, :method, :url, :code, :stacktrace)';

        $sth = $app['pdo']->prepare($sql);
        $sth->bindParam(':site', $site);
        $sth->bindParam(':method', $method);
        $sth->bindParam(':url', $url);
        $sth->bindParam(':code', $code);
        $sth->bindParam(':stacktrace', $stacktrace);
        $sth->execute();
    }

    /**
     * jTraceEx() - provide a Java style exception trace
     * @param $e \Exception ff
     * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return array of strings, one entry per trace line
     */
    private static function jTraceEx($e, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if(!$seen)
            $seen = array();
        $trace = $e->getTrace();
        $prev = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while(true)
        {
            $current = "$file:$line";
            if(is_array($seen) && in_array($current, $seen))
            {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)', count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '', count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '', count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)', $line === null ? $file : basename($file), $line === null ? '' : ':', $line === null ? '' : $line);
            if(is_array($seen))
                $seen[] = "$file:$line";
            if(!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if($prev)
            $result .= "\n" . self::jTraceEx($prev, $seen);

        return $result;
    }
}