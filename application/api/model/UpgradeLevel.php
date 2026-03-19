<?php

namespace app\api\model;

use think\Db;
use think\Model;
class UpgradeLevel extends Model
{
    protected $table = 'upgradeLevel'; 
    //获取所有信息
    public function getAll()
    {
        return $this->select();
    }
}
