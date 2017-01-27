<?php

namespace FlorianCassayre\Api\Minecraft;

class MinecraftUtils
{
    /**
     * @param $name
     * @return bool
     */
    public static function isMinecraftName($name)
    {
        return is_string($name) && preg_match('/^[A-z0-9_]{2,16}$/', $name);
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function isUUID($uuid)
    {
        return self::isUUIDWithDashes($uuid) // With dashes
        || self::isUUIDWithoutDashes($uuid);    // Without dashes
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function isUUIDWithDashes($uuid)
    {
        return is_string($uuid) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid);
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function isUUIDWithoutDashes($uuid)
    {
        return is_string($uuid) && preg_match('/^[0-9a-fA-F]{32}$/', $uuid);
    }

    /**
     * @param $uuidWithout
     * @return string
     */
    public static function addDashes($uuidWithout)
    {
        if(!self::isUUIDWithoutDashes($uuidWithout) || !is_string($uuidWithout))
            throw new \InvalidArgumentException();

        return substr($uuidWithout, 0, 8) . '-' . substr($uuidWithout, 8, 4) . '-' . substr($uuidWithout, 12, 4) . '-' . substr($uuidWithout, 16, 4)  . '-' . substr($uuidWithout, 20);
    }

    /**
     * @param $uuid
     * @return string
     */
    public static function normalize($uuid)
    {
        if(!self::isUUID($uuid) || !is_string($uuid))
            throw new \InvalidArgumentException();

        $safe_uuid = strtolower($uuid);
        $safe_uuid = str_replace('-', '', $safe_uuid);

        return $safe_uuid;
    }
}