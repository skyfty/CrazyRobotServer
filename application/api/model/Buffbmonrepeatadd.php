<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Buffbmonrepeatadd extends Model
{
    protected $table = 'buffbmonrepeatadd';

    /**
     * 获取所有buffbmonrepeatadd    
     */
    public function getAll()
    {
        return $this->select();
    }

}