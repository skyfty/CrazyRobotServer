<?php
namespace app\api\model;

use think\Model;

class AchievementProgress extends Model
{
    // 表名
    protected $table = 'achievementprogress';
    
    public function checkAchievementProgress($user_id, $achievement_id)
    {
        return $this->where('user_id', $user_id)->where('achievement_id', $achievement_id)->find()!=null;
    }
    
    public function addAchievementProgress($user_id, $achievement_id, $status)
    {
         $this->insert([
             'user_id' => $user_id,
             'achievement_id' => $achievement_id,
             'status' => $status,
         ]);
    }
    
    public function clear($user_id)
    {
         var_export($user_id);
        $this->where('user_id', $user_id)->delete();
    }
}
