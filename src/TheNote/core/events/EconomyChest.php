<?php

namespace TheNote\core\events;

use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\tile\Chest;
use pocketmine\block\utils\SignText;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use TheNote\core\utils\ChestShopDataManager;

class EconomyChest implements Listener
{
    private $plugin;
    private $chestshop;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->chestshop = (new Config($this->plugin->getDataFolder() . Main::$cloud . "ChestShop.yml", Config::YAML))->getAll();
    }

    /*public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = new BaseAPI();
        if ($block instanceof BaseSign) {

            if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK && $player->isSneaking()) return;
            if (($shopInfo = $this->databaseManager->selectByCondition([
                    "signX" => $block->getPosition()->getX(),
                    "signY" => $block->getPosition()->getY(),
                    "signZ" => $block->getPosition()->getZ()
                ])) === false) return;
            $shopInfo = $shopInfo->fetchArray(SQLITE3_ASSOC);
            if ($shopInfo === false)
                return;
            if ($shopInfo['shopOwner'] === $player->getName()) {
                $player->sendTip($api->getSetting("error") . "§cDu kannst nicht bei dir selbst kaufen!");
                return;
            } else {
                $event->cancel();
            }
            $buyerMoney = $api->getMoney($player->getName());
            if ($buyerMoney === false) {
                $player->sendMessage($api->getSetting("error") . "§cFehler in der Matrix woops");
                return;
            }
            if ($buyerMoney < $shopInfo['price']) {
                $player->sendTip($api->getSetting("error") . "§cDu hast zu wenig Geld!");
                return;
            }
            $chest = $player->getWorld()->getTile(new Vector3($shopInfo['chestX'], $shopInfo['chestY'], $shopInfo['chestZ']));
            $itemNum = 0;
            $pName = $shopInfo['productName'];
            for ($i = 0; $i < $chest->getInventory()->getSize(); $i++) {
                $item = $chest->getInventory()->getItem($i);
                if ($item->getName() === $pName) $itemNum += $item->getCount();
            }
            if ($itemNum < $shopInfo['saleNum']) {
                $player->sendTip($api->getSetting("error") . "§cDieser Shop ist leider Ausverkauft!");
                if (($p = $this->plugin->getServer()->getPlayerExact($shopInfo['shopOwner'])) !== null) {
                    $itemName = StringToItemParser::getInstance()->parse($pName)->getName();
                    $p->sendMessage($api->getSetting("error") . "§cDein Shop ist leider Leer! Bitte fülle ihn mit auf! §f:§e " . $itemName);
                }
                return;
            }
            $item = StringToItemParser::getInstance()->parse($shopInfo['productName'])->setCount((int)$shopInfo['saleNum']);
            $chest->getInventory()->removeItem($item);
            $player->getInventory()->addItem($item);
            $sellerMoney = $api->getMoney($shopInfo['shopOwner']);
            $target = $shopInfo['shopOwner'];

            if ($api->removeMoney($player, (int)$shopInfo['price']) === $api->addMoney($target, (int)$shopInfo['price'])) {
                $player->sendTip($api->getSetting("money") . "§dDer Einkauf war erfolgreich!");
                if (($p = $this->plugin->getServer()->getPlayerExact($shopInfo['shopOwner'])) !== null) {

                    $itemName = StringToItemParser::getInstance()->parse($pName)->getName();
                    $p->sendTip($api->getSetting("money") . "§e{$player->getName()} §dhat von dir §e" . $itemName . " §dfür§e " . $shopInfo['price'] . "§e$ §dgekauft!");
                }
            } else {
                $player->getInventory()->removeItem($item);
                $chest->getInventory()->addItem($item);
                $api->setMoney($player, $buyerMoney);
                $api->setMoney($shopInfo['shopOwner'], $sellerMoney);
                $player->sendTip($api->getSetting("error") . "§cDer Kauf ist Fehlgeschlagen!");
            }
        }

        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) {
            $shopInfo = $this->databaseManager->selectByCondition([
                "chestX" => $block->getPosition()->getX(),
                "chestY" => $block->getPosition()->getY(),
                "chestZ" => $block->getPosition()->getZ()
            ]);
            if ($shopInfo === false) return;
            $shopInfo = $shopInfo->fetchArray(SQLITE3_ASSOC);
            if ($shopInfo !== false and $shopInfo['shopOwner'] !== $player->getName() and !$player->hasPermission("core.economy.chestshop.admin")) {
                $player->sendMessage($api->getSetting("error") . "§cDieser Shop ist geschützt! Du hast keine Berechtigung dafür!");
                $event->cancel();
            }
        }

        $api = new BaseAPI();
        $block = $event->getBlock();
        $position = $block->getPosition();
        if ($block instanceof BaseSign) {
            if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK && $player->isSneaking()) return;
            if ($shopInfo['shopOwner'] === $player->getName()) {
                $player->sendTip($api->getSetting("error") . "§cDu kannst nicht bei dir selbst kaufen!");
                return;
            } else {
                $event->cancel();
            }
            $loc = $position->getX() . ":" . $position->getY() . ":" . $position->getZ() . ":" . $event->getPlayer()->getWorld()->getFolderName();
            if (isset($this->chestshop[$loc])) {
                $chestshop = $this->chestshop[$loc];
                $player = $event->getPlayer();

                if (!$player->getInventory()->canAddItem(StringToItemParser::getInstance()->parse($chestshop['itemName'])->setCount((int)$chestshop['amount']))) {
                    $player->sendTip($api->getSetting("error") . "§cDein Inventar ist voll! Leere es bevor du was Kaufst");
                    return;
                }

                $geld = $api->getMoney($player->getName());
                if ($chestshop["price"] > $geld) {
                    $player->sendTip($api->getSetting("error") . "§cDu hast zu wenig geld um dir was zu kaufen!" );
                } else {
                    $now = microtime(true);
                    if (!isset($this->tap[$player->getName()]) or $now - $this->tap[$player->getName()][1] >= 1.5 or $this->tap[$player->getName()][0] !== $loc) {
                        $this->tap[$player->getName()] = [$loc, $now];
                        $player->sendTip($api->getSetting("money") . "§cDrücke erneut um was zu kaufen!");
                        return;
                    } else {
                        unset($this->tap[$player->getName()]);
                    }
                    $signshop = StringToItemParser::getInstance()->parse($chestshop['itemName'])->setCount((int)$chestshop['amount']);
                    $player->getInventory()->addItem($signshop);
                    $api->removeMoney($player, $chestshop["price"]);
                    $player->sendTip($api->getSetting("money") . "§6Du hast erfolgreich was gekauft!");
                }
                $event->cancel();
                if ($event->getItem()->canBePlaced()) {
                    $this->placeQueue[$player->getName()] = true;
                }
            }
        }
    }*/

    public function onPlayerBreakBlock(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = new BaseAPI();
        $cfg = new Config($this->plugin->getDataFolder() . Main::$cloud . "ChestShop.yml", Config::YAML);
        if ($block instanceof BaseSign) {
            //$cfg->set($shopOwner->getName(), "{$cx}:{$cy}:{$cz}:{$world}:{$x}:{$y}:{$z}:{$amount}:{$item}:{$price}");
            //if ($this->existthis($player->getName())) {
                $pos = explode(" ", $cfg->get($player->getName()));
                $name = $pos[0];
                $signx = $pos[1];
                $signy = $pos[2];
                $signz = $pos[3];
                //$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($pos[4]);
                var_dump( $name . ":" . $signx . ":" . $signy . ":" . $signz . ":" . $pos[4]);
                    if (!$name == $player->getName() or $player->hasPermission("core.economy.chestshop.admin")) {
                        $player->sendMessage($api->getSetting("error") . "§cDieser Shop ist geschützt! Du hast keine Berechtigung dafür!");
                        $event->cancel();
                        return;
                    } else {
                        $this->chestshop[$player->getName() . ":" . $block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getFolderName()] = null;
                        unset($this->chestshop[$player->getName() . ":" . $block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getFolderName()]);
                        $player->sendMessage($api->getSetting("money") . "§dDein Shop wurde geschlossen!");
                    }

            //}
        }

        /*if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) {
            $condition = [
                "chestX" => $block->getPosition()->getX(),
                "chestY" => $block->getPosition()->getY(),
                "chestZ" => $block->getPosition()->getZ()
            ];
            $shopInfo = $this->databaseManager->selectByCondition($condition);
            if ($shopInfo !== false) {
                $shopInfo = $shopInfo->fetchArray();
                if ($shopInfo === false) return;
                if ($shopInfo['shopOwner'] !== $player->getName() and !$player->hasPermission("core.economy.chestshop.admin")) {
                    $player->sendMessage($api->getSetting("error") . "§cDieser Shop ist geschützt! Du hast keine Berechtigung dafür!");
                    $event->cancel();
                } else {
                    $this->databaseManager->deleteByCondition($condition);
                    $player->sendMessage($api->getSetting("money") . "§dDein Shop wurde geschlossen!");
                }
            }
        }*/
    }
    public function existthis(string $player): bool
    {
        $back = new Config(Main::getInstance()->getDataFolder() . Main::$cloud . "ChestShop.yml", Config::YAML);
        return $back->exists($player);
    }

    public function onSignChange(SignChangeEvent $event): void
    {
        $shopOwner = $event->getPlayer();
        $signText = $event->getNewText();
        $amount = (int)$signText->getLine(1);
        $price = (int)$signText->getLine(2);
        $productData = $signText->getLine(3);
        $pName = StringToItemParser::getInstance()->parse($productData) ?? LegacyStringToItemParser::getInstance()->parse($productData);
        $sign = $event->getBlock();
        if ($signText->getLine(0) !== "") return;
        if (!is_numeric($amount) or $amount <= 0) return;
        if (!is_numeric($price) or $price < 0) return;
        if ($pName === false) return;
        if (($chest = $this->getSideChest($sign->getPosition())) === false) return;
        if (empty($signText->getLine(3))) return;


        $event->setNewText(new SignText([
            "§6" . $shopOwner->getName(),
            "§7[§8Anzahl: §b" . $amount . "§7]",
            "§7[§8Preis: §b" . $price . "§7]",
            "§e" . $pName->getName()
        ]));

        $this->registerShop($shopOwner, $amount, $price, $pName->getName(), $sign, $chest);
    }


    private function getSideChest(Position $pos): Block|bool
    {
        $block = $pos->getWorld()->getBlock(new Vector3($pos->getX() + 1, $pos->getY(), $pos->getZ()));
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) return $block;
        $block = $pos->getWorld()->getBlock(new Vector3($pos->getX() - 1, $pos->getY(), $pos->getZ()));
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) return $block;
        $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY() - 1, $pos->getZ()));
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) return $block;
        $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY(), $pos->getZ() + 1));
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) return $block;
        $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY(), $pos->getZ() - 1));
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) return $block;
        return false;
    }

    public function registerShop(Player $shopOwner, int $amount, int $price, $item, Block $sign, Block $chest): bool
    {
        $x = $sign->getPosition()->getFloorX();
        $y = $sign->getPosition()->getFloorY();
        $z = $sign->getPosition()->getFloorZ();
        $cx = $chest->getPosition()->getFloorX();
        $cy = $chest->getPosition()->getFloorY();
        $cz = $chest->getPosition()->getFloorZ();
        $world = $shopOwner->getWorld()->getFolderName();
        /*$this->chestshop[$shopOwner->getName() . ":" . $cx . ":" . $cy . ":" . $cz . ":" . $shopOwner->getWorld()->getFolderName()] = array(
            "signX" => $x,
            "signY" => $y,
            "signZ" => $z,
            "chestX" => $cx,
            "chestY" => $cy,
            "chestZ" => $cz,
            "world" => $shopOwner->getWorld()->getFolderName(),
            "price" => $price,
            "itemName" => $item,
            "amount" => $amount
        );*/

        $cfg = new Config($this->plugin->getDataFolder() . Main::$cloud . "ChestShop.yml", Config::YAML);
        $cfg->set($shopOwner->getName(), "{$cx} {$cy} {$cz} {$world} {$x} {$y} {$z} {$amount} {$item} {$price}");
        //$cfg->setAll($this->chestshop);
        $cfg->save();
        return true;
    }
}
