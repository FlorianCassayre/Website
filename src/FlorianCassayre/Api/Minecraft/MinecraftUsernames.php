<?php

namespace FlorianCassayre\Api\Minecraft;

use FlorianCassayre\Api\HttpUtils;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftErrorException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftInvalidInputException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftRateLimitException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownInputException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownNameException;
use FlorianCassayre\Api\Minecraft\Exceptions\MinecraftUnknownUUIDException;

class MinecraftUsernames
{
    const API_URL = 'https://api.mojang.com';

    /**
     * @param $name string the player name, case insensitive
     * @return object 'uuid': the uuid, 'name': the username
     * @throws MinecraftErrorException
     * @throws MinecraftInvalidInputException
     * @throws MinecraftRateLimitException
     * @throws MinecraftUnknownInputException
     */
    public static function getByName($name)
    {
        if(!MinecraftUtils::isMinecraftName($name))
            throw new MinecraftInvalidInputException();

        $result = HttpUtils::requestHttp(MinecraftUsernames::API_URL . '/users/profiles/minecraft/' . $name);

        if(!$result->success) // If the request failed (ie. Mojang servers are down)
        {
            throw new MinecraftErrorException();
        }

        switch($result->code) // Catch exceptions from http code
        {
            case 204: // Empty body
                throw new MinecraftUnknownNameException();
                break;
            case 400: // Bad request
                throw new MinecraftInvalidInputException();
                break;
            case 429: // Too many requests
                throw new MinecraftRateLimitException();
                break;
        }

        $object = json_decode($result->content);

        $legacy = isset($object->legacy) && $object->legacy;
        $demo = isset($object->demo) && $object->demo;

        return (object) array('uuid' => $object->id, 'name' => $object->name, 'is_migrated' => !$legacy, 'is_paid' => !$demo);
    }

    /**
     * @param $uuid
     * @return object
     * @throws MinecraftErrorException
     * @throws MinecraftInvalidInputException
     * @throws MinecraftRateLimitException
     * @throws MinecraftUnknownInputException
     */
    public static function getByUUID($uuid)
    {
        if(!MinecraftUtils::isUUID($uuid))
            throw new MinecraftInvalidInputException();

        $safe_uuid = MinecraftUtils::normalize($uuid);

        $result = HttpUtils::requestHttp(MinecraftUsernames::API_URL . '/user/profiles/' . $safe_uuid . '/names');

        if(!$result->success) // If the request failed (ie. Mojang servers are down)
        {
            throw new MinecraftErrorException();
        }

        switch($result->code) // Catch exceptions from http code
        {
            case 204: // Empty body
                throw new MinecraftUnknownUUIDException();
                break;
            case 400: // Bad request
                throw new MinecraftInvalidInputException();
                break;
            case 429: // Too many requests
                throw new MinecraftRateLimitException();
                break;
        }

        $object = json_decode($result->content);

        $first = null;
        $current = null;

        $old = array();

        foreach ((array) $object as $item)
        {
            if(isset($item->changedToAt))
            {
                array_push($old, (object) array('name' => $item->name, 'timestamp' => $item->changedToAt));
                $current = $item->name;
            }
            else
            {
                array_push($old, (object) array('name' => $item->name));
                $first = $item->name;
            }
        }

        if($current == null)
            $current = $first;

        return (object) array('uuid' => $safe_uuid, 'name' => $current, 'first_name' => $first, 'changes' => $old);
    }

    public static function getInformations($input)
    {
        $is_uuid = MinecraftUtils::isUUID($input);

        if($is_uuid)
        {
            $result_by_uuid = self::getByUUID($input);

            $uuid = $result_by_uuid->uuid;
            $name = $result_by_uuid->name;
            $first_name = $result_by_uuid->first_name;
            $changes = $result_by_uuid->changes;

            $result_by_name = self::getByName($name);

            $is_migrated = $result_by_name->is_migrated;
            $is_paid = $result_by_name->is_paid;
        }
        else
        {
            if(!MinecraftUtils::isMinecraftName($input))
                throw new MinecraftInvalidInputException(); // Neither uuid nor username

            $result_by_name = self::getByName($input);

            $uuid = $result_by_name->uuid;
            $name = $result_by_name->name;
            $is_migrated = $result_by_name->is_migrated;
            $is_paid = $result_by_name->is_paid;

            $result_by_uuid = self::getByUUID($uuid);

            $first_name = $result_by_uuid->first_name;
            $changes = $result_by_uuid->changes;
        }

        return (object) array('is_input_uuid' => $is_uuid, 'uuid' => $uuid, 'name' => $name, 'first_name' => $first_name, 'is_migrated' => $is_migrated, 'is_paid' => $is_paid, 'changes' => $changes);
    }
}