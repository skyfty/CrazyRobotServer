<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;
use think\Config;
use think\Validate;
use app\api\model\Skill as SkillModel;//关卡模型


class Skill extends Api
{
       protected $noNeedLogin = ['searchCheckpoint'];
       protected $noNeedRight = '*';

       public function getAllSkill() {
          $skillModel = new SkillModel();
          $skillData = $skillModel->select();
          if($skillData){
               $this->success(__('获取成功'), $skillData);
          }else{
               $this->error('获取失败');
          }
       }

       public function obtain() {
          
            // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];

          $user = \app\common\model\User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $params = $this->request->param();
          //检查是否有gold参数
          if (!isset($params['id'])) {
               $this->error('缺少必要的参数或参数值错误');
          }

            $id = $params['id'];
            $id = intval($id);
            
          $skillModel = new SkillModel();
          $skill = $skillModel->get($id );
          if(!$skill){
               $this->error('获取失败');
          }else{
               $userSkill = $user->skill !== null ? json_decode($user->skill, true) : [];
               foreach ($userSkill as $s) {
                    if ($s['id'] == $id) {
                         $this->error('用户已拥有该技能');
                    }
               }
               $userSkill[] = ['id' => $id, 'level' => 1];
               $user->skill = json_encode($userSkill);
               $user->save();
               $this->success(__('获取技能成功'), ['id' => $id, 'level' => 1]);
          }
       }

       public function upgrade() {
                     // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];

          $user = \app\common\model\User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $params = $this->request->param();

          if (!isset($params['id'])) {
               $this->error('缺少必要的参数或参数值土f错误 {$params}');
          }

          $id = $params['id'];
          $id = intval($id);

          
          $skillModel = new SkillModel();
          $skill = $skillModel->get($id );
          if(!$skill){
               $this->error('获取失败');
          }
          if ($user->gold < $skill->upgradegold) {
               $this->error('用户没有足够的金币');
          }

          $existingSkill = false;
          $userSkill = $user->skill !== null ? json_decode($user->skill, true) : [];
          foreach ($userSkill as &$s) {
               if ($s['id'] === $id) {
                    $s['level'] += 1; // 升级技能
                    $existingSkill = true;
                    break;
               }
          }
          unset($s);
          if (!$existingSkill) {
               $userSkill[] = ['id' => $id, 'level' => 1]; // 如果用户没有该技能，则添加新技能
          } else {
               $user->gold -= $skill->upgradegold; // 扣除金币
          }
          $user->skill = json_encode($userSkill);
          $user->save();
          $this->success(__('技能升级成功'), $userSkill);
       }

       public function setCurrentSkill() {
          
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];

          $user = \app\common\model\User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $params = $this->request->param();

          if (!isset($params['id'])) {
               $this->error('缺少必要的参数或参数值土f错误 {$params}');
          }
          $id = $params['id'];
          $id = intval($id);

          $skillModel = new SkillModel();
          $skill = $skillModel->get($id );
          if(!$skill){
               $this->error('获取失败');
          }

          $userSkill = $user->skill !== null ? json_decode($user->skill, true) : [];
          $hasSkill = false;
          foreach ($userSkill as $s) {
               if ($s['id'] == $id) {
                    $hasSkill = true;
                    break;
               }
          }
          if (!$hasSkill) {
               $this->error('用户没有该技能');
          }
          $user->currentskill = $id;
          $user->save();
          $this->success(__('设置当前技能成功'));

       }
}

