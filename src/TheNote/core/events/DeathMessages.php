<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\core\events;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use TheNote\core\BaseAPI;
use TheNote\core\Main;

class DeathMessages implements Listener
{

	private Main $plugin;

	public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getNameTag();
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            $api = new BaseAPI();

            if ($cause->getCause() == EntityDamageEvent::CAUSE_CONTACT) {
                $event->setDeathMessage($name . $api->getSetting("zuhoch"));
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
                $event->setDeathMessage($name . $api->getSetting("entityattacke"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_PROJECTILE) {
                $event->setDeathMessage($name . $api->getSetting("abgeschossen"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_SUFFOCATION) {
                $event->setDeathMessage($name . $api->getSetting("erstickte"));
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_FIRE) {
                $event->setDeathMessage($name . $api->getSetting("verbrannteimstehen"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_FIRE_TICK) {
                $event->setDeathMessage($name . $api->getSetting("verbrannte"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_LAVA) {
                $event->setDeathMessage($name . $api->getSetting("lava"));
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_DROWNING) {
                $event->setDeathMessage($name . $api->getSetting("ertrinken"));
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_ENTITY_EXPLOSION || $cause->getCause() == EntityDamageEvent::CAUSE_BLOCK_EXPLOSION) {
                $event->setDeathMessage($name . $api->getSetting("hochgejagt"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_VOID) {
                $event->setDeathMessage($name . $api->getSetting("void"));
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_SUICIDE) {
                $event->setDeathMessage($name . $api->getSetting("selbstmord"));
                $this->plugin->addStrike($player);
            } elseif ($cause->getCause() == EntityDamageEvent::CAUSE_MAGIC) {
                $event->setDeathMessage($name . $api->getSetting("magic"));
            }
        }
        return true;
    }
}