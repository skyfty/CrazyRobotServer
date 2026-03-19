<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;
use think\Config;
use think\Validate;
use app\api\model\CheckPoint as CheckPointModel;//关卡模型
use app\api\model\Wave as WaveModel;//波次模型
use app\api\model\Enemy as EnemyModel;//敌人模型
use app\api\model\LevelType as LevelTypeModel;//关卡类型模型
use app\api\model\Buff as BuffModel;//buff模型
use app\api\model\Buffs as BuffsModel;//buffs模型
use app\api\model\Bm as BmModel;//bm模型
use app\api\model\Buffbmoncreate as BuffbmoncreateModel;//buffbmoncreate模型
use app\api\model\Buffbmonremove as BuffbmonremoveModel;//buffbmonremove模型
use app\api\model\Buffbmontick as BuffbmontickModel;//buffbmontick模型
use app\api\model\Buffbmonrepeatadd as BuffbmonrepeataddModel;//buffbmonrepeatadd模型



class Buff extends Api
{
    protected $noNeedLogin = ['getBuff','searchBuff'];
    protected $noNeedRight = ['*'];
    /**
     * 获取所有buff
     */
    public function getBuff(){
        $buffModel = new BuffModel();
        $buffList = $buffModel->getAll();
        $this->success(__('获取所有buff成功'), $buffList);
    }
    /**
     * 搜索buff
     */
    public function searchBuff(){
        $bmModel = new BmModel();
        $bm = $bmModel->getAll();
        $buffsModel = new BuffsModel();
        $buffs = $buffsModel->getAll();
        $oncreateModel = new BuffbmoncreateModel();
        $oncreate = $oncreateModel->getAll();
        $onremoveModel = new BuffbmonremoveModel();
        $onremove = $onremoveModel->getAll();
        $ontickModel = new BuffbmontickModel();
        $ontick = $ontickModel->getAll();
        $onrepeataddModel = new BuffbmonrepeataddModel();
        $onrepeatadd = $onrepeataddModel->getAll();
        // 处理数据，使用临时数组来存储更新后的buffs  
        $updatedBuffs = [];  
        foreach ($buffs as $buff) {  
            $updatedBuff = $buff;  
            $oncreateItems = array_filter($oncreate, function ($item) use ($buff) {  
                return $item['buff_id'] == $buff['id'];  
            });  
            $onremoveItems = array_filter($onremove, function ($item) use ($buff) {  
                return $item['buff_id'] == $buff['id'];  
            });  
            $ontickItems = array_filter($ontick, function ($item) use ($buff) {  
                return $item['buff_id'] == $buff['id'];  
            });  
            $onrepeataddItems = array_filter($onrepeatadd, function ($item) use ($buff) {  
                return $item['buff_id'] == $buff['id'];  
            });  
            
            $updatedBuff['OnCreate'] = [];  
            $updatedBuff['OnRemove'] = []; 
            $updatedBuff['OnTick'] = []; 
            $updatedBuff['OnRepeatAdd'] = [];
        
            // 处理OnCreate  
            $this->processBuffEvents($updatedBuff, $oncreateItems, 'OnCreate', $bm);  
        
            // 处理OnRemove，注意这里使用$onRemoveItem  
            $this->processBuffEvents($updatedBuff, $onremoveItems, 'OnRemove', $bm);  

            $this->processBuffEvents($updatedBuff,$ontickItems,'OnTick',$bm);
            $this->processBuffEvents($updatedBuff,$onrepeataddItems,'OnRepeatAdd',$bm);
        
            $updatedBuffs[] = $updatedBuff;  
        }  
        $this->success(__('获取所有buffs成功'), $updatedBuffs); 
    }


        // 处理buff的OnCreate和OnRemove事件  
    private function processBuffEvents(&$updatedBuff, $items, $eventType, $bm) {  
        $result = [];  
        foreach ($items as $item) {  
            $bmItem = $this->findById($bm, $item['bm_id']);  
            if ($bmItem) {  
                $result[] = [

                    'bm_id' => $item['bm_id'],  
                    'name' => $bmItem['name'],  
                    'hp' => $bmItem['hp'],  
                    'atk' => $bmItem['atk'],  
                    'speed' => $bmItem['speed'],  
                    'typeid' => $bmItem['typeid'],  
                    'stackNum' => $bmItem['stackNum'],  
                ]; 
            }  
        }  
        $updatedBuff[$eventType] = $result;  
    }  
     // 辅助函数，用于查找ID对应的元素  
    private function findById($array, $id, $key = 'id') {  
        foreach ($array as $item) {  
            if ($item[$key] == $id) {  
                return $item;  
            }  
        }  
        return null;  
    }  

}
