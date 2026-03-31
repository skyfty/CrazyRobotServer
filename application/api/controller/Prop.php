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

}
