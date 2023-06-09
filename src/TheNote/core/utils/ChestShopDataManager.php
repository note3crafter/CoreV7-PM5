<?php

namespace TheNote\core\utils;

use pocketmine\block\Block;
use pocketmine\utils\Config;
use TheNote\core\Main;

class ChestShopDataManager
{
    public function registerShop(string $shopOwner, int $saleNum, int $price, $item, Block $sign, Block $chest) : bool
    {
        $cfg = new Config(Main::$cloud . "ChestShop.json", Config::JSON);
        $cfg->setNested();
        $x = $sign->getPosition()->getFloorX();
        $y = $sign->getPosition()->getFloorY();
        $z = $sign->getPosition()->getFloorZ();
        $cx = $chest->getPosition()->getFloorX();
        $cy = $chest->getPosition()->getFloorY();
        $cz = $chest->getPosition()->getFloorZ();
        return $this->database->exec("INSERT OR REPLACE INTO Main (shopOwner, saleNum, price, item, signX, signY, signZ, chestX, chestY, chestZ) VALUES
			((SELECT item FROM Main WHERE signX = $x AND signY = $y AND signZ = $z),
			'$shopOwner', $saleNum, $price, $item, $x, $y, $z, $cx, $cy, $cz)");
    }

    public function selectByCondition(array $condition) : bool|\SQLite3Result
    {
        $where = $this->formatCondition($condition);
        $res = false;
        try{
            $res = $this->database->query("SELECT * FROM Main WHERE $where");
        }finally{
            return $res;
        }
    }

    public function deleteByCondition(array $condition) : bool
    {
        $where = $this->formatCondition($condition);
        return $this->database->exec("DELETE FROM Main WHERE $where");
    }

    private function formatCondition(array $condition) : string
    {
        $result = "";
        $first = true;
        foreach ($condition as $key => $val) {
            if ($first) $first = false;
            else $result .= "AND ";
            $result .= "$key = $val ";
        }
        return trim($result);
    }
}