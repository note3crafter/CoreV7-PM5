<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\core\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use TheNote\core\Main;
use TheNote\core\utils\PlayerUtils;

class UserdataListener implements Listener {

    public function __construct(private Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $pf = new Config($this->plugin->getDataFolder() . Main::$logdatafile . strtolower($name) . ".json");
        $pf->set("Name", $player->getName());
        $pf->set("IP", $player->getNetworkSession()->getIp());
        $pf->set("Xbox-ID", $player->getPlayerInfo()->getXuid());
        $pf->set("OS", PlayerUtils::getPlayerPlatform($player));
        $pf->set("ID", $player->getUniqueId());
        $pf->set("Last_Join", date('d.m.Y H:I') . date_default_timezone_set("Europe/Berlin"));
        $pf->save();
    }
}