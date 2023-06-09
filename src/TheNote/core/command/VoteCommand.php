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
use pocketmine\network\NetworkInterface;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\Config;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use TheNote\core\server\ServerList;
use TheNote\core\task\RequestThread;

class VoteCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $api = new BaseAPI();
        parent::__construct("vote", $api->getSetting("prefix") . $api->getLang("voteprefix"), "/vote");
        $this->setPermission(Main::$defaultperm);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $api = new BaseAPI();
        if (!$sender instanceof Player) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("commandingame"));
            return false;
        }
        $this->plugin->queue[] = strtolower($sender->getName());
        $requests = [];
        foreach ($this->plugin->lists as $list) {
            if (isset($list["check"]) && isset($list["claim"])) {
                $requests[] = new ServerList($list["check"], $list["claim"]);
            }
        }
        //$query = new RequestThread(strtolower($sender->getName()), array([$requests]));
        $this->plugin->getServer()->getAsyncPool()->submitTask(new RequestThread(strtolower($sender->getName()), array([$requests])));
        return true;
    }
}