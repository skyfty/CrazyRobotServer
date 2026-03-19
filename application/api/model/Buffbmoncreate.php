<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buffbmoncreate extends Model
{
    protected $table = 'buffbmoncreate';

    /**
     * 获取所有buffbmoncreate
     */
    public function getAll()
    {
        return $this->select();
    }

}