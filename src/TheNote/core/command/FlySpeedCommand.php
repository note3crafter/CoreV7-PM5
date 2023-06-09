<?php

namespace TheNote\core\command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Attribute;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use TheNote\core\BaseAPI;
use TheNote\core\Main;

class FlySpeedCommand extends Command
{

    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $api = new BaseAPI();
        $config = new Config($this->plugin->getDataFolder() . Main::$setup . "settings" . ".json", Config::JSON);
        parent::__construct("flyspeed", $config->get("prefix") . $api->getLang("speedprefix"), "/flyspeed");
        $this->setPermission("core.command.speed");

    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $api = new BaseAPI();
        $config = new Config($this->plugin->getDataFolder() . Main::$setup . "settings" . ".json", Config::JSON);
        if (!$sender instanceof Player) {
            $sender->sendMessage($config->get("error") . $api->getLang("commandingame"));
            return false;
        }
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($config->get("error") . $api->getLang("nopermission"));
            return false;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($config->get("info") . $api->getLang("speedusage"));
            return false;
        }
        if (!is_numeric($args[0])) {
            $sender->sendMessage($config->get("error") . $api->getLang("speednumb"));
            return false;
        }
        if((int)$args[0] === 0) {
            $this->setGroundSpeed($sender, 0);
        } elseif ((int)$args[0] > 255) {
            $sender->sendMessage($config->get("info") . $api->getLang("speedusage"));
            return false;
        } else {
            $this->setGroundSpeed($sender, $args[0]);
        }
        $message = str_replace("{speed}" ,  $args[0] , $api->getLang("speedsucces"));
        $sender->sendMessage($config->get("prefix") . $message);
        return true;
    }
    public function getGroundSpeed(Player $player) : int
    {
        $movement = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
        return $movement->getValue();
    }

    public function setGroundSpeed(Player $player, float $value) : void
    {
        $movement = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
        $movement->setValue($value);
    }

}