<?php
namespace app\api\controller;
    
use app\common\controller\Api;
use app\api\model\Achievement as AchievementModel;
    
class Achievement extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
       
    public function getItem(){
        //获取第一条数据，如果没数据，则插入一条最初的数据，如果有，则直接返回
        
        // 根据Token获取用户ID
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
            
        $userId = $tokenInfo['user_id'];

        $achievementModel = new AchievementModel();

        $item = AchievementModel::where('user_id',  $userId)->limit(1)->select();
        if (count($item) == 0) {
            AchievementModel::insert(['user_id'=>$userId,'fire_bullet_count'=>0]);
            $item = AchievementModel::where('user_id',  $userId)->limit(1)->select();
        }

       $this->success(__('成功'), $item,  1);
    }
}