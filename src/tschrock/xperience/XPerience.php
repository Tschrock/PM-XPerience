<?php

/**
 * XPerience - an XP framework for your pocketmine server.
 * 
 * @author Tschrock <tschrock@gmail.com>
 * @link http://www.tschrock.net
 * @internal XPerience
 */

namespace tschrock\xperience;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;

/**
 * The main plugin class.
 */
class XPerience extends PluginBase {

    /**
     * The onLoad function - empty.
     */
    public function onLoad() {
        
    }

    /**
     * The onEnable function - just registering events and setting up the (nonexistant) config.
     */
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener($this), $this);

        $this->saveDefaultConfig();
        $this->reloadConfig();
    }

    /**
     * The onDisable function - also empty.
     */
    public function onDisable() {
        
    }

    /**
     * The command handler - Handles user input for the /xp, /upgrade, and /repair commands.
     * 
     * @param \pocketmine\command\CommandSender $sender The person who sent the command.
     * @param \pocketmine\command\Command $command The command.
     * @param string $label The label for the command. - What's this?
     * @param array $args The arguments with the command.
     * @return boolean Wether or not the command succeded.
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command->getName()) {
            case "xp":
                if ($sender instanceof \pocketmine\Player) {
                    XPerienceAPI::showLevelBarTo($sender);
                }
                return true;
            case "upgrade":
                if ($sender instanceof \pocketmine\Player && (count($args) == 0 || count($args) == 1)) {

                    $item = $sender->getInventory()->getItemInHand();
                    $upgrade = XPerienceAPI::getItemUpgrade($item);

                    if ($upgrade[1] == false) {
                        $sender->sendMessage("[XP] You can't upgrade that!");
                    } elseif (count($args) == 0) {
                        if (XPerienceAPI::getXP($sender) < $upgrade[1]) {
                            $sender->sendMessage("[XP] You dont have enough xp to upgrade that!");
                            $sender->sendMessage("[XP] You need " . $upgrade[1] . " xp to upgrade " . $item->getName() . " to " . $upgrade[0]->getName() . ".");
                        } else {
                            XPerienceAPI::removeXP($sender, $upgrade[1]);
                            $sender->getInventory()->setItemInHand($upgrade[0]);
                            $sender->sendMessage("[XP] upgraded " . $item->getName() . " to " . $upgrade[0]->getName() . " for " . $upgrade[1] . " xp.");
                        }
                    } elseif (count($args) == 1 && ($args[0] == "view" || $args[0] == "cost")) {
                        $sender->sendMessage("[XP] You will need " . $upgrade[1] . " xp to upgrade " . $item->getName() . " to " . $upgrade[0]->getName() . ".");
                    } else {
                        $sender->sendMessage("Usage: /upgrade [view]");
                    }
                }
                return true;
            case "repair":
                if ($sender instanceof \pocketmine\Player && (count($args) == 0 || count($args) == 1)) {

                    $item = $sender->getInventory()->getItemInHand();

                    $repairCost = XPerienceAPI::getItemRepairCost($item);

                    if ($repairCost == false) {
                        $sender->sendMessage("[XP] You can't repair that!");
                    } elseif (count($args) == 0) {
                        if (XPerienceAPI::getXP($sender) < $repairCost) {
                            $sender->sendMessage("[XP] You dont have enough xp to repair that!");
                            $sender->sendMessage("[XP] You need " . $repairCost . " xp to fix " . $item->getName() . ".");
                        } else {
                            XPerienceAPI::removeXP($sender, $repairCost);
                            $item->setDamage(0);
                            $sender->getInventory()->setItemInHand($item);
                            $sender->sendMessage("[XP] fixed " . $item->getName() . " for " . $repairCost . " xp.");
                        }
                    } elseif (count($args) == 1 && ($args[0] == "view" || $args[0] == "cost")) {
                        $sender->sendMessage("[XP] You will need " . $repairCost . " xp to repair " . $item->getName() . ".");
                    } else {
                        $sender->sendMessage("Usage: /repair [view]");
                    }
                }
                return true;
            default:
                return false;
        }
    }

}
