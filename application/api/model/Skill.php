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

        $skillData = $this->select();
        foreach ($skillData as $skill) {
            $id = $skill['id'];
            $id = intval($id);
            foreach ($Array as $value) {
                if ($value['id'] == $id) {
                    $skill['level'] = $value['level'];
                    break;
                }
            }
            $details[] = $skill;
        }
        return  $details;

    }

}