<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;
use think\Config;
use think\Validate;
use app\api\model\CheckPoint as CheckPointModel;//关卡模型
use app\api\model\Wave as WaveModel;//波次模型
use app\api\model\Enemy as EnemyModel;//敌人模型
use app\api\model\LevelType as LevelTypeModel;//关卡类型
use app\api\model\Pass as PassModel;//通过记录模型


class Prop extends Api
{
       protected $noNeedLogin = ['searchCheckpoint'];
       protected $noNeedRight = '*';

       public function item() {
        
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
                // 获取用户信息
            $user = $this->auth->getInfo($userId);

            return $this->success(__('success'), ['signItem' => $user['signItem'],'luckyItem'=>$user['luckyItem']]);
       }

       public function addSignItem() {
            $params = $this->request->param();
             //检查是否有gold参数
            if (!isset($params['count']) || $params['count'] < 0) {
                 $this->error('缺少必要的参数或参数值错误');
            }
            $count = $params['count'];

            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];

            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }
            $user->signItem = isset($user->signItem) ? $user->signItem + $count : $count;
            $user->save();
            return $this->success(__('success'), ['signItem' => $user->signItem]);
       }

       public function addLuckyItem() {
            $params = $this->request->param();
             //检查是否有gold参数
            if (!isset($params['count']) || $params['count'] < 0) {
                 $this->error('缺少必要的参数或参数值错误');
            }
            $count = $params['count'];


            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];


            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }
            $user->luckyItem = isset($user->luckyItem) ? $user->luckyItem + $count : $count;
            $user->save();

            return $this->success(__('success'), ['luckyItem' => $user->luckyItem]);
       }

       public function consumeSignItem() {
        
            $params = $this->request->param();
             //检查是否有gold参数
            if (!isset($params['count']) || $params['count'] < 0) {
                 $this->error('缺少必要的参数或参数值错误');
            }
            $count = $params['count'];
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];

            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }
            $user->signItem = isset($user->signItem) ? $user->signItem - $count : 0;
            if ($user->signItem < 0) {
                $user->signItem = 0; // 确保数量不会变成负数
            }
            $user->save();
            return $this->success(__('success'), ['signItem' => $user->signItem]);
       }

       public function consumeLuckyItem() {
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];

            $params = $this->request->param();
             //检查是否有gold参数
            if (!isset($params['count']) || $params['count'] < 0) {
                 $this->error('缺少必要的参数或参数值错误');
            }
            $count = $params['count'];
            
            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }

            $user->luckyItem = isset($user->luckyItem) ? $user->luckyItem - $count : 0;
            if ($user->luckyItem < 0) {
                $user->luckyItem = 0; // 确保数量不会变成负数
            }
            $user->save();

            return $this->success(__('success'), ['luckyItem' => $user->luckyItem]);
       }

       public function gain() {          
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $params = $this->request->param();
          //检查是否有type参数
          if (!isset($params['type']) || !isset($params['count']) || $params['count'] < 0) {
               $this->error('缺少必要的参数或参数值错误');
          }

          if ($params['type'] == "gold") {
               $user->gold = isset($user->gold) ? $user->gold + $params['count'] : $params['count'];
               $user->save();
               return $this->success(__('success'), ['gold' => $user->gold]);
          } elseif ($params['type'] == "diamond") {
               $user->diamond = isset($user->diamond) ? $user->diamond + $params['count'] : $params['count'];
               $user->save();
               return $this->success(__('success'), ['diamond' => $user->diamond]);
          }  else {
               $this->error('参数type值错误');
          }
       }

       public function consume() {
                    // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $params = $this->request->param();
          //检查是否有type参数
          if (!isset($params['type']) || !isset($params['count']) || $params['count'] < 0) {
               $this->error('缺少必要的参数或参数值错误');
          }

          if ($params['type'] == "gold") {
               $user->gold = isset($user->gold) ? $user->gold - $params['count'] : $params['count'];
               if ($user->gold < 0) {
                    $user->gold = 0; // 确保数量不会变成负数
               }
               $user->save();
               return $this->success(__('success'), ['gold' => $user->gold]);
          } elseif ($params['type'] == "diamond") {
               $user->diamond = isset($user->diamond) ? $user->diamond - $params['count'] : $params['count'];
               if ($user->diamond < 0) {
                    $user->diamond = 0; // 确保数量不会变成负数
               }
               $user->save();
               return $this->success(__('success'), ['diamond' => $user->diamond]);
          }  else {
               $this->error('参数type值错误');
          }

       }

       public function knapsack() {
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }

            $knapsackData = $user->knapsack ? json_decode($user->knapsack, true) : ['signinCard' => 0, 'luckyCard' => 0];
            return $this->success(__('success'), $knapsackData);
       }

       public function updateKnapsack() {
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
            $user = User::get($userId);
            if (!$user) {
                $this->setError('User not found');
                return false;
            }
            $knapsackData = $user->knapsack ? json_decode($user->knapsack, true) : ['signinCard' => 0, 'luckyCard' => 0];

            $params = $this->request->param();
             //检查是否有signinCard和luckyCard参数
            if (isset($params['signinCard']) &&  $params['signinCard'] >=0) {
                $knapsackData['signinCard'] = $params['signinCard'];
            }
            if (isset($params['luckyCard']) &&  $params['luckyCard'] >=0) {
                $knapsackData['luckyCard'] = $params['luckyCard'];
            }
            $user->knapsack = json_encode($knapsackData);
            $user->save();

            return $this->success(__('success'), $knapsackData);
       }

       public function bulletLevel() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $bulletLevelData = $user->bulletLevel ? json_decode($user->bulletLevel, true) : [];
          return $this->success(__('success'), $bulletLevelData);
       }

       public function updateBulletLevel() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $bulletLevelData = $user->bulletLevel ? json_decode($user->bulletLevel, true) : [];

          $params = $this->request->param();
          if (!isset($params['bulletId']) || !isset($params['level']) || $params['level'] < 0) {
               $this->error('缺少必要的参数或参数值错误');
          }
          $bulletId = $params['bulletId'];
          $level = $params['level'];
          $bulletLevelData[$bulletId] = $level;
  
          $user->bulletLevel = json_encode($bulletLevelData);
          $user->save();

          return $this->success(__('success'), $bulletLevelData);
       }

       public function qualityLevel() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $qualityLevelData = $user->qualityLevel ? json_decode($user->qualityLevel, true) : [];
          return $this->success(__('success'), $qualityLevelData);
       }

       public function updateQualityLevel() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $qualityLevelData = $user->qualityLevel ? json_decode($user->qualityLevel, true) : [];
          $qualitys = ['frequency', 'distance', 'accurate', 'defense', 'configuration', 'sequence', 'magazine'];
      
          $params = $this->request->param();
          if (!isset($params['quality']) || !in_array($params['quality'], $qualitys) || !isset($params['level']) || $params['level'] < 0) {
               $this->error('缺少必要的参数或参数值错误');
          }
          $qualityLevelData[$params['quality']] = $params['level'];
          $user->qualityLevel = json_encode($qualityLevelData);
          $user->save();
          return $this->success(__('success'), $qualityLevelData);
       }

       public function skillLevel() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $skillLevelData = $user->skillLevel ? json_decode($user->skillLevel, true) : [];
          return $this->success(__('success'), $skillLevelData);

       }

       public function updateSkillLevel() {
                  // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $skillLevelData = $user->skillLevel ? json_decode($user->skillLevel, true) : [];

          $params = $this->request->param();
          if (!isset($params['skillId']) || !isset($params['level']) || $params['level'] < 0) {
               $this->error('缺少必要的参数或参数值错误');
          }
          $skillId = $params['skillId'];
          $level = $params['level'];
          $skillLevelData[$skillId] = $level;
          $user->skillLevel = json_encode($skillLevelData);
          $user->save();
          return $this->success(__('success'), $skillLevelData);
       }

       public function checkinStatus() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $checkinStatusData = $user->checkinStatus ? json_decode($user->checkinStatus, true) : [];
          return $this->success(__('success'), $checkinStatusData);
       }

       public function updateCheckinStatus() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $checkinStatusData = $user->checkinStatus ? json_decode($user->checkinStatus, true) : [];

          $params = $this->request->param();
          if (!isset($params['date']) || !isset($params['status'])) {
               $this->error('缺少必要的参数');
          }
          $date = $params['date'];
          $status = $params['status'];
          $checkinStatusData[$date] = $status;
          $user->checkinStatus = json_encode($checkinStatusData);
          $user->save();
          return $this->success(__('success'), $checkinStatusData);
       }

       public function tollgate() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }

          $tollgateData = $user->tollgate ? json_decode($user->tollgate, true) : [];
          return $this->success(__('success'), $tollgateData);
       }

       public function updateTollgate() {
          // 根据Token获取用户ID
          $token = $this->auth->getToken();
          $tokenInfo = \app\common\library\Token::get($token);
          $userId = $tokenInfo['user_id'];
          $user = User::get($userId);
          if (!$user) {
               $this->setError('User not found');
               return false;
          }
          $tollgateData = $user->tollgate ? json_decode($user->tollgate, true) : [];

          $params = $this->request->param();
          if (!isset($params['way']) || !isset($params['id']) || !isset($params['time'])) {
               $this->error('缺少必要的参数');
          }

          $tollgateData[$params['way']] = ['id'=>$params['id'], 'time'=>$params['time']];
          $user->tollgate = json_encode($tollgateData);
          $user->save();
          return $this->success(__('success'), $tollgateData);
       }

}
