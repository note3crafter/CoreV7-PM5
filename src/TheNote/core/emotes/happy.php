<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\core\emotes;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use TheNote\core\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class happy extends Command
{
    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $langsettings = new Config($this->plugin->getDataFolder() . Main::$lang . "LangConfig.yml", Config::YAML);
        $l = $langsettings->get("Lang");
        $lang = new Config($this->plugin->getDataFolder() . Main::$lang . "Lang_" . $l . ".json", Config::JSON);

        $config = new Config($this->plugin->getDataFolder() . Main::$setup . "settings" . ".json", Config::JSON);
        parent::__construct("happy", $config->get("prefix") . $lang->get("happyprefix"), "/happy");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $langsettings = new Config($this->plugin->getDataFolder() . Main::$lang . "LangConfig.yml", Config::YAML);
        $l = $langsettings->get("Lang");
        $lang = new Config($this->plugin->getDataFolder() . Main::$lang . "Lang_" . $l . ".json", Config::JSON);
        if (!$sender instanceof Player) {
            return $this->plugin->getServer()->broadcastMessage($lang->get("happysucces"));
        }
        $dcsettings = new Config($this->plugin->getDataFolder() . Main::$setup . "discordsettings" . ".yml", Config::YAML);
        $playerdata = new Config($this->plugin->getDataFolder() . Main::$cloud . "players.yml", Config::YAML);
        $nickname = $sender->getNameTag();
        $name = $sender->getName();
        $prefix = $playerdata->getNested($sender->getName() . ".group");
        $chatprefix = $dcsettings->get("chatprefix");
        $message = str_replace("{player}", $nickname, $lang->get("happysucces"));
        $this->plugin->getServer()->broadcastMessage($message);
        if ($dcsettings->get("DC") == true) {
            $ar = getdate();
            $time = $ar['hours'] . ":" . $ar['minutes'];
            $format = $chatprefix . " : {time} : $prefix {player} ist glücklich :D";
            $msg = str_replace("{time}", $time, str_replace("{player}", $name, $format));
            $this->plugin->sendMessage($name, $msg);
        }
        return true;
    }
}