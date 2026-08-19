<?php
namespace app\api\controller;
    
use app\common\controller\Api;
use app\api\model\Achievement as AchievementModel;
use app\api\model\AchievementProgress as AchievementProgressModel;
    
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
        
        $statusItem = AchievementProgressModel::where('user_id',  $userId)->select();
        // 创建新的关联数组结构
        $newStructure = [
            'achievement_item' => $item,
            'progress_item' => $statusItem,
        ];
        $this->success(__('成功'), $newStructure,  1);
    }
    
    
    public function record(){
         $token = $this->auth->getToken();
         $tokenInfo = \app\common\library\Token::get($token);
         $userId = $tokenInfo['user_id'];
         $user = \app\common\model\User::get($userId);
         if (!$user) {
             $this->setError('User not found');
             return false;
         }
         $params = $this->request->param();
         $type = $params['type'];
         $count = $params['count'];
         
         $achievementModel = new AchievementModel();
         $item = $achievementModel::get($userId);
//          var_export($userId);
//          var_export($type === '1');
         $intType = intval($type);
         if($intType === 1){
            //炮弹发射数量
//             var_export($item -> fire_bullet_count);
            $item -> fire_bullet_count += $count;
         }
//          AchievementModel::save();
         $item -> save();
         $newStructure = [
            'type' => $intType,
            'count' => $item -> fire_bullet_count,
         ];
         $this->success(__('保存成功'), $newStructure);
    }
    
    public function getAchievementClaimReward(){
         $token = $this->auth->getToken();
         $tokenInfo = \app\common\library\Token::get($token);

         $userId = $tokenInfo['user_id'];
         
         $user = \app\common\model\User::get($userId);
         if (!$user) {
             $this->setError('User not found');
             return false;
         }

         $params = $this->request->param();
         $id = intval($params['id']);
         $rewardType = intval($params['rewardType']);
         $rewardCount = intval($params['rewardCount']);
         
         if($rewardType == 1){
            //金币
            $user->gold = isset($user->gold) ? $user->gold + $rewardCount : $rewardCount;
            $user->save();
            //记录数据
            $achievementProgressModel = new AchievementProgressModel();
            if(!$achievementProgressModel->checkAchievementProgress($userId, $id)){
                 $achievementProgressModel->addAchievementProgress($userId, $id, 2);
            }
            return $this->success(__('success'), ['id'=>$id, 'status'=>2, 'diamond' => $user->diamond,'gold' => $user->gold]);
         }
         else if($rewardType == 2){
            //钻石         
            $user->diamond = isset($user->diamond) ? $user->diamond + $rewardCount : $rewardCount;
            $user->save();
            //记录数据
            $achievementProgressModel = new AchievementProgressModel();
            if(!$achievementProgressModel->checkAchievementProgress($userId, $id)){
                $achievementProgressModel->addAchievementProgress($userId, $id, 2);
            }
            return $this->success(__('success'), ['id'=>$id, 'status'=>2, 'diamond' => $user->diamond,'gold' => $user->gold]);
         }else{
            $this->error('参数type值错误');
         }
    }
    
    public function updateProgressStatus(){
        
    }
}