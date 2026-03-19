<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Upgrade extends Model
{
    protected $table = 'upgrade'; 

    /**
     * 获取所有升级记录
     */
    public function getAll($Json)
    {
        //json解析
         $Array  = json_decode($Json, true);
          $details = [];
        //判断是否为空
        if (empty($Array)) {
            return [];
        }
       
        $data = [];
        //遍历数组，查询数据库
        foreach ($Array as $value) {
            $id = $value['id'];
            // //转换整型
            $id = intval($id);
            // $equipment['tt'] = $id;
            //   $data[] = $equipment;
            
            //查询数据库
            $newData = $this->where('id', $id)->find();
            //  $data[] = $newData;
            // //判断是否存在
            if ($newData) {
                $newData['level'] =$value['level'];
                //合并数组
               //$combined = array_merge($newData, $value);
                $data[] = $newData;
            }
        }
        return $data;
    }
      public function getUserUpgradeDatas($Json)
      {
         $Array = json_decode($Json, true);  
        $details = [];  
       foreach ($Array as $value) {  
                $id = $value['id'];
                $item = $this->where('id', $id)->find();//$this->getUpgradeData($value['id']); // 使用 $this 调用类内方法  
                $item['level'] =$value['level'];
               // $combined = array_merge($value, $item);  
                $details[] = $item;   
            }  
        return $details;  
      }

}