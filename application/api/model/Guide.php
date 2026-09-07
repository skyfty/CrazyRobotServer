<?php
namespace app\api\model;

use think\Model;

class Guide extends Model
{
    // 表名
    protected $table = 'guide';
    
    public function clear($user_id)
    {
        $this->where('user_id', $user_id)->delete();
    }
    
    public function getAll($user_id)
    {
        return $this->where($user_id)->select();
    }
    
    public function insertGuide($user_id, $id)
    {
         $this->insert([
             'user_id' => $user_id,
             'id' => $id,
         ]);
    }
    
    public function getGuide($user_id, $id)
    {
        return $this->where('user_id', $user_id)->where('id', $id)->find();
    }
    
    public function addGuide($user_id, $id)
    {
        $build = $this->getGuide($user_id, $id);
        if($build === null)
        {
            $this->insertGuide($user_id, $id);
        }
//         $build = $this->getGuide($user_id, $id);
//         return $build;
    }
}