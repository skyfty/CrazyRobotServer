<?php
namespace app\api\controller;
    
use app\common\controller\Api;
use app\api\model\Guide as GuideModel;

class Guide extends Api
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
             
        $guideModel = new GuideModel();
        $list = $guideModel->getAll($user_id);
        $this->success(__('获取成功'), $list);
    }
    
    public function addGuide()
    {
         $params = $this->request->param();
         $id = intval($params['id']);
         
         $token = $this->auth->getToken();
         $tokenInfo = \app\common\library\Token::get($token);
         $user_id = $tokenInfo['user_id'];
         $user = \app\common\model\User::get($user_id);
         if (!$user) {
             $this->setError('User not found');
             return false;
         }
         $guideModel = new GuideModel();
         $guideModel->addGuide($user_id, $id);
         
         $newStructure = [
             'id' => $id,
         ];
         $this->success(__('保存成功'), $newStructure);
    }
}