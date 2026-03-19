<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buffbmontick extends Model
{
    protected $table = 'buffbmontick';

    /**
     * 获取所有buffbmontick 
     */
    public function getAll()
    {
        return $this->select();
    }

}