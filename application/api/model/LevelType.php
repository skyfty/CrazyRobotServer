<?php

namespace app\api\model;

use think\Db;
use think\Model;
class LevelType extends Model
{
    protected $table = 'leveltype';

    /**
     * 获取所有等级类型
     */
    public function getAll()
    {
        return $this->select();
    }
}