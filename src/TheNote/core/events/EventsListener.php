<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//                        2017-2020

namespace TheNote\core\events;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use TheNote\core\entity\SkullEntity;
use TheNote\core\player\Player as MyPlayer;

use TheNote\core\Main;

class EventsListener implements Listener
{

    public function remove(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Player && isset(Main::$godmod[$entity->getName()])){
			if(Main::$godmod[$entity->getName()]){
				$event->cancel();
			}
		}
	}

    /*public function onBreak(BlockBreakEvent $event)
    {
        if ($event->isCancelled()) return;
        if ($event->getBlock()->getId() === Block::SKULL_BLOCK) {
            if (($skull = $event->getBlock()->getLevelNonNull()->getNearestEntity($event->getBlock()->floor()->add(0.5, 0, 0.5), 0.5)) instanceof SkullEntity) {

                $name = ($skull->namedtag->hasTag("skull_name", StringTag::class) ? $skull->namedtag->getString("skull_name") : "-");

                $event->setDrops([Main::constructPlayerHeadItem($name, $skull->getSkin())]);

                $skull->flagForDespawn();
            }
        }
    }

    public function onSpread(BlockSpreadEvent $event)
    {
        if ($event->isCancelled()) return;
        if ($event->getBlock()->getId() === Block::SKULL_BLOCK and $event->getBlock()->getDamage() === 1) {
            if (($skull = $event->getBlock()->getLevelNonNull()->getNearestEntity($event->getBlock()->floor()->add(0.5, 0, 0.5), 0.3)) instanceof SkullEntity) {

                $name = ($skull->namedtag->hasTag("skull_name", StringTag::class) ? $skull->namedtag->getString("skull_name") : "-");

                $event->getBlock()->getLevelNonNull()->dropItem($event->getBlock()->add(0, 0.5), Main::constructPlayerHeadItem($name, $skull->getSkin()));

                $skull->flagForDespawn();
            }
        }
    }

    public function onLevelChange(ChangeDimensionPacket $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {

            $originGenerator = $event->getOrigin()->getProvider()->getGenerator();
            $targetGenerator = $event->getTarget()->getProvider()->getGenerator();

            $getDimension = function ($generator): int {
                switch ($generator) {
                    case "normal":
                    case "skyblock":
                    case "void":
                        return 0;
                    case "nether":
                        return 1;
                    case "ender":
                        return 2;
                    default:
                        return 0;
                }
            };

            if ($getDimension($originGenerator) == $getDimension($targetGenerator)) return;
            $pk = new ChangeDimensionPacket();
            $pk->dimension = $getDimension($targetGenerator);
            $pk->position = $event->getTarget()->getSpawnLocation();
            $entity->dataPacket($pk);
        }
    }*/
}
