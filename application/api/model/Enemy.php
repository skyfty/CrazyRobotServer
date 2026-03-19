<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Enemy extends Model
{
    protected $table = 'enemy';

    /**
     * 获取所有敌人
     */
    public function getAll()
    {
        return $this->select();
    }
    /**
     * 根据id获取敌人
     */
    public function getById($id)
    {
        return $this->where('id', $id)->find();
    }
    
}