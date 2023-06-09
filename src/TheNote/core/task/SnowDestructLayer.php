<?php

namespace TheNote\core\task;

use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use TheNote\core\Main;

class SnowDestructLayer extends Task
{
    public $position;
    private Main $player;

    function __construct(Main $player, Position $position)
    {
        $this->player = $player;
        $this->position = $position;
    }

    public function onRun(): void
    {
        $player = $this->player;
        $player->SnowDestruct($this->position);
    }
}