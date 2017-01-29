<?php

namespace FlorianCassayre\Controllers\Tools;

use FlorianCassayre\Api\Minecraft\MinecraftEnchanting;
use FlorianCassayre\Api\Minecraft\MinecraftHeads;
use FlorianCassayre\Api\Minecraft\MinecraftUsernames;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestToolController
{
    public function test(Application $app, Request $request)
    {
        /*


        $stream = function () use ($file) {
       readfile($file);
   };
   return $app->stream($stream, 200, array('Content-Type' => 'image/jpeg'));


         */

        $img = imagecreate(8, 8);

        $str = 'b5ac9fb5ac9fb5ac9f75ac1f75ac1fb5ac9f0d0d9f75ac1f0dac9f13141d1077090e0e0e0eac9f13d6050d77090d0d0d0dac0d0d77090d3c1d0eac0e0e870e0d';

        $data = MinecraftHeads::hex2String($str);
        $i = 0;

        for($y = 0; $y < 8; $y++) {
            for($x = 0; $x < 8; $x++) {
                $r = ord($data[$i]);
                $g = ord($data[$i + 1]);
                $b = ord($data[$i + 2]);

                $color = imagecolorallocate($img, $r, $g, $b);

                imagefilledrectangle($img, $x, $y, $x + 1, $y + 1, $color);

                $i += 3;
            }
        }

        imagepng($img);

       //$response = new Response(imagepng($img), 200, array('Content-Type' => 'image/png'));
        //$response->headers->set();
        return new Response('', 200, array('Content-Type' => 'image/png'));
    }

    public function test2(Application $app, Request $request)
    {
        /*
        $hex = MinecraftHeads::getHeadToHex(MinecraftUsernames::getByName('iamblueslime')->uuid);
        echo $hex;
        */

        $uuid = MinecraftUsernames::getByName('amaurypi')->uuid;
        //$skin = MinecraftHeads::getSkinImage($uuid);
        $v = MinecraftHeads::getHeadAndHatHex($uuid);
        $without = MinecraftHeads::mergeHatToHead($v[0], $v[1]);
        //echo MinecraftHeads::convertHeadOrHatHexToImage($without);

//echo ' --- ';
        //$hat_head = MinecraftHeads::getHeadAndHatHex(MinecraftUsernames::getByName('amaurypi')->uuid);
        //echo MinecraftHeads::getTransparencySquareCropToHex($skin, MinecraftHeads::HAT_X, MinecraftHeads::HAT_Y);
        //$textures = MinecraftHeads::mergeHatToHead($hat_head[0], $hat_head[1], 0);
        //echo $textures;
        //$img = MinecraftHeads::convertHeadOrHatHexToImage($textures[1], 8);
        imagepng(MinecraftHeads::convertHeadOrHatHexToImage($without, 8));
        return new Response('', 200, array('Content-Type' => 'image/png')); ///
    }




    //$app['pdo']->;

    // var_dump(MinecraftHeads::getHeadToHex('9cc7b4033ce847d79d95eb2a03dd78b4'));

    /*return $app['twig']->render('tools/test.html.twig', array(

    ));*/

}