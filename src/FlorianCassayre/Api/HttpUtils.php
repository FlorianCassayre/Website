<?php

namespace FlorianCassayre\Api;

class HttpUtils
{
    /**
     * @param $url
     * @return object
     */
    public static function requestHttp($url)
    {
        $curl = curl_init(); // Initialization

        curl_setopt_array($curl, array( // Set all the parameters
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 5 // Timeout set to 5 seconds
        ));

        $content = curl_exec($curl); // Gets the content

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl); // Close request to clear up some resources

        return (object) array('success' => !($content === false), 'content' => $content, 'code' => $code);
    }

    /**
     * @return bool
     */
    public static function isLocalhost()
    {
        return isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
    }
}