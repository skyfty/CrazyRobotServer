<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Specialequipments extends Model
{
    protected $table = 'specialequipments'; 

    /**
     * 获取所有特殊装备
     */
    public function getAll($equipmentJson,$equipmentEquipped, $limit_index = 0, $limit_count = -1)
    {
        //json解析
        $equipmentArray = json_decode($equipmentJson, true);
        //判断是否为空
        if (empty($equipmentArray)) {
            return [];
        }
        $equipmentEquippedArray = json_decode($equipmentEquipped, true);

        $data = [];
        if ($limit_count == -1) {
            $allEquipment = $this->select();
        } else {
            $allEquipment = $this->limit($limit_index, $limit_count)->select();
        }

        //遍历数组，查询数据库
        foreach ($allEquipment as $equipment) {
            $id = $equipment['id'];
            // //转换整型
            $id = intval($id);

            foreach($equipmentArray as $equip) {
                if ($equip['id'] == $id) {
                    $equipment['level'] = $equip['level'];
                    break;
                }
            }
            $equipment['isEquipped'] = false;
            foreach ($equipmentEquippedArray as $equip) {
                if ($equip['id'] == $id) {
                    $equipment['isEquipped'] = true;
                    break;
                }   
            }
            $data[] = $equipment;
        }
        return $data;
    }
    /**
     * 获取所有装备
     */
    public function getAllEquipment($limit_index = 0, $limit_count = 10)
    {
        $data = $this->select();
        return $data;
    }
}