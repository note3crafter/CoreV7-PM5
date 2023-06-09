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

use muqsit\invmenu\InvMenu;
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\player\Player;
use TheNote\core\BaseAPI;
use TheNote\core\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class CraftCommand extends Command
{
	private $plugin;

	public static function WORKBENCH() : InvMenu{
		return InvMenu::create(Main::INV_MENU_TYPE_WORKBENCH);
	}
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		$api = new BaseAPI();
		parent::__construct("craft", $api->getSetting("prefix") . $api->getLang("craftprefix"), "/craft", ["crafting"]);
		$this->setPermission("core.command.craft");

	}
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $api = new BaseAPI();
        if (!$sender instanceof Player) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("commandingame"));
            return false;
        }
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($api->getSetting("error") . $api->getLang("nopermission"));
            return false;
        }
        //$sender->setCurrentWindow(new CraftingTableInventory($sender->getPosition()));

        self::WORKBENCH()->send($sender);
        return true;
    }
    /*public function sendCraftingTable(Player $player)
    {
        $block1 = BlockFactory::get(BlockLegacyIds::CRAFTING_TABLE, 0);
        $block1->x = (int)floor($player->getPosition()->x);
        $block1->y = (int)floor($player->getPosition()->y) - 2;
        $block1->z = (int)floor($player->getPosition()->z);
        $block1->level = $player->getWorld();
        $block1->level->sendBlocks([$player], [$block1]);
    }*/
}
