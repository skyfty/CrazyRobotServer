<?php
namespace app\api\controller;
    
use app\common\controller\Api;
use app\api\model\Building as BuildingModel;

class Building extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    
    public function getAll()
    {
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $user_id = $tokenInfo['user_id'];
        $user = \app\common\model\User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
             
        $buildingModel = new BuildingModel();
        $list = $buildingModel->getAll($user_id);
        $this->success(__('获取成功'), $list);
    }
    
    //解锁
    public function unlock()
    {
        $params = $this->request->param();
        $index = intval($params['index']);
        $id = intval($params['id']);
        $level = intval($params['level']);
        $status = intval($params['status']);
        $end_time = intval($params['end_time']);
              
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $user_id = $tokenInfo['user_id'];
        $user = \app\common\model\User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $buildingModel = new BuildingModel();
        $build = $buildingModel->updateLevel($user_id, $id, $level, $status, $end_time, $index);
               
        $newStructure = [
             'id' => $id,
             'level' => $level,
             'status' => $status,
             'end_time' => $end_time,
             'index' => $index,
        ];
        $this->success(__('解锁中'), $newStructure);
    }
    
    
    //升级
    public function upgrade()
    {
        $params = $this->request->param();
        $id = intval($params['id']);
        $level = intval($params['level']);
        $status = intval($params['status']);
        $end_time = intval($params['end_time']);
        $index = intval($params['index']);
                    
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $user_id = $tokenInfo['user_id'];
        $user = \app\common\model\User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $buildingModel = new BuildingModel();
        $building = $buildingModel->getBuilding($user_id, $index);
        
        if($building->status === 1)
        {
             $this->error(__('正在升级中'));
        }
        else
        {
            $buildingModel = new BuildingModel();
            $build = $buildingModel->updateLevel($user_id, $id, $level, $status, $end_time, $index);
            $newStructure = [
               'id' => $id,
               'level' => $level,
               'status' => $status,
               'end_time' => $end_time,
               'index' => $index,
            ];
            $this->success(__('升级中'), $newStructure);
        }
    }
    
    
    public function updateLevel()
    {
         $params = $this->request->param();
         $index = intval($params['index']);
         $id = intval($params['id']);
         $level = intval($params['level']);
         $status = intval($params['status']);
         $end_time = intval($params['end_time']);
         
         $token = $this->auth->getToken();
         $tokenInfo = \app\common\library\Token::get($token);
         $user_id = $tokenInfo['user_id'];
         $user = \app\common\model\User::get($user_id);
         if (!$user) {
             $this->setError('User not found');
             return false;
         }
         $buildingModel = new BuildingModel();
         $build = $buildingModel->updateLevel($user_id, $id, $level, $status, $end_time, $index);
         
         $newStructure = [
             'id' => $id,
             'level' => $level,
             'status' => $status,
             'end_time' => $end_time,
             'index' => $index,
         ];
         $this->success(__('更新成功'), $newStructure);
    }
    
    public function change()
    {
        $params = $this->request->param();
        $index = intval($params['index']);
        $id = intval($params['id']);
        $level = intval($params['level']);
        $status = intval($params['status']);
        $end_time = intval($params['end_time']);
        
        $index2 = intval($params['index2']);
        $id2 = intval($params['id2']);
        $level2 = intval($params['level2']);
        $status2 = intval($params['status2']);
        $end_time2 = intval($params['end_time2']);
               
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $user_id = $tokenInfo['user_id'];
        $user = \app\common\model\User::get($user_id);
        if (!$user) {
              $this->setError('User not found');
              return false;
        }
        $buildingModel = new BuildingModel();
        $build = $buildingModel->updateLevel($user_id, $id2, $level2, $status2, $end_time2, $index);
        $build2 = $buildingModel->updateLevel($user_id, $id, $level, $status, $end_time, $index2);
        
        $data1 = [
              'id' => $id2,
              'level' => $level2,
              'status' => $status2,
              'end_time' => $end_time2,
              'index' => $index,
        ];
        
         $data2 = [
              'id' => $id,
              'level' => $level,
              'status' => $status,
              'end_time' => $end_time,
              'index' => $index2,
         ];
        
        $newStructure = ['data1'=>$data1, 'data2'=>$data2];
        
        $this->success(__('更新成功'), $newStructure);
        
    }
}