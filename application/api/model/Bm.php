<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Bm extends Model
{
    protected $table = 'bm';

    /**
     * 获取所有bm
     */
    public function getAll()
    {
        return $this->select();
    }

}