<?php

namespace TheNote\core\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use TheNote\core\world\weather\WeatherManager;

class ToggleDownFallCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("toggledownfall", "Toggles the weather",  "/toggledownfall");
        $this->setPermission("core.command.weather");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $cfg = new Config(Main::getInstance()->getDataFolder() . "snowtask.json" , Config::JSON);
        $api = new BaseAPI();
        if (!$sender instanceof Player) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("commandingame"));
            return;
        }
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("nopermission"));
            return;
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (($weather = WeatherManager::getInstance()->getWeather($world)) !== null) {
                if ($weather->isRaining()) {
                    $weather->stopStorm();
                    /*if ($api->getConfig("SnowMod") === true){
                        $cfg->set("snowallowed", false);
                        $cfg->save();
                        $sender->sendMessage("Toggled downfall and SnowMod Deactivatet");
                    } else {
                        $sender->sendMessage("Toggled downfall");
                    }*/
                } else {
                    $weather->startStorm();
                    /*if ($api->getConfig("SnowMod") === true){
                        $cfg->set("snowallowed", true);
                        $cfg->save();
                        $sender->sendMessage("Toggled downfall and SnowMod Activatet");
                    } else {
                        $sender->sendMessage("Toggled downfall");
                    }*/
                }
            }
        }
        if ($api->getConfig("SnowMod") === true){
            $cfg->set("snowallowed", true);
            $cfg->save();
            $sender->sendMessage("Toggled downfall and SnowMod Activatet");
        } else {
            $sender->sendMessage("Toggled downfall");
        }
    }
}