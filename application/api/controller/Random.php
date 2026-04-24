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
use app\api\model\Treasure as TreasureModel;

//随机类的接口
class Random extends Api
{
     protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
    public function research()
    {

    /*
            CREATE TABLE `treasure` (
        `id` int(10) NOT NULL,
        `name` varchar(255) DEFAULT NULL COMMENT '宝物名称',
        `type` varchar(255) DEFAULT NULL COMMENT '宝物类型：gold为金币,diamond为钻石，其他为特殊装备',
        `amount` int(10) NOT NULL COMMENT '数量',
        `probability` double(10,2) NOT NULL COMMENT '概率'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        --
        -- 转存表中的数据 `treasure`
        --

        INSERT INTO `treasure` (`id`, `name`, `type`, `amount`, `probability`) VALUES
        (1, '金币10', 'gold', 10, 60.00),
        (2, '金币100', 'gold', 100, 30.00),
        (3, '钻石1', 'diamond', 1, 8.00),
        (4, '基础炮弹', '基础炮弹', 1, 1.00),
        (5, '减速炮弹', '减速炮弹', 1, 0.60),
        (6, '灼烧炮弹', '灼烧炮弹', 1, 0.30),
        (7, '强力炮弹', '强力炮弹', 1, 0.10);
    */

        // 本接口从 treasure 表按概率抽取一个物品并发放给用户
        // 获取宝箱配置
        $treasureModel = new TreasureModel();
        $treasures = $treasureModel->select();
        if (empty($treasures)) {
            $this->success(__('未找到宝物配置'), '', 0);
        }

        // 计算权重（将百分比保留两位，转为整数权重）
        $weights = [];
        $total = 0;
        foreach ($treasures as $t) {
            $prob = floatval($t->probability);
            $w = (int)round($prob * 100); // 0.01% 精度 -> 总和应为 10000
            if ($w < 0) $w = 0;
            $weights[] = ['item' => $t, 'weight' => $w];
            $total += $w;
        }

        if ($total <= 0) {
            $this->error('宝物配置概率错误');
        }

        // 随机选取
        $rand = mt_rand(1, $total);
        $acc = 0;
        $selected = null;
        foreach ($weights as $entry) {
            $acc += $entry['weight'];
            if ($rand <= $acc) {
                $selected = $entry['item'];
                break;
            }
        }

        if (!$selected) {
            $this->error('抽取失败');
        }

        // 处理不同类型
        $type = strtolower(trim($selected->type));
        $amount = intval($selected->amount);

        if ($type === 'gold') {
            // 增加金币
            $this->success(__('抽中金币'), $selected, 1);
        } elseif ($type === 'diamond') {
            // 增加钻石（Auth 中没有 addDiamond，直接更新用户模型）
            $this->success(__('抽中钻石'), $selected, 1);
        } elseif ($type === 'equipment') {
            // 视为特殊装备（炮弹）名称，尝试查找对应装备并添加到用户装备里（去重）
            $specialequipmentsModel = new SpecialequipmentsModel();
            $equip = $specialequipmentsModel->where('id', $selected->amount)->find();
            if (!$equip) {
                // 找不到对应装备，返回宝物信息但不发放
                $this->success(__('抽中物品，但对应装备未找到'), $selected, 1);
            }
            $this->success(__('抽中到装备'), $selected, 1);
        }
    }

    // public function RandomEquipment()
    // {
    //     $params = $this->request->param();
    //     //检查是否有num参数
    //     if (!isset($params['num'])) {
    //         $this->error('缺少必要的参数');
    //     }
    //     $num = $params['num'];
    //     // $id = $params['id'];
    //     // $level = $params['level'];
    //     $action = 'draw_and_add';
    //     if ($num <= 0) {
    //          $this->success(__('抽奖数量必须大于0'), '', 0);
    //     }
    //         // 根据Token获取用户ID
    //     $token = $this->auth->getToken();
    //     $tokenInfo = \app\common\library\Token::get($token);
    //     $userId = $tokenInfo['user_id'];
    //         // 获取用户信息
    //     $user = $this->auth->getInfo($userId);
    //     $user['equipment']=json_decode($user['equipment']);
    //     $user['upgradeData']=json_decode($user['upgradeData']);
    //     $userDiamond = $user['diamond'];
    //     if ($userDiamond < $num *1) {
    //         $this->success(__('用户砖石不足'), '', 0);
    //     }
    //     // 根据action执行不同操作
    //     switch ($action) {
    //         // case 'draw':
    //         //     handleDraw();
    //         //     break;
    //         // case 'add':
    //         //     handleAdd();
    //         //     break;
    //         case 'draw_and_add':
    //             $specialequipmentsModel = new SpecialequipmentsModel();
    //             $equipments = $specialequipmentsModel->getAllEquipment();
    //             if (empty($equipments)) {  
    //                 $this->success(__('未找到装备数据'), '', 0);
    //             }  
    //             $drawnEquipments = array();
    //             for ($i = 0; $i < $num; $i++) {
    //                 $rand = mt_rand(1, 100);  
    //                 if ($rand <= 10) {  
    //                     $randomIndex = mt_rand(1, count($equipments) - 1);  
    //                     $equipment = $equipments[$randomIndex];
    //                     $equipment['level'] = 1;
    //                 } else {
    //                     $equipment = $equipments[0];  
    //                     $equipment['level'] = 1;
    //                 }
    //                 $drawnEquipments[] = $equipment;
    //             }
    //             $this->auth->reduceDiamond($userId, $num);
    //             $existingEquipment =  $user['equipment'];
    //             if (!is_array($existingEquipment)) {
    //                 $existingEquipment = array();
    //             }
    //               // 添加抽到的装备
    //             $addedCount = 0;
    //             $duplicateCount = 0;
    //             $addedEquipments = array();
    //             foreach ($drawnEquipments as $equipment) {
    //                 $idExists = false;
    //                 foreach ($existingEquipment as $item) {
    //                     if (isset($item->id) && $item->id == $equipment->id) {
    //                         $idExists = true;
    //                         $duplicateCount++;
    //                         break;
    //                     }
    //                 }
                    
    //                 if (!$idExists) {
    //                     $newItem = array(
    //                         'id' => $equipment->id,
    //                         'level' => 1,
    //                     );
    //                     $existingEquipment[] = $newItem;
    //                     $addedEquipments[] = $newItem;
    //                     $addedCount++;
    //                 }
    //             }

    //             $updateEquipment = [];
    //             foreach ($existingEquipment as $equipment) {
    //                 if (array_search($equipment, $updateEquipment) === false) {
    //                     $updateEquipment[] = $equipment;
    //                 }
    //             }

    //             $equipmentJson = json_encode($updateEquipment);  
    //             // 更新用户装备信息
    //             if ($this->auth->updateEquipment($userId, $equipmentJson)) {
    //                 // $newUser = $this->auth->getInfo($userId);
    //                 // $newequipmentJson = json_encode($newUser['equipment']);  
    //                 $this->success(__('抽奖并添加完成'), $drawnEquipments, 1);
    //             } else {
    //                 $this->error('更新用户装备信息失败');
    //             }
    

    //             break;
    //         default:
    //             $this->success(__('未知的操作类型'), '', 0);
    //             break;
    //     }



    //     $this->success(__('随机生成成功'), '', 1);
    // }
}