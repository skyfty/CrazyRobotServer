<?php
namespace app\api\model;

use think\Model;

class Building extends Model
{
    // 表名
    protected $table = 'building';
    
    public function clear($user_id)
    {
        $this->where('user_id', $user_id)->delete();
    }
    
    public function getAll($user_id)
    {
         return $this->where($user_id)->select();
    }
    
    public function addBuilding($user_id, $id, $level, $status, $end_time, $index)
    {
         $this->insert([
             'user_id' => $user_id,
             'id' => $id,
             'level' => $level,
             'status' => $status,
             'end_time' => $end_time,
             'index' => $index,
         ]);
    }
    
    public function getBuilding($user_id, $index)
    {
        return $this->where('user_id', $user_id)->where('index', $index)->find();
    }
    
    public function updateLevel($user_id, $id, $level, $status, $end_time, $index)
    {
        $build = $this->getBuilding($user_id, $index);
        if($build === null)
        {
            $this->addBuilding($user_id, $id, $level, $status, $end_time, $index);
        }
        else
        {
//              var_export($level);
            $build->id = $id;
            $build->index = $index;
            $build->level = $level;
            $build->status = $status;
            $build->end_time = $end_time;
            $build->save();
        }
    }
}