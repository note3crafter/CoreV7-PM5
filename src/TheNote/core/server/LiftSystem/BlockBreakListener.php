<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\core\server\LiftSystem;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\utils\Config;
use TheNote\core\server\LiftListener;
use TheNote\core\Main;

class BlockBreakListener extends LiftListener implements Listener {

    public function onBlockBreak(BlockBreakEvent $event) {
        $settings = new Config($this->getPlugin()->getDataFolder() . Main::$setup . "settings" . ".json", Config::JSON);
        if($event->isCancelled()) return;
        if($event->getBlock()->getTypeId() !== VanillaBlocks::DAYLIGHT_SENSOR()->getTypeId() && $event->getBlock()->getTypeId() !== VanillaBlocks::DAYLIGHT_SENSOR()->getTypeId()) return;
        $event->getPlayer()->sendTip($settings->get("lift") . "Du hast diesen Lift erfolgreich zerstört.");
    }
}