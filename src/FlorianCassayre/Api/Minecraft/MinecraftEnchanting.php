<?php
/**
 * Created by IntelliJ IDEA.
 * User: Florian
 * Date: 22/11/2016
 * Time: 13:50
 */

namespace FlorianCassayre\Api\Minecraft;


use PDO;

class MinecraftEnchanting
{
    const ITEM_SWORD = 'sword';
    const ITEM_PICKAXE = 'pickaxe';
    const ITEM_SHOVEL = 'shovel';
    const ITEM_AXE = 'axe';
    const ITEM_BOOTS = 'boots';
    const ITEM_LEGGINGS = 'leggings';
    const ITEM_CHESTPLATE = 'chestplate';
    const ITEM_HELMET = 'helmet';
    const ITEM_BOW = 'bow';

    const MATERIAL_CHAINMAIL = 'chainmail';
    const MATERIAL_LEATHER = 'leather';
    const MATERIAL_WOOD = 'wood';
    const MATERIAL_GOLD = 'gold';
    const MATERIAL_STONE = 'stone';
    const MATERIAL_IRON = 'iron';
    const MATERIAL_DIAMOND = 'diamond';
    const MATERIAL_DEFAULT = 'default';

    private static $types_translations = array(
        self::ITEM_SWORD => 'Epée',
        self::ITEM_PICKAXE => 'Pioche',
        self::ITEM_SHOVEL => 'Pelle',
        self::ITEM_AXE => 'Hache',
        self::ITEM_BOOTS => 'Bottes',
        self::ITEM_LEGGINGS => 'Pantalon',
        self::ITEM_CHESTPLATE => 'Torse',
        self::ITEM_HELMET => 'Casque',
        self::ITEM_BOW => 'Arc'
    );

    private static $materials_translations = array(
        self::MATERIAL_CHAINMAIL => 'Cotte de maille',
        self::MATERIAL_LEATHER => 'Cuir',
        self::MATERIAL_WOOD => 'Bois',
        self::MATERIAL_GOLD => 'Or',
        self::MATERIAL_STONE => 'Pierre',
        self::MATERIAL_IRON => 'Fer',
        self::MATERIAL_DIAMOND => 'Diamant',
        self::MATERIAL_DEFAULT => '-'
    );

    private static $enchantments = null;

    private static $enchantments_names = array(
        0 => 'Protection',
        1 => 'Fire protection',
        2 => 'Feather Falling',
        3 => 'Blast Protection',
        4 => 'Projectile Protection',
        5 => 'Respiration',
        6 => 'Aqua Affinity',
        7 => 'Thorns',
        8 => 'Depth Strider',
        9 => 'Sharpness',
        10 => 'Smite',
        11 => 'Bane of Arthropods',
        12 => 'Knockback',
        13 => 'Fire Aspect',
        14 => 'Looting',
        15 => 'Efficiency',
        16 => 'Silk Touch',
        17 => 'Unbreaking',
        18 => 'Fortune',
        19 => 'Power',
        20 => 'Punch',
        21 => 'Flame',
        22 => 'Infinity',
        23 => 'Luck of the Sea',
        24 => 'Lure'
    );

    public static function getEnchantments()
    {
        return array(
            0 => self::enchantment('Protection', 4, array(self::ITEM_BOOTS, self::ITEM_LEGGINGS, self::ITEM_CHESTPLATE, self::ITEM_HELMET)),
            1 => self::enchantment('Fire protection', 4, array(self::ITEM_BOOTS, self::ITEM_LEGGINGS, self::ITEM_CHESTPLATE, self::ITEM_HELMET)),
            2 => self::enchantment('Feather Falling', 4, array(self::ITEM_BOOTS)),
            3 => self::enchantment('Blast Protection', 4, array(self::ITEM_BOOTS, self::ITEM_LEGGINGS, self::ITEM_CHESTPLATE, self::ITEM_HELMET)),
            4 => self::enchantment('Projectile Protection', 4, array(self::ITEM_BOOTS, self::ITEM_LEGGINGS, self::ITEM_CHESTPLATE, self::ITEM_HELMET)),
            5 => self::enchantment('Respiration', 3, array(self::ITEM_HELMET)),
            6 => self::enchantment('Aqua Affinity', 1, array(self::ITEM_HELMET)),
            7 => self::enchantment('Thorns', 3, array(self::ITEM_CHESTPLATE)),
            8 => self::enchantment('Depth Strider', 3, array(self::ITEM_BOOTS)),
            9 => self::enchantment('Sharpness', 5, array(self::ITEM_SWORD)),
            10 => self::enchantment('Smite', 5, array(self::ITEM_SWORD)),
            11 => self::enchantment('Bane of Arthropods', 5, array(self::ITEM_SWORD)),
            12 => self::enchantment('Knockback', 2, array(self::ITEM_SWORD)),
            13 => self::enchantment('Fire Aspect', 2, array(self::ITEM_SWORD)),
            14 => self::enchantment('Looting', 3, array(self::ITEM_SWORD)),
            15 => self::enchantment('Efficiency', 5, array(self::ITEM_PICKAXE, self::ITEM_SHOVEL, self::ITEM_AXE)),
            16 => self::enchantment('Silk Touch', 1, array(self::ITEM_PICKAXE, self::ITEM_SHOVEL, self::ITEM_AXE)),
            17 => self::enchantment('Unbreaking', 3, array(self::ITEM_BOOTS, self::ITEM_LEGGINGS, self::ITEM_CHESTPLATE, self::ITEM_HELMET, self::ITEM_SWORD, self::ITEM_PICKAXE, self::ITEM_SHOVEL, self::ITEM_AXE, self::ITEM_BOW)),
            18 => self::enchantment('Fortune', 3, array(self::ITEM_PICKAXE, self::ITEM_SHOVEL, self::ITEM_AXE)),
            19 => self::enchantment('Power', 5, array(self::ITEM_BOW)),
            20 => self::enchantment('Punch', 2, array(self::ITEM_BOW)),
            21 => self::enchantment('Flame', 1, array(self::ITEM_BOW)),
            22 => self::enchantment('Infinity', 1, array(self::ITEM_BOW)),
            23 => self::enchantment('Luck of the Sea', 3, array()),
            24 => self::enchantment('Lure', 3, array())
        );
    }

    private static function enchantment($enchantment_name, $max_level, $items)
    {
        return (object) array('name' => $enchantment_name, 'code' => str_replace(' ', '_', strtolower($enchantment_name)), 'max_level' => $max_level, 'items' => $items);
    }

    public static function getItems()
    {
        if(self::$enchantments == null)
        self::$enchantments = array(
            0 => self::item(self::ITEM_PICKAXE, self::MATERIAL_WOOD, 1),
            1 => self::item(self::ITEM_PICKAXE, self::MATERIAL_GOLD, 5),
            2 => self::item(self::ITEM_PICKAXE, self::MATERIAL_STONE, 3),
            3 => self::item(self::ITEM_PICKAXE, self::MATERIAL_IRON, 7),
            4 => self::item(self::ITEM_PICKAXE, self::MATERIAL_DIAMOND, 9),

            5 => self::item(self::ITEM_SHOVEL, self::MATERIAL_WOOD, 1),
            6 => self::item(self::ITEM_SHOVEL, self::MATERIAL_GOLD, 5),
            7 => self::item(self::ITEM_SHOVEL, self::MATERIAL_STONE, 3),
            8 => self::item(self::ITEM_SHOVEL, self::MATERIAL_IRON, 7),
            9 => self::item(self::ITEM_SHOVEL, self::MATERIAL_DIAMOND, 9),

            10 => self::item(self::ITEM_AXE, self::MATERIAL_WOOD, 1),
            11 => self::item(self::ITEM_AXE, self::MATERIAL_GOLD, 5),
            12 => self::item(self::ITEM_AXE, self::MATERIAL_STONE, 3),
            13 => self::item(self::ITEM_AXE, self::MATERIAL_IRON, 7),
            14 => self::item(self::ITEM_AXE, self::MATERIAL_DIAMOND, 9),

            15 => self::item(self::ITEM_SWORD, self::MATERIAL_WOOD, 0),
            16 => self::item(self::ITEM_SWORD, self::MATERIAL_GOLD, 4),
            17 => self::item(self::ITEM_SWORD, self::MATERIAL_STONE, 2),
            18 => self::item(self::ITEM_SWORD, self::MATERIAL_IRON, 6),
            19 => self::item(self::ITEM_SWORD, self::MATERIAL_DIAMOND, 8),

            20 => self::item(self::ITEM_BOOTS, self::MATERIAL_CHAINMAIL, 13),
            21 => self::item(self::ITEM_BOOTS, self::MATERIAL_LEATHER, 17),
            22 => self::item(self::ITEM_BOOTS, self::MATERIAL_GOLD, 21),
            23 => self::item(self::ITEM_BOOTS, self::MATERIAL_IRON, 25),
            24 => self::item(self::ITEM_BOOTS, self::MATERIAL_DIAMOND, 29),

            25 => self::item(self::ITEM_LEGGINGS, self::MATERIAL_CHAINMAIL, 12),
            26 => self::item(self::ITEM_LEGGINGS, self::MATERIAL_LEATHER, 16),
            27 => self::item(self::ITEM_LEGGINGS, self::MATERIAL_GOLD, 20),
            28 => self::item(self::ITEM_LEGGINGS, self::MATERIAL_IRON, 24),
            29 => self::item(self::ITEM_LEGGINGS, self::MATERIAL_DIAMOND, 28),

            30 => self::item(self::ITEM_CHESTPLATE, self::MATERIAL_CHAINMAIL, 11),
            31 => self::item(self::ITEM_CHESTPLATE, self::MATERIAL_LEATHER, 15),
            32 => self::item(self::ITEM_CHESTPLATE, self::MATERIAL_GOLD, 19),
            33 => self::item(self::ITEM_CHESTPLATE, self::MATERIAL_IRON, 23),
            34 => self::item(self::ITEM_CHESTPLATE, self::MATERIAL_DIAMOND, 27),

            35 => self::item(self::ITEM_HELMET, self::MATERIAL_CHAINMAIL, 10),
            36 => self::item(self::ITEM_HELMET, self::MATERIAL_LEATHER, 14),
            37 => self::item(self::ITEM_HELMET, self::MATERIAL_GOLD, 18),
            38 => self::item(self::ITEM_HELMET, self::MATERIAL_IRON, 22),
            39 => self::item(self::ITEM_HELMET, self::MATERIAL_DIAMOND, 26),

            40 => self::item(self::ITEM_BOW, self::MATERIAL_DEFAULT, 30)
        );

        return self::$enchantments;
    }

    private static function item($type_code, $material_code, $db_id)
    {
        return (object) array('type_code' => $type_code, 'type_name' => self::$types_translations[$type_code], 'material_code' => $material_code, 'material_name' => self::$materials_translations[$material_code], 'db_id' => $db_id);
    }

    public static function getItemSelect()
    {
        $items = self::getItems();

        $types = array();
        foreach (self::$types_translations as $type_code => $type_name) {
            array_push($types, (object) array('code' => $type_code, 'name' => $type_name));
        }

        $materials = array();
        foreach (self::$materials_translations as $material_code => $material_name) {
            array_push($materials, (object) array('code' => $material_code, 'name' => $material_name));
        }

        foreach($materials as $material) {
            $used_by = array();
            foreach($items as $item) {
                if($material->code === $item->material_code)
                    array_push($used_by, $item->type_code);
            }
            $material->{'used_by'} = $used_by;
        }

        $materials = array_reverse($materials); // Best materials first

        return (object) array('types' => $types, 'materials' => $materials);
    }

    /**
     * @param $id int the internal id (not the minecraft id system)
     * @return string a readable name
     */
    public static function getEnchantmentNameById($id)
    {
        if(!isset(self::getEnchantments()[$id]))
            throw new \InvalidArgumentException();
        return self::getEnchantments()[$id]->name;
    }

    /**
     * @param $code
     * @return int
     */
    public static function getEnchantmentIdByCode($code)
    {
        foreach(self::getEnchantments() as $id => $enchantment) {
            if($enchantment->code === $code)
                return $id;
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param $type
     * @param $material
     * @return int
     */
    public static function getItemId($type, $material)
    {
        foreach(self::getItems() as $item)
            if($item->type_code === $type && $item->material_code === $material)
                return $item->db_id;
        throw new \InvalidArgumentException();
    }

    private static function getCleanEnchantmentLevel($level)
    {
        switch($level)
        {
            case 1:
                return 'I';
            case 2:
                return 'II';
            case 3:
                return 'III';
            case 4:
                return 'IV';
            case 5:
                return 'V';
            default:
                return strval($level);
        }
    }

    public static function getCleanEnchantmentName($id, $level)
    {
        return self::getEnchantmentNameById($id) . ' ' . self::getCleanEnchantmentLevel($level);
    }

    /**
     * @param $hex string
     * @return object
     */
    private static function hexToEnchantmentsObjects($hex, $frequency)
    {
        $enchantments = array();

        for($i = 0; $i < count(self::getEnchantments()); $i++)
        {
            $index = strlen($hex) - 1 - $i;

            $level = intval($hex[$index]);
            if($level > 0)
            {
                $name = self::getEnchantmentNameById($i);
                $name_full = $name . ' ' . self::getCleanEnchantmentLevel($level);
                array_push($enchantments, (object) array('id' => $i, 'level' => $level, 'name' => $name, 'name_full' => $name_full));
            }
        }

        return (object) array('enchantments' => $enchantments, 'frequency' => $frequency);
    }

    /**
     * @param $db pdo
     * @param $item_id int
     * @param $levels int
     * @return array
     */
    public static function getCombinations($db, $item_id, $levels)
    {
        $sql = 'SELECT HEX(enchantments) AS enchantments, frequency FROM enchanting_stats INNER JOIN enchanting_combinations ON enchanting_stats.combination = enchanting_combinations.id WHERE item = :item AND levels = :levels ORDER BY frequency DESC';
        $sth = $db->prepare($sql);
        $sth->bindParam(':item', $item_id);
        $sth->bindParam(':levels', $levels);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $enchantments = array();

        foreach($results as $result)
        {
            $enchants_hex = $result['enchantments'];
            $frequency = floatval($result['frequency']);
            $enchantments_object = self::hexToEnchantmentsObjects($enchants_hex, $frequency);
            array_push($enchantments, $enchantments_object);
        }

        return $enchantments;
    }
    //SELECT * FROM `enchanting_combinations` WHERE SUBSTRING(HEX(enchantments), -0 - 1) != '0'
}