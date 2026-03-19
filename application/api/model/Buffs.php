<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buffs extends Model
{
    protected $table = 'buffs';

    /**
     * 获取所有buffs
     */
    public function getAll()
    {
        return $this->select();
    }

}