<?php
namespace app\api\model;

use think\Model;

class Achievement extends Model
{
    // 表名
    protected $table = 'achievement';
    
    public function clear($user_id)
    {
        $this->where('user_id', $user_id)->delete();
    }
}
