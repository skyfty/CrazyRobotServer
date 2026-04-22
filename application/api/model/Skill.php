<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Skill extends Model
{
    protected $table = 'skill';

    /**
     * 获取所有技能
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
            $id = intval($id);

            //查询数据库
            $newData = $this->where('id', $id)->find();
            // //判断是否存在
            if ($newData) {
                $data[] = $newData;
            }
        }
        return $data;

    }

}