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
use app\api\model\Equipment as EquipmentModel;//装备模型
use app\api\model\UpgradeLevel as UpgradeLevelModel;//升级等级模型


//装备类的接口
class Equipment extends Api
{
       protected $noNeedLogin = ['getAllEquipment','getAllUpgradeLevel'];
       protected $noNeedRight = '*';
         /**
       * 获取所有装备升级
       */
    public function getAllUpgradeLevel(){
            $upgradeLevel = new UpgradeLevelModel();
            $upgradeLevelData = $upgradeLevel->getAll();
            if($upgradeLevelData){
                $this->success(__('获取成功'), $upgradeLevelData);
            }else{
                $this->error('获取失败');
            }
       }
    
       /*
       * 装备
       */
    public function EquipEquipment(){
            // post接收参数index、id
            $params = $this->request->param();
            $index = $params['index'];
            $id = $params['id'];

            
            // 判断参数是否有值
            if ($index == null || $id == null) {
                $this->error('缺少必要的参数');
            }   
            
            // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
            
            // 获取用户信息
            $user = $this->auth->getInfo($userId);
            
            if (!$user || !isset($user['equipmentEquippedIndex']) || !is_array($user['equipmentEquippedIndex'])) {  
                $this->error('用户信息或装备信息不存在');
            }  
            
            // 检查索引是否存在
            if (!isset($user['equipmentEquippedIndex'][$index])) {  
                $this->error('指定的装备索引不存在');
            }
            
            // 获取装备索引数组的副本
            $equipmentEquippedIndex = $user['equipmentEquippedIndex'];
            $equipmentEquippedJson = json_encode($equipmentEquippedIndex);  

            // 判断装备是否已经装备在这个位置
            if ($equipmentEquippedIndex[$index]['id'] == $id) {
                $this->success(__('装备已经装备在这个位置'), $equipmentEquippedJson, 0);
            }
            
            // 不能重复装备（检查其他位置）
            if (in_array($id, array_column($equipmentEquippedIndex, 'id'))) {
                $this->success(__('装备已经装备在其他位置'), $equipmentEquippedJson, 0);
            }
            
            // 修改副本
            $equipmentEquippedIndex[$index]['id'] = $id;  
            
            // 将修改后的副本编码为JSON
            $equipmentEquippedJson = json_encode($equipmentEquippedIndex);  
            
            // 更新用户装备信息
            if ($this->auth->updateEquipmentEquipped($userId, $equipmentEquippedJson)) {
                $newUser = $this->auth->getInfo($userId);
                $newequipmentEquippedJson = json_encode($newUser['equipmentEquippedIndex']);  
                $this->success(__('装备成功'), $newequipmentEquippedJson, 1);
            } else {
                $this->error('更新用户装备信息失败');
            }
        }
        /**
         * 升级装备
         */
        public function upgrade(){
             // post接收参数index、id
            $params = $this->request->param();
            $type = $params['type'];//0升级数据 1升级装备
            $id = $params['id'];//升级的装备id
            // 判断参数是否有值
            if ($type == null || $id == null) {
                $this->error('缺少必要的参数');
            }   
                 // 根据Token获取用户ID
            $token = $this->auth->getToken();
            $tokenInfo = \app\common\library\Token::get($token);
            $userId = $tokenInfo['user_id'];
             // 获取用户信息
            $user = $this->auth->getInfo($userId);
            $user['equipment']=json_decode($user['equipment']);
            $user['upgradeData']=json_decode($user['upgradeData']);
            $listDetailsName='upgradeDatas';//升级数据详情
            $listDataName='upgradeData';//升级数据
            $haveGold='0';
            switch ($type) {
                case 0:
                    $listDetailsName='upgradeDatas';
                    $listDataName='upgradeData';
                    break;
                case 1:
                    $listDetailsName='equipmentDetails';
                    $listDataName='equipment';
                    break;
                default:
                    // code...
                    break;
            }
                // $this->success(__('装备成功'), $user, 1);
                try{
                        $haveGold=$this->getDetailsfromid($id,$user[$listDetailsName])['upgradegold'];
                }catch(\Exception $e){
                    $this->error('升级数据不存在');
                }
                
        
             if($user['gold']<$haveGold){
                 $this->success(__('钱不够'), '', 3);
            }
            // 减少用户金钱
            $this->auth->updateGold($userId, $user['gold'] - $haveGold);
            foreach ($user[$listDataName] as $value) {  
                if($value->id==$id){
                    $value->level=++$this->getlevelfromid($id,$user[$listDataName])->level;
                }
            }  
           // 更新升级数据
            $this->auth->updateUpgradeData($userId, json_encode($user[$listDataName]),$listDataName);
            $this->success(__('升级成功'),'', 1);

        }
        private function getDetailsfromid($id,$arr){
            foreach ($arr as $value) {  
                if($value['id']==$id){
                    return $value;
                }
            }  
            return null;
        }
        function getlevelfromid($id,$arr){
            foreach ($arr as $value) {  
                if($value->id==$id){
                    return $value;
                }
            }  
            return null;
        }
        // /**
        //  * 获取所有装备
        //  */
        // public function getAllEquipment(){
        //     $equipmentModel = new EquipmentModel();
        //     $equipmentList = $equipmentModel->getAll();
        //     $this->success(__('获取所有装备成功'), $equipmentList);
        // }
        /**
         * 获取所有详情信息PlayerEquipmentAdd
         */
        // public function PlayerEquipmentAdd(){
        //      // post接收参数level、id
        //     $params = $this->request->param();
        //     //判断是否存在参数
        //     if (!$params['level'] || !$params['id']) {
        //         $this->error('缺少必要的参数');
        //     }   
        //     $level = $params['level'];
        //     $id = $params['id'];
        //           // 根据Token获取用户ID
        //     $token = $this->auth->getToken();
        //     $tokenInfo = \app\common\library\Token::get($token);
        //     $userId = $tokenInfo['user_id'];
        //      // 获取用户信息
        //     $user = $this->auth->getInfo($userId);
        //     $existingEquipment = json_decode($user['equipment'], true); 
        //       // 检查新数据的 id 是否已经存在  
        //     $idExists = false;  
        //     foreach ($existingEquipment as $item) {  
        //         if ($item['id'] == $id) {  
        //             $idExists = true;  
        //             break;  
        //         }  
        //     }
        //      if (!$idExists) { 
        //         // 将新数据添加到现有数据中  
        //         $existingEquipment[] = $newEquipmentItem;   

        //      }  else{
        //         $this->success(__('ID 已存在于 equipment 中'), '', 2);
        //      }
        // }
}
