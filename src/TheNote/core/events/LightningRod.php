<?php

namespace TheNote\core\events;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Random;
use pocketmine\world\particle\BlockBreakParticle;
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
            if ($item->getId() === ItemIds::BLAZE_ROD) {
                $this->Lightning($player);
            }
        }
        return true;
    }

    public function Lightning(Player $player): void
    {
        $pos = $player->getPosition();
        $light2 = new AddActorPacket();
        $light2->actorUniqueId = Entity::nextRuntimeId();
        $light2->actorRuntimeId = 1;
        $light2->position = $player->getPosition()->asVector3();
        $light2->type = "minecraft:lightning_bolt";
        $light2->yaw = $player->getLocation()->getYaw();
        $light2->syncedProperties = new PropertySyncData([], []);        $block = $player->getWorld()->getBlock($player->getPosition()->floor()->down());
        $particle = new BlockBreakParticle($block);
        $player->getWorld()->addParticle($pos, $particle, $player->getWorld()->getPlayers());
        $sound2 = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 1, 1);
        Server::getInstance()->broadcastPackets($player->getWorld()->getPlayers(), [$light2, $sound2]);

    }
}