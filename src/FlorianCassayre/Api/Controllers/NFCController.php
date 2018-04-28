<?php

namespace FlorianCassayre\Api\Controllers;

use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class NFCController
{
    const KEY_TEXT_TO_SPEECH = 'tts';

    public function text_to_speech(Application $app)
    {
        $query = $app['pdo']->query('SELECT * FROM nfc');
        $rows = $query->fetchAll(PDO::FETCH_KEY_PAIR);
        $content = isset($rows[self::KEY_TEXT_TO_SPEECH]) ? $rows[self::KEY_TEXT_TO_SPEECH] : '';

        return new Response($content, 200, array('Content-Type' => 'text/plain'));
    }
}