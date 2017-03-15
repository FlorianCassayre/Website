<?php

namespace FlorianCassayre\Api\Controllers;

use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftRateLimitException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownInputException;
use FlorianCassayre\Api\Minecraft\MinecraftHeads;
use FlorianCassayre\Api\Minecraft\MinecraftUsernames;
use FlorianCassayre\Api\Minecraft\MinecraftUtils;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class MinecraftHeadsController
{
    const ERROR_INPUT = 'illegal_input';
    const ERROR_NOT_FOUND = 'not_found';
    const ERROR_RATE_LIMIT = 'mojang_rate_limit';
    const ERROR_INTERNAL = 'error_internal';

    const DEFAULT_UUID = '8667ba71b85a4004af54457a9734eed7';

    const DEFAULT_SIZE = 8;

    public function head(Application $app, $input, $size = self::DEFAULT_SIZE)
    {
        return $this->create_response($app, $input, false, $size);
    }

    public function head_with_helmet(Application $app, $input, $size = self::DEFAULT_SIZE)
    {
        return $this->create_response($app, $input, true, $size);
    }

    public function create_response(Application $app, $input, $with_hat, $size)
    {
        $size = intval($size);

        if($size < 1 || $size > 128)
            return $app->json((object) array('error' => self::ERROR_INPUT), 400);

        try
        {
            $input = $this->removeSuffix($input);

            if(MinecraftUtils::isUUID($input))
                $uuid_without = MinecraftUtils::normalize($input);
            elseif(MinecraftUtils::isMinecraftName($input))
                $uuid_without = MinecraftUsernames::getByName($input)->uuid;
            else
                return $app->json((object)array('error' => self::ERROR_INPUT));

            $sth = $app['pdo']->prepare("SELECT HEX(layer_head) AS 'layer_head', HEX(layer_hat) AS 'layer_hat' FROM minecraft_heads WHERE uuid = UNHEX(:uuid) LIMIT 1");
            $sth->bindParam(':uuid', $uuid_without);
            $sth->execute();
            $row = $sth->fetch();

            if($row != false) // The texture is already cached, using it
            {
                $array = array(strtolower($row['layer_head']), strtolower($row['layer_hat']));
            }
            else // Texture not yet cached, caching it right away
            {
                $array = MinecraftHeads::getHeadAndHatHex($uuid_without);

                if($array == null)
                {
                    $array = MinecraftHeads::getHeadAndHatHex(self::DEFAULT_UUID);
                }

                $sql = 'INSERT INTO minecraft_heads (uuid, layer_head, layer_hat) VALUES (UNHEX(:uuid), UNHEX(:layer_head), UNHEX(:layer_hat))';

                $sth = $app['pdo']->prepare($sql);
                $sth->bindParam(':uuid', $uuid_without);
                $sth->bindParam(':layer_head', $array[0]);
                $sth->bindParam(':layer_hat', $array[1]);
                $sth->execute();
            }

            if($with_hat)
                $hex = MinecraftHeads::mergeHatToHead($array[0], $array[1]);
            else
                $hex = $array[0];

            return $this->responseImage(MinecraftHeads::convertHeadOrHatHexToImage($hex, $size));
        }
        catch(MinecraftUnknownInputException $e)
        {
            return $app->json((object) array('error' => self::ERROR_NOT_FOUND), 404);
        }
        catch(MinecraftRateLimitException $e)
        {
            return $app->json((object) array('error' => self::ERROR_RATE_LIMIT), 503);
        }
        catch(\Exception $e)
        {
            return $app->json((object) array('error' => self::ERROR_INTERNAL), 500);
        }
    }

    private function responseImage($image)
    {
        imagepng($image);
        return new Response('', 200, array('Content-Type' => 'image/png'));
    }

    private function removeSuffix($str)
    {
        return preg_replace('/\.png$/', '', $str);
    }
}