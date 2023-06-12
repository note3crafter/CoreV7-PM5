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

use pocketmine\block\utils\SignText;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockPlaceEvent;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use onebone\economyapi\EconomyAPI;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

class EconomyShop implements Listener
{
    private $shop;
    private $placeQueue;
    private Main $plugin;
    private $tap;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->placeQueue = [];
        $this->shop = (new Config($this->plugin->getDataFolder() . Main::$cloud . "Shop.yml", Config::YAML))->getAll();
    }

    public function onSignChange(SignChangeEvent $event): void
    {
        $api = new BaseAPI();
        $result = $this->tagExists($event->getNewText()->getLine(0));
        if ($result !== false) {
            $player = $event->getPlayer();
            if (!$player->hasPermission("core.economy.shop.create")) {
                $player->sendMessage($api->getSetting("error") . "§cDu hast keine Berechtigung um einen Shop zu erstellen!");
                return;
            }
            $signText = $event->getNewText();
            $count = (int)$signText->getLine(2);
            $price = (int)$signText->getLine(1);
            $productData = $signText->getLine(3);
            $item = StringToItemParser::getInstance()->parse($productData) ?? LegacyStringToItemParser::getInstance()->parse($productData);

            if (!is_numeric($count)) {
                $player->sendTip($api->getSetting("error") . "§cDie Menge muss in Zahlen angegeben werden");
                return;
            }
            if (!is_numeric($price)) {
                $player->sendTip($api->getSetting("error") . "§cDer Preis muss in Zahlen angegeben werden");
                return;
            }
            if ($item === null) {
                $player->sendTip($api->getSetting("error") . "§cDas Item wird nicht Unterstützt! §e");
                return;
            }
            $block = $event->getBlock();
            $position = $block->getPosition();
            $this->shop[$position->getX() . ":" . $position->getY() . ":" . $position->getZ() . ":" . $player->getWorld()->getFolderName()] = array(
                "x" => $block->getPosition()->getX(),
                "y" => $block->getPosition()->getY(),
                "z" => $block->getPosition()->getZ(),
                "level" => $block->getPosition()->getWorld()->getFolderName(),
                "price" => (int)$event->getNewText()->getLine(1),
                "itemName" => StringToItemParser::getInstance()->lookupAliases($item)[0],
                "amount" => (int)$event->getNewText()->getLine(2)
            );
            $cfg = new Config($this->plugin->getDataFolder() . Main::$cloud . "Shop.yml", Config::YAML);
            $cfg->setAll($this->shop);
            $cfg->save();
            //$productName = StringToItemParser::getInstance()->parse($pName)->getName();
            $player->sendTip($api->getSetting("money") . "§6Du hast den Verkaufsshop erfolgreich erstellt!");
            $event->setNewText(new SignText([
                0 => $result[0],
                1 => str_replace("{price}", $price, $result[1]),
                2 => str_replace("{amount}", $count, $result[2]),
                3 => str_replace("{item}", $item->getName(), $result[3])
            ]));
        }
    }

    public function onTouch(PlayerInteractEvent $event)
    {
        $api = new BaseAPI();
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return;
        }
        $block = $event->getBlock();
        $position = $block->getPosition();
        $loc = $position->getX() . ":" . $position->getY() . ":" . $position->getZ() . ":" . $event->getPlayer()->getWorld()->getFolderName();
        if (isset($this->shop[$loc])) {
            $shop = $this->shop[$loc];
            $player = $event->getPlayer();
            if ($player->getGamemode()->getEnglishName() === "Creative") {
                $player->sendTip($api->getSetting("error") . "§cDu kannst nicht im Kreativmodus kaufen!");
                $event->cancel();
                return;
            }
            if (!$player->hasPermission("core.economy.shop.buy")) {
                $player->sendTip($api->getSetting("error") . "§cDu hast keine Berechtigung um was zu kaufen!");
                $event->cancel();
                return;
            }
            if (!$player->getInventory()->canAddItem(StringToItemParser::getInstance()->parse($shop['itemName'])->setCount((int)$shop['amount']))) {
                $player->sendTip($api->getSetting("error") . "§cDein Inventar ist voll! Leere es bevor du was Kaufst");
                return;
            }

            $geld = $api->getMoney($player->getName());
            if ($shop["price"] > $geld) {
                $player->sendTip($api->getSetting("error") . "§cDu hast zu wenig geld um dir was zu kaufen!");
            } else {
                $now = microtime(true);
                if (!isset($this->tap[$player->getName()]) or $now - $this->tap[$player->getName()][1] >= 1.5 or $this->tap[$player->getName()][0] !== $loc) {
                    $this->tap[$player->getName()] = [$loc, $now];
                    $player->sendTip($api->getSetting("money") . "§cDrücke erneut um was zu kaufen!");
                    return;
                } else {
                    unset($this->tap[$player->getName()]);
                }
                $signshop = StringToItemParser::getInstance()->parse($shop['itemName']);
                $player->getInventory()->addItem($signshop->setCount((int)$shop["amount"]));
                $api->removeMoney($player, $shop["price"]);
                $player->sendTip($api->getSetting("money") . "§6Du hast erfolgreich was gekauft!");
            }
            $event->cancel();
            if ($event->getItem()->canBePlaced()) {
                $this->placeQueue[$player->getName()] = true;
            }
        }
    }

    public function onPlaceEvent(BlockPlaceEvent $event)
    {
        $username = $event->getPlayer()->getName();
        if (isset($this->placeQueue[$username])) {
            $event->cancel();
            unset($this->placeQueue[$username]);
        }
    }

    public function onBreakEvent(BlockBreakEvent $event)
    {
        $api = new BaseAPI();
        $block = $event->getBlock();
        if (isset($this->shop[$block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getFolderName()])) {
            $player = $event->getPlayer();
            if (!$player->hasPermission("core.economy.shop.remove")) {
                $player->sendTip($api->getSetting("error") . "§cDu hast keine Berechtigung um diesen Shop zu zerstören!");
                $event->cancel();
                return;
            }
            $this->shop[$block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getFolderName()] = null;
            unset($this->shop[$block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getFolderName()]);
            $player->sendTip($api->getSetting("money") . "§6Der Shop wurde erfolgreich entfernt.");
        }
    }

    public function tagExists($tag)
    {
        foreach ($this->plugin->shopSign->getAll() as $key => $val) {
            if ($tag == $key) {
                return $val;
            }
        }
        return false;
    }
}