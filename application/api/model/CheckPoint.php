<?php

namespace app\api\model;

use think\Db;
use think\Model;
class CheckPoint extends Model
{
    protected $table = 'mission';

    /**
     * 获取所有关卡
     */
    public function getAll()
    {
        return $this->select();
    }
    
    /**
     * 获取指定关卡
     */
    public function getById($id)
    {
        return $this->where('id', $id)->select();
    }
    /**
     * 获取指定关卡
     */
    public function getToId($id)
    {
        return $this->where('id', $id)->find();
    }
}
