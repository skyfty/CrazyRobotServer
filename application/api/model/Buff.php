<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buff extends Model
{
    protected $table = 'buff';

    /**
     * 获取所有buff
     */
    public function getAll()
    {
        return $this->select();
    }

}