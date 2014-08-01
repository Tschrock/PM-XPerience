<?php

/**
 * XPerience - an XP framework for your pocketmine server.
 * 
 * @author Tschrock <tschrock@gmail.com>
 * @link http://www.tschrock.net
 */

namespace tschrock\xperience;

use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\Player;

/**
 * The API for working with xp; Where all the magic happens.
 */
class XPerienceAPI {

    /**
     * These are the action ids for getXpFor().
     */
    const ACTION_KILL = 0;
    const ACTION_BREED = 1;
    const ACTION_SMELT = 2;
    const ACTION_MINE = 3;
    const ACTION_FISH = 4;  # Preparing for
    const ACTION_TRADE = 5; # the future :D

    /**
     * Calculates the amount of xp gained from various actions
     * 
     * These values come directly from the minecraft wiki: http://minecraft.gamepedia.com/Xp
     * Some shortcuts are taken where necessary.
     * 
     * @param int $action The action id to calculate xp for.
     * @param mixed $object The object related to the action. For ex: ACTION_MINE would need the block mined.
     * @return int The amount of xp gained for $action.
     */

    public static function getXpFor($action, $object) {

        switch ($action) {
            case self::ACTION_KILL: # We don't have mobs yet :(
                if ($object instanceof pocketmine\entity\Living) {
                    $classname = get_class($object);
                    preg_match('(?<=\\)([\w]+)$', $classname, $matches);
                    var_dump($matches);
                    $type = $matches[0];

                    if (in_array($type, array("Pig", "Ocelot", "Wolf", "Chicken", "Cow", "Sheep", "Mooshroom"))) {
                        return rand(1, 3);
                    } elseif (in_array($type, array("Spider", "Zombie", "PigZombie", "Enderman", "Silverfish", "Skeleton", "Creeper"))) {

                        ### We don't even have mobs, why should I expect something like this?
                        #
                        # $items = count($object->getItemsSpawnedWith());  
                        # $itembonus = 0;
                        # 
                        # for ($i = 0; $i < $items; $i++) {
                        #    $itembonus += rand(1, 3);
                        # }
                        #
                    
                    return 5; # + $itembonus;
                    } elseif ($type == "Slime") {
                        return 2; # return $object->getSlimeSize();
                    } elseif ($type == "Villager") {
                        return 0;
                    } elseif ($type == "Player") {
                        return min(array(self::calculateLevelInfo(self::getXP($object))["level"] * 7, 100));
                    }
                }
                return false;

            case self::ACTION_BREED:  # Can't do this either, but i'm prepared
                if ($object instanceof pocketmine\entity\Animal) {
                    return rand(1, 4);
                }
                return false;
                
            case self::ACTION_SMELT:
                if ($object instanceof Item) {

                    $itemid = $object->getID();

                    if (in_array($itemid, array(Item::DIAMOND_ORE, Item::EMERALD_ORE, Item::GOLD_ORE))) {
                        return 1;
                    } elseif (in_array($itemid, array(Item::IRON_ORE, Item::REDSTONE_ORE))) {
                        return 0.7;
                    } elseif (in_array($itemid, array(Item::CLAY_BLOCK, Item::POTATO, Item::RAW_BEEF, Item::RAW_CHICKEN, Item::RAW_PORKCHOP/* , Item::RAW_FISH */))) {
                        return 0.35;
                    } elseif (in_array($itemid, array(Item::CLAY))) {
                        return 0.3;
                    } elseif (in_array($itemid, array(Item::CACTUS, Item::LAPIS_ORE, Item::NETHER_QUARTZ))) {
                        return 0.2;
                    } elseif (in_array($itemid, array(Item::LOG, Item::LOG2))) { # What's LOG2?
                        return 0.15;
                    } elseif (in_array($itemid, array(Item::COAL_ORE, Item::COBBLESTONE, Item::NETHERRACK, Item::SAND))) {
                        return 0.1;
                    }
                }
                return false;

            case self::ACTION_MINE:
                if (is_subclass_of($object, "pocketmine\block\Block")) {

                    $itemid = $object->getID();

                    if (in_array($itemid, array(Block::COAL_ORE))) {
                        return rand(0, 2);
                    } elseif (in_array($itemid, array(Block::DIAMOND_ORE, Block::EMERALD_ORE))) {
                        return rand(3, 7);
                    } elseif (in_array($itemid, array(Block::LAPIS_ORE))) {
                        return rand(2, 5);
                    } elseif (in_array($itemid, array(Block::REDSTONE_ORE))) {
                        return rand(1, 5);
                    }
                    /* elseif (in_array($itemid, array(Block::MONSTER_SPAWNER))){ # Just waiting for shog to add it...
                      return rand(15, 43);
                      } */
                }
                return false;
                
            case self::ACTION_FISH:  # No fishing yet :(
                if ($object instanceof Item) {
                    return rand(1, 3);
                } else {
                    return false;
                }
            default:
                return false;
        }
    }

    # We can use NBT Tags now! No more databases for little integrated things! :)
    # Remember to use pocketmine\nbt\tag\*, not just the value.
    # Probably still not a good idea for complex data, but for a simple float it should work perfectly.

    /**
     * Gets the amount of xp a player has.
     * 
     * @param \pocketmine\Player $player The player.
     * @return \pocketmine\nbt\tag\Float The amount of xp $player has.
     */
    public static function getXP(Player $player) {
        if (!isset($player->namedtag["XPerience"])) {
            $player->namedtag["XPerience"] = new \pocketmine\nbt\tag\Float("XPerience", 0);
        }
        return $player->namedtag["XPerience"];
    }

    /**
     * Adds to the amount of xp a player has.
     * 
     * @param \pocketmine\Player $player The player.
     * @param int|float $amount The ammount to add to $player 's xp.
     * @return \pocketmine\nbt\tag\Float The amount of xp $player now has.
     */
    public static function addXP(Player $player, $amount) {
        if (!isset($player->namedtag["XPerience"])) {
            $player->namedtag["XPerience"] = new \pocketmine\nbt\tag\Float("XPerience", 0);
        }
        return $player->namedtag["XPerience"] = $player->namedtag["XPerience"] + $amount;
    }

    /**
     * Takes away from the amount of xp a player has.
     * 
     * @param \pocketmine\Player $player The player.
     * @param int|float $amount The ammount to remove to $player 's xp.
     * @return \pocketmine\nbt\tag\Float The amount of xp $player now has.
     */
    public static function removeXP(Player $player, $amount) {
        if (!isset($player->namedtag["XPerience"])) {
            $player->namedtag["XPerience"] = new \pocketmine\nbt\tag\Float("XPerience", 0);
        }
        return $player->namedtag["XPerience"] = $player->namedtag["XPerience"] - $amount;
    }

    /**
     * Sets the amount of xp a player has.
     * 
     * @param \pocketmine\Player $player The player.
     * @param int|float $amount The ammount to set the $player 's xp to.
     * @return \pocketmine\nbt\tag\Float The amount of xp $player now has.
     */
    public static function setXP(Player $player, $amount) {
        return $player->namedtag["XPerience"] = new \pocketmine\nbt\tag\Float("XPerience", $amount);
    }

    /**
     * Calculates level information.
     * 
     * This is based off the psuedo-code found on the minecraft wiki.
     * 
     * @param int|float|\pocketmine\Player $xp
     * @return array An array containing: "level" - The current level; "remainingxp" - The xp that doesn't fit in a level; "xpnextlevel" - The amount of xp the next level requires.
     */
    public static function calculateLevelInfo($xp) {

        if ($xp instanceof Player){
            $xp = self::getXP($xp);
        }
        
        $level = ($xpneededforlevel = 0);
        $remainingxp = $xp;

        while (true) {
            if ($level >= 30) {
                $xpneededforlevel = 62 + ($level - 30) * 7;
            } elseif ($level >= 15) {
                $xpneededforlevel = 17 + ($level - 15) * 3;
            } else {
                $xpneededforlevel = 17;
            }

            if ($xpneededforlevel > $remainingxp) {
                break;
            } else {
                $level++;
                $remainingxp = $remainingxp - $xpneededforlevel;
            }
        }

        return array(
            "level" => $level,
            "remainingxp" => $remainingxp,
            "xpnextlevel" => $xpneededforlevel,
        );
    }

    /**
     * Just a helper function to show an xp-bar to the player.
     * 
     * @param \pocketmine\Player $player The player.
     */
    public static function showLevelBarTo(Player $player) {
        $player->sendMessage(" ");
        $player->sendMessage(self::getLevelBarFor($player));
        $player->sendMessage(" ");
    }

    /**
     * Generates a text xp-bar that somewhat resembles the pc version.
     * 
     * @param \pocketmine\Player $player
     * @param int $width The width of the bar.
     * @return string The xp-bar (In text)
     */
    public static function getLevelBarFor(Player $player, $width = 46) {
        $levelInfo = self::calculateLevelInfo($xp = self::getXP($player));
        $row1 = "Level " . $levelInfo["level"] . ", " . $levelInfo["remainingxp"] . "/" . $levelInfo["xpnextlevel"] . "xp (" . $xp . " total)";

        $totalLen = floor(($width / 2) * 1.5);
        $row1Len = floor(strlen($row1) / 2);
        $startpos = $totalLen - $row1Len;

        $row1 = str_repeat(" ", $startpos) . $row1;

        $row2 = self::makeBar($levelInfo["remainingxp"], $levelInfo["xpnextlevel"], $width);
        return $row1 . "\n" . $row2;
    }

    /**
     * Generates a text bar graph.
     * 
     * @param int $part The number of parts done.
     * @param int $total The number of parts to do.
     * @param int $width The width of the bar.
     * @param array $chars An array of characters to use in the graph: opening, part, remainder, and closing.
     * @return string The bar.
     */
    public static function makeBar($part, $total = 100, $width = 50, $chars = array("(", "-", "=", ")")) {
        $graph = $chars[0];
        $actualPart = round(($part / $total) * $width);
        $graph .= str_repeat($chars[1], $actualPart);
        $graph .= str_repeat($chars[2], $width - $actualPart);
        $graph .= $chars[3];
        return $graph;
    }

    
    /**
     * @var array A list of default item upgrades. Used in getItemUpgrade();
     */
    private static $ItemUpgrades = array(
        Item::WOODEN_AXE => array(Item::STONE_AXE, 15),
        Item::STONE_AXE => array(Item::IRON_AXE, 30),
        Item::IRON_AXE => array(Item::GOLD_AXE, 45),
        Item::GOLD_AXE => array(Item::DIAMOND_AXE, 60),
        #
        Item::WOODEN_PICKAXE => array(Item::STONE_PICKAXE, 15),
        Item::STONE_PICKAXE => array(Item::IRON_PICKAXE, 30),
        Item::IRON_PICKAXE => array(Item::GOLD_PICKAXE, 45),
        Item::GOLD_PICKAXE => array(Item::DIAMOND_PICKAXE, 60),
        #
        Item::WOODEN_HOE => array(Item::STONE_HOE, 15),
        Item::STONE_HOE => array(Item::IRON_HOE, 30),
        Item::IRON_HOE => array(Item::GOLD_HOE, 45),
        Item::GOLD_HOE => array(Item::DIAMOND_HOE, 60),
        #
        Item::WOODEN_SWORD => array(Item::STONE_SWORD, 15),
        Item::STONE_SWORD => array(Item::IRON_SWORD, 30),
        Item::IRON_SWORD => array(Item::GOLD_SWORD, 45),
        Item::GOLD_SWORD => array(Item::DIAMOND_SWORD, 60),
        #
        Item::WOODEN_SHOVEL => array(Item::STONE_SHOVEL, 15),
        Item::STONE_SHOVEL => array(Item::IRON_SHOVEL, 30),
        Item::IRON_SHOVEL => array(Item::GOLD_SHOVEL, 45),
        Item::GOLD_SHOVEL => array(Item::DIAMOND_SHOVEL, 60),
        #
        #
        Item::LEATHER_CAP => array(Item::CHAIN_HELMET, 15),
        Item::CHAIN_HELMET => array(Item::IRON_HELMET, 30),
        Item::IRON_HELMET => array(Item::GOLD_HELMET, 45),
        Item::GOLD_HELMET => array(Item::DIAMOND_HELMET, 60),
        #
        Item::LEATHER_TUNIC => array(Item::CHAIN_CHESTPLATE, 15),
        Item::CHAIN_CHESTPLATE => array(Item::IRON_CHESTPLATE, 30),
        Item::IRON_CHESTPLATE => array(Item::GOLD_CHESTPLATE, 45),
        Item::GOLD_CHESTPLATE => array(Item::DIAMOND_CHESTPLATE, 60),
        #
        Item::LEATHER_PANTS => array(Item::CHAIN_LEGGINGS, 15),
        Item::CHAIN_LEGGINGS => array(Item::IRON_LEGGINGS, 30),
        Item::IRON_LEGGINGS => array(Item::GOLD_LEGGINGS, 45),
        Item::GOLD_LEGGINGS => array(Item::DIAMOND_LEGGINGS, 60),
        #
        Item::LEATHER_BOOTS => array(Item::CHAIN_BOOTS, 15),
        Item::CHAIN_BOOTS => array(Item::IRON_BOOTS, 30),
        Item::IRON_BOOTS => array(Item::GOLD_BOOTS, 45),
        Item::GOLD_BOOTS => array(Item::DIAMOND_BOOTS, 60),
    );

    /**
     * Gets an upgraded item and it's cost.
     * 
     * @param \pocketmine\item\Item $item The item to upgrade.
     * @param array $upgradelist An optional array of upgrades in the format: fromItemId => array(toItemId, cost)
     * @return array An array containing the upgraded item and its cost.
     */
    public static function getItemUpgrade(Item $item, $upgradelist = false) {
        if ($upgradelist === false){
            $upgradelist = self::$ItemUpgrades;
        }
        if ($item instanceof \pocketmine\item\Item && isset($upgradelist[$item->getID()])) {
            $replacement = $upgradelist[$item->getID()];
            return array(Item::get($replacement[0], $item->getDamage()), $replacement[1]);
        } else {
            return array($item, false);
        }
    }

    /**
     * @var array A list of repairable Items. Used in getItemRepairCost();
     */
    private static $ItemRepairs = array(
        Item::WOODEN_AXE => 1,
        Item::WOODEN_HOE => 1,
        Item::WOODEN_PICKAXE => 1,
        Item::WOODEN_SHOVEL => 1,
        Item::WOODEN_SWORD => 1,
        #
        Item::STONE_AXE => 2,
        Item::STONE_HOE => 2,
        Item::STONE_PICKAXE => 2,
        Item::STONE_SHOVEL => 2,
        Item::STONE_SWORD => 2,
        #
        Item::IRON_AXE => 3,
        Item::IRON_HOE => 3,
        Item::IRON_PICKAXE => 3,
        Item::IRON_SHOVEL => 3,
        Item::IRON_SWORD => 3,
        #
        Item::GOLD_AXE => 4,
        Item::GOLD_HOE => 4,
        Item::GOLD_PICKAXE => 4,
        Item::GOLD_SHOVEL => 4,
        Item::GOLD_SWORD => 4,
        #
        Item::DIAMOND_AXE => 5,
        Item::DIAMOND_HOE => 5,
        Item::DIAMOND_PICKAXE => 5,
        Item::DIAMOND_SHOVEL => 5,
        Item::DIAMOND_SWORD => 5,
        #
        #
        Item::LEATHER_CAP => 1,
        Item::LEATHER_TUNIC => 1,
        Item::LEATHER_PANTS => 1,
        Item::LEATHER_BOOTS => 1,
        #
        Item::CHAIN_HELMET => 2,
        Item::CHAIN_CHESTPLATE => 2,
        Item::CHAIN_LEGGINGS => 2,
        Item::CHAIN_BOOTS => 2,
        #
        Item::IRON_HELMET => 3,
        Item::IRON_CHESTPLATE => 3,
        Item::IRON_LEGGINGS => 3,
        Item::IRON_BOOTS => 3,
        #
        Item::GOLD_HELMET => 4,
        Item::GOLD_CHESTPLATE => 4,
        Item::GOLD_LEGGINGS => 4,
        Item::GOLD_BOOTS => 4,
        #
        Item::DIAMOND_HELMET => 5,
        Item::DIAMOND_CHESTPLATE => 5,
        Item::DIAMOND_LEGGINGS => 5,
        Item::DIAMOND_BOOTS => 5,
        
        );
    
    public static function getItemRepairCost(Item $item, $repairlist = false){
        if ($repairlist == false) {
            $repairlist = self::$ItemRepairs;
        }
        
        if (isset($repairlist[$item->getID()])){
            $damage = $item->getDamage();
            return round(($damage/10)*$repairlist[$item->getID()]);
        }
        else {
            return false;
        }
        
    }
    
}
