<?php

/** 
 * XPerience - an XP framework for your pocketmine server.
 * 
 * @author Tschrock <tschrock@gmail.com>
 * @link http://www.tschrock.net
 * @internal PlayerEventListener
 */

namespace tschrock\xperience;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\Player;

/**
 * The event listener. Has the basic ways for getting xp (Other plugins can make their own ways).
 */
class PlayerEventListener implements Listener {

    /**
     * When a block is broken - check for mine xp.
     * 
     * Could be better. Need to check for what was used to break the block and the gamemode of the player.
     * 
     * @param BlockBreakEvent $event
     *
     * @priority MONITOR
     * @ignoreCancelled false
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $xp = XPerienceAPI::getXpFor(XPerienceAPI::ACTION_MINE, $block);
        if ($xp != false) {
            $xptotal = XPerienceAPI::addXP($player, $xp);
            $player->sendMessage("[XP] You got " . $xp . "xp! (" . $xptotal . " total)");
        }
    }

    /**
     * An attempt at catching smelting - doesn't work atm. (Can't use furnaces)
     * 
     * @param CraftItemEvent $event
     *
     * @priority MONITOR
     * @ignoreCancelled false
     */
    public function onCraftItem(CraftItemEvent $event) {
        $player = $event->getTransaction()->getSource();
        if ($player instanceof Player) {
            $block = $event->getTransaction()->getResult();
            $xp = XPerienceAPI::getXpFor(XPerienceAPI::ACTION_SMELT, $block);
            if ($xp != false) {
                $xptotal = XPerienceAPI::addXP($player, $xp);
                $player->sendMessage("[XP] You got " . $xp . "xp! (" . $xptotal . " total)");
            }
        }
    }

    /**
     * A placeholder for when a mob is killed. Can't do anything untill we actualy have mobs.
     * 
     * @param type $event
     */
    public function onMobKilled($event){
        
    }
        
    /**
     * A placeholder for when a player breed an animal. Can't do anything untill we actualy have mobs.
     * 
     * @param type $event
     */
    public function onAnimalBreed($event){
        
    }
}
