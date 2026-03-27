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
    public function getAll($equipmentJson,$equipmentEquipped)
    {
        //json解析
        $equipmentArray = json_decode($equipmentJson, true);
        //判断是否为空
        if (empty($equipmentArray)) {
            return [];
        }
               $equipmentEquippedArray = json_decode($equipmentEquipped, true);

        $data = [];
        //遍历数组，查询数据库
        foreach ($equipmentArray as $equipment) {
            $id = $equipment['id'];
            // //转换整型
            $id = intval($id);
            // $equipment['tt'] = $id;
            //   $data[] = $equipment;
            
            //查询数据库
            $newData = $this->where('id', $id)->find();
            $newData['level'] = $equipment['level'];
            $newData['isEquipped'] = false;

            for ($i = 0; $i < count($equipmentEquippedArray); $i++) {
                if ($equipmentEquippedArray[$i]['id'] == $id) {
                    $newData['isEquipped'] = true;
                    break;
                }
            }

            //  $data[] = $newData;
            // //判断是否存在
            if ($newData) {
                //合并数组
                $data[] = $newData;
            }
        }
        return $data;
    }
    /**
     * 获取所有装备
     */
    public function getAllEquipment()
    {
        $data = $this->select();
        return $data;
    }
}