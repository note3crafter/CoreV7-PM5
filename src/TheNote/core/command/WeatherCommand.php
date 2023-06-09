<?php

namespace TheNote\core\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use TheNote\core\world\weather\WeatherManager;

class WeatherCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("weather", "change weather", "/weather", ["clear", "rain", "thunder"]);
        $this->setPermission("core.command.weather");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $api = new BaseAPI();
        $cfg = new Config(Main::getInstance()->getDataFolder() . "snowtask.json" , Config::JSON);
        if (!$sender instanceof Player) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("commandingame"));
            return;
        }
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("nopermission"));
            return;
        }
        $duration = 6000;

        $weathers = [];
        if (!$sender instanceof Player) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                if (($weather = WeatherManager::getInstance()->getWeather($world)) !== null) {
                    $weathers[] = $weather;
                }
            }
        } else {
            $weathers[] = WeatherManager::getInstance()->getWeather($sender->getWorld());
        }

        if (isset($args[1]) && is_numeric($args[1])) {
            $duration = intval($args[1]);
        }
        switch ($type = strtolower($args[0])) {
            case "clear":
                foreach ($weathers as $weather) $weather->stopStorm();
                if ($api->getConfig("SnowMod") === true){
                    $cfg->set("snowallowed", false);
                    $cfg->save();
                    $sender->sendMessage("Changing to clear weather and SnowMod Deactivatet");
                } else {
                    $sender->sendMessage("Changing to clear weather");
                }
                break;
            case "query":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "This command is only available in game.");
                    return;
                }
                $state = "clear";
                if ($api->getConfig("SnowMod") === true){
                    $cfg->set("snowallowed", false);
                    $cfg->save();
                }
                $weather = WeatherManager::getInstance()->getWeather($sender->getWorld());
                if ($weather->isRaining()) {
                    if ($weather->isThundering()) {
                        $state = "thunder";
                    } else {
                        $state = "rain";
                    }
                }
                $this->plugin->getScheduler()->cancelAllTasks();
                $sender->sendMessage("Weather state is: " . $state);
                return;
            case "rain":
                foreach ($weathers as $weather) $weather->startStorm(false, $duration);
                if ($api->getConfig("SnowMod") === true){
                    $cfg->set("snowallowed", true);
                    $cfg->save();
                    $sender->sendMessage("Changing to rainy weather and SnowMod Activatet");
                } else {
                    $sender->sendMessage("Changing to rainy weather");
                }
                return;
            case "thunder":
                foreach ($weathers as $weather) $weather->startStorm(true, $duration);
                if ($api->getConfig("SnowMod") === true){
                    $cfg->set("snowallowed", true);
                    $cfg->save();
                    $sender->sendMessage("Changing to rain and thunder and SnowMod Activatet");
                } else {
                    $sender->sendMessage("Changing to rain and thunder");
                }
                return;
            default:
                $sender->sendMessage("/weather (clear rain thunder)");
        }
    }
}