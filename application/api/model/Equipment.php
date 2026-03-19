<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Equipment extends Model
{
    protected $table = 'equipment';

    /**
     * 获取所有装备
     */
    public function getAll()
    {
        return $this->select();
    }

}