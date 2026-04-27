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
    public function getAll($Json, $currentskill)
    {
        //json解析
        $Array  = json_decode($Json, true);
        $details = [];

        $skillData = $this->select();
        foreach ($skillData as $skill) {
            $id = $skill['id'];
            $id = intval($id);
            $skill['level'] = 0; // 默认等级为0
            foreach ($Array as $value) {
                if ($value['id'] == $id) {
                    $skill['level'] = $value['level'];
                    break;
                }
            }
            if ($currentskill == $id) {
                $skill['is_current'] = true;
            } else {
                $skill['is_current'] = false;
            }
            $details[] = $skill;
        }
        return  $details;

    }

}