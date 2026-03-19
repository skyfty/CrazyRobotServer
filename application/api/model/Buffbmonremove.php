<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buffbmonremove extends Model
{
    protected $table = 'buffbmonremove';

    /**
     * 获取所有buffbmonremove
     */
    public function getAll()
    {
        return $this->select();
    }

}