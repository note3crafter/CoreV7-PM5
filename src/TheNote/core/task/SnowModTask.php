<?php

namespace TheNote\core\task;

use pocketmine\scheduler\Task;
use TheNote\core\Main;

class SnowModTask extends Task
{
    private Main $player;

    function __construct(Main $player)
    {
        $this->player = $player;
    }

    public function onRun(): void
    {
        $player = $this->player;
        $player->SnowMod();
    }
}