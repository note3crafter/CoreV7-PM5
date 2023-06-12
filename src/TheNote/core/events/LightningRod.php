<?php

namespace TheNote\core\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemTypeIds;
use TheNote\core\Main;

class LightningRod implements Listener
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event): bool
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player->hasPermission("core.events.lightningrod")) {
            if ($item->getTypeId() === ItemTypeIds::BLAZE_ROD) {
                $this->plugin->addStrike($player);
            }
        }
        return true;
    }
}