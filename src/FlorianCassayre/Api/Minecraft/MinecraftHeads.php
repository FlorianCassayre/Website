<?php

namespace FlorianCassayre\Api\Minecraft;

use FlorianCassayre\Api\HttpUtils;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftErrorException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftInvalidInputException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftRateLimitException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownUUIDException;

class MinecraftHeads
{
    const API_URL = 'https://sessionserver.mojang.com/session/minecraft/profile/';
    const SIZE = 8;

    const HEAD_X = 1;
    const HEAD_Y = 1;

    const HAT_X = 5;
    const HAT_Y = 1;

    const HEX = '0123456789abcdef';

    /**
     * @param $uuid
     * @return resource
     * @throws MinecraftErrorException
     * @throws MinecraftInvalidInputException
     * @throws MinecraftRateLimitException
     */
    public static function getSkinImage($uuid)
    {
        if(!MinecraftUtils::isUUID($uuid))
            throw new MinecraftInvalidInputException();

        $safe_uuid = MinecraftUtils::normalize($uuid);

        $result = HttpUtils::requestHttp(self::API_URL . $safe_uuid);

        if(!$result->success) // If the request failed (ie. Mojang servers are down)
        {
            throw new MinecraftErrorException();
        }

        switch($result->code) // Catch exceptions from http code
        {
            case 204: // Empty body
                throw new MinecraftInvalidInputException(); // The UUID might be correct, we don't know
                break;
            case 429: // Too many requests
                throw new MinecraftRateLimitException();
                break;
        }

        $obj = json_decode($result->content);

        $object = json_decode(base64_decode($obj->properties[0]->value));

        if(!isset($object->textures->SKIN)) // Might happen if the player doesn't have a skin
        {
            return null;
        }

        $textures_url = $object->textures->SKIN->url;

        $skin_result = HttpUtils::requestHttp($textures_url);

        if(!$skin_result->success) // Skin servers down
        {
            throw new MinecraftErrorException();
        }

        $skin_image_raw = $skin_result->content;

        return imagecreatefromstring($skin_image_raw);
    }

    /**
     * @param $skin
     * @param $start_x
     * @param $start_y
     * @return string
     */
    public static function getSquareCropToHex($skin, $start_x, $start_y)
    {
        $bytes = '';

        for($y = 0; $y < self::SIZE; $y++) {
            for($x = 0; $x < self::SIZE; $x++) {
                $rgb = imagecolorat($skin, $x + $start_x * self::SIZE, $y + $start_y * self::SIZE);
                $r = ($rgb >> 16) & 0xff;
                $g = ($rgb >> 8) & 0xff;
                $b = $rgb & 0xff;

                $bytes .= self::byteToHex($r);
                $bytes .= self::byteToHex($g);
                $bytes .= self::byteToHex($b);
            }
        }

        return $bytes;
    }

    public static function getTransparencySquareCropToHex($skin, $start_x, $start_y)
    {
        $bytes = '';

        for($y = 0; $y < self::SIZE; $y++) {
            $temp = 0;
            for($x = 0; $x < self::SIZE; $x++) {
                $argb = imagecolorat($skin, $x + $start_x * self::SIZE, $y + $start_y * self::SIZE);
                $a = ($argb >> 24) & 0xff;

                $temp |= (($a == 0xff ? 1 : 0) << $x);
            }
            $bytes .= self::byteToHex($temp);
            echo self::byteToHex($temp) . '-';
        }

        return $bytes;
    }

    public static function mergeHatToHead($head_hex, $hat_hex)
    {
        $bytes = '';
        $i = 0;

        for($y = 0; $y < self::SIZE; $y++) {
            for($x = 0; $x < self::SIZE; $x++) {
                $flag = substr($hat_hex, $i * 2 * 3, 2 * 3) === '000000';

                if($flag)
                    $texture = $head_hex;
                else
                    $texture = $hat_hex;

                $bytes .= substr($texture, $i * 2 * 3, 2 * 3);

                $i++;
            }
        }

        return $bytes;
    }

    public static function getHeadAndHatHex($uuid)
    {
        $skin = self::getSkinImage($uuid);

        if($skin == null)
            return null;

        $head_hex = self::getSquareCropToHex($skin, self::HEAD_X, self::HEAD_Y);
        $hat_hex = self::getSquareCropToHex($skin, self::HAT_X, self::HAT_Y);

        return array($head_hex, $hat_hex);
    }

    public static function convertHeadOrHatHexToImage($hex, $size = 1)
    {
        $img = imagecreate(self::SIZE * $size, self::SIZE * $size);

        $data = MinecraftHeads::hex2String($hex);
        $i = 0;

        for($y = 0; $y < 8; $y++) {
            for($x = 0; $x < 8; $x++) {
                $r = ord($data[$i]);
                $g = ord($data[$i + 1]);
                $b = ord($data[$i + 2]);

                $color = imagecolorallocate($img, $r, $g, $b);

                imagefilledrectangle($img, $x * $size, $y * $size, $x * $size + $size, $y * $size + $size, $color);

                $i += 3;
            }
        }

        return $img;
    }

    private static function byteToHex($byte)
    {
        return substr(self::HEX, $byte >> 4, 1) . substr(self::HEX, $byte & 0xf, 1);
    }

    public static function hex2String($hex)
    {
        $string = '';
        for($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
}