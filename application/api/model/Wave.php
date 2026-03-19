<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Wave extends Model
{
    protected $table = 'wave';

    /**
     * 获取所有波次
     */
    public function getAll()
    {
        return $this->select();
    }
    
}