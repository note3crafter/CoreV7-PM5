<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\core\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use TheNote\core\BaseAPI;
use TheNote\core\Main;

class ListHomeCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $api = new BaseAPI();
        parent::__construct("listhome", $api->getSetting("prefix") . $api->getLang("listhomesprefix"), "/listhome", ["homes"]);
        $this->setPermission(Main::$defaultperm);
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $api = new BaseAPI();
        if (!$sender instanceof Player) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("commandingame"));
            return false;
        }
        $home = new Config($this->plugin->getDataFolder() . Main::$homefile . $sender->getName() . ".json", Config::JSON);
        $homes = $home->getAll(true);
        $sender->sendMessage($api->getSetting("info") . $api->getLang("listhomes"));
        foreach ($homes as $key) {
            if ($homes === null) {
                $sender->sendMessage($api->getSetting("error") . $api->getLang("listhomeserror"));
            } else {
                $sender->sendMessage("§e-" . $key);
            }
        }
        return true;
    }
}