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


class Gunner extends Api
{
       protected $noNeedLogin = ['searchCheckpoint'];
       protected $noNeedRight = '*';

       /**
        * 获取关卡
        */
       public function searchCheckpoint(){
           //接收post参数id
           $id = $this->request->param('id');
           //判断是否存在
           if (!$id) {
                $id = 0;
            //    $this->error(__('Please enter the level id', 'id'));
           }
           //实例化模型
           $checkPointModel = new CheckPointModel();
            $rcheckpoints =[];
           if($id==0){
               //获取所有关卡
               $rcheckpoints = $checkPointModel->getAll();
           }else{
               //获取指定关卡
               $rcheckpoints = $checkPointModel->getById($id);
           }
           $waveModel = new WaveModel();
           $waves = $waveModel->getAll();
           $enemyModel = new EnemyModel();
           $enemies = $enemyModel->getAll();
           $result = [];
           foreach($rcheckpoints as $checkpoint){
                 // 初始化一个临时数组来存储当前 checkpoint 的 waves
                $wavesList = $checkpoint->waves ?? []; // 获取现有的 waves 数组，可能为空
                 foreach ($waves as $wave) {
                    if ($wave->missionId == $checkpoint->id) {
                        //给monster数组初始化
                        $monsters = $wave->monster ?? [];
                        $monsters = json_decode($monsters, true);
                        $enemies = [];
                        //遍历monster数组
                        foreach($monsters as $key => $monster){
                             $enemy = $enemyModel->getById($monster['id']);
                                // echo $enemy->toJson();
                                $enemy['count'] = $monster['count'];
                                $enemies[] = $enemy;
                           
                        }
                        //移除wave中的monster
                        unset($wave->monster);
                        $wave->enemies = $enemies;
                        $wavesList[] = $wave;


                        //   $enemiesList = $wave->enemies ?? []; // 获取当前值（可能是 null 或数组）
                        // foreach ($enemies as $enemy) {
                        //     if ($enemy->wave_id == $wave->id) {
                        //          $enemiesList[] = $enemy; // 向临时数组添加对象
                        //     }
                        // }
                        // $wave->enemies = $enemiesList;
                        // $wavesList[] = $wave;
                    }
                }
                  // 将最终的 waves 列表赋给 checkpoint 的 waves 属性
                $checkpoint->waves = $wavesList;
                $result[] = $checkpoint;
           }
        //    //实例化模型
           $levelTypeModel = new LevelTypeModel();
           $levelTypes = $levelTypeModel->getAll();
           foreach($levelTypes as $levelType){
                    $levelType->type = $levelType->id;
                  //移除id
                  unset($levelType->id);
                //   $levelType->data = [];
                $dataList = [];
                   foreach($result as $item){
                        // echo $item['type'] . '<br>';
                        if($item->type == $levelType->type){
                            $dataList[] = $item;
                        }
                    }
                    $levelType->data = $dataList;
           }
        //返回
        $this->success(__('success'), $levelTypes);
       }
       /**
        * 奖励
        */
       public function Reward(){
            //post接收gold、checkpointId、shouldUpdateCheckpoint参数
            $params = $this->request->param();
             //检查是否有gold参数
            if (!isset($params['gold']) || $params['gold'] < 0 || !isset($params['checkpointId']) ) {
                $this->error('缺少必要的参数或参数值错误');
            }
            $gold = $params['gold'];
            $checkpointId = $params['checkpointId'];
            // $shouldUpdateCheckpoint = $params['shouldUpdateCheckpoint'];
            if($gold==0){
                 $this->success(__('更新成功，奖励已添加。'), '', 1);
            }
             // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
                // 获取用户信息
            $user = $this->auth->getInfo($userId);
            $checkPointModel = new CheckPointModel();
            $checkpoint = $checkPointModel->getToId($checkpointId);
            $type = $checkpoint['type'];
            $sortId = $checkpoint['sortId'];
            $levelTypeModel = new LevelTypeModel();
            $levelTypes = $levelTypeModel->getAll();
            $passModel = new PassModel();
            if(!$passModel->checkPass($userId, $checkpointId)){
                $passModel->addPass($userId, $checkpointId, $type, $sortId);
            }
            // $currentCheckpointId = $user['passCheckpointId'];  
            // $shouldUpdateCheckpointValue = ($checkpointId > $currentCheckpointId); // 检查是否需要更新checkpointId  
            //更新用户gold
             $this->auth->updateAddGold($userId, $gold);

            $this->success(__('更新成功，奖励已添加。'), '', 1);
       }
}
