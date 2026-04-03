<?php

namespace app\api\controller;

use app\common\controller\Api;

use think\Config;
use think\Validate;
use app\api\model\CheckPoint as CheckPointModel;//关卡模型
use app\api\model\Wave as WaveModel;//波次模型
use app\api\model\Enemy as EnemyModel;//敌人模型
use app\api\model\LevelType as LevelTypeModel;//关卡类型
use app\api\model\Specialequipments as SpecialequipmentsModel;//特殊装备模型

//随机类的接口
class Random extends Api
{
     protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    public function RandomEquipment()
    {
        $params = $this->request->param();
        //检查是否有num参数
        if (!isset($params['num'])) {
            $this->error('缺少必要的参数');
        }
        $num = $params['num'];
        // $id = $params['id'];
        // $level = $params['level'];
        $action = 'draw_and_add';
        if ($num <= 0) {
             $this->success(__('抽奖数量必须大于0'), '', 0);
        }
            // 根据Token获取用户ID
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $userId = $tokenInfo['user_id'];
            // 获取用户信息
        $user = $this->auth->getInfo($userId);
        $user['equipment']=json_decode($user['equipment']);
        $user['upgradeData']=json_decode($user['upgradeData']);
        $userDiamond = $user['diamond'];
        if ($userDiamond < $num *1) {
            $this->success(__('用户砖石不足'), '', 0);
        }
        // 根据action执行不同操作
        switch ($action) {
            // case 'draw':
            //     handleDraw();
            //     break;
            // case 'add':
            //     handleAdd();
            //     break;
            case 'draw_and_add':
                $specialequipmentsModel = new SpecialequipmentsModel();
                $equipments = $specialequipmentsModel->getAllEquipment();
                if (empty($equipments)) {  
                    $this->success(__('未找到装备数据'), '', 0);
                }  
                $drawnEquipments = array();
                for ($i = 0; $i < $num; $i++) {
                    $rand = mt_rand(1, 100);  
                    if ($rand <= 10) {  
                        $randomIndex = mt_rand(1, count($equipments) - 1);  
                        $equipment = $equipments[$randomIndex];
                        $equipment['level'] = 1;
                    } else {
                        $equipment = $equipments[0];  
                        $equipment['level'] = 1;
                    }
                    $drawnEquipments[] = $equipment;
                }
                $this->auth->reduceDiamond($userId, $num);
                $existingEquipment =  $user['equipment'];
                if (!is_array($existingEquipment)) {
                    $existingEquipment = array();
                }
                  // 添加抽到的装备
                $addedCount = 0;
                $duplicateCount = 0;
                $addedEquipments = array();
                foreach ($drawnEquipments as $equipment) {
                    $idExists = false;
                    foreach ($existingEquipment as $item) {
                        if (isset($item->id) && $item->id == $equipment->id) {
                            $idExists = true;
                            $duplicateCount++;
                            break;
                        }
                    }
                    
                    if (!$idExists) {
                        $newItem = array(
                            'id' => $equipment->id,
                            'level' => 1,
                        );
                        $existingEquipment[] = $newItem;
                        $addedEquipments[] = $newItem;
                        $addedCount++;
                    }
                }

                $updateEquipment = [];
                foreach ($existingEquipment as $equipment) {
                    if (array_search($equipment, $updateEquipment) === false) {
                        $updateEquipment[] = $equipment;
                    }
                }

                $equipmentJson = json_encode($updateEquipment);  
                // 更新用户装备信息
                if ($this->auth->updateEquipment($userId, $equipmentJson)) {
                    // $newUser = $this->auth->getInfo($userId);
                    // $newequipmentJson = json_encode($newUser['equipment']);  
                    $this->success(__('抽奖并添加完成'), $drawnEquipments, 1);
                } else {
                    $this->error('更新用户装备信息失败');
                }
    

                break;
            default:
                $this->success(__('未知的操作类型'), '', 0);
                break;
        }



        $this->success(__('随机生成成功'), '', 1);
    }
}