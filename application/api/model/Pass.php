<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Pass extends Model
{
    protected $table = 'pass';

    /**
     * 获取用户的所有通过记录
     */
    public function getAll($user_id)
    {
        return $this->where('user_id', $user_id)->select();
    }
    /**
     * 判断用户是否通过了该关卡，返回bool值
     */
    public function checkPass($user_id, $checkpoint_id)
    {
        return $this->where('user_id', $user_id)->where('checkpoint_id', $checkpoint_id)->find()!=null;
    }
    /**
     * 添加用户通过记录
     */
    public function addPass($user_id, $checkpoint_id, $type, $sortId)
    {
        $this->insert([
            'user_id' => $user_id,
            'checkpoint_id' => $checkpoint_id,
            'type' => $type,
            'create_time' => date('Y-m-d H:i:s'),
            'sortId' => $sortId,
        ]);
    }
}