<?php  
include('pdo.php');  
include('database.php');  
  
$db = new DB($pdo);  
  
// 输入验证和清理  
$index = isset($_POST['index']) ? $_POST['index'] : null;  //装备索引（第几个装备槽）
$id = isset($_POST['id']) ? $_POST['id'] : null;  //装备ID
$openid = isset($_POST['openid']) ? $_POST['openid'] : null;  
  
// 辅助函数：设置响应并退出  
function sendResponse($code, $msg, $data = null) {  
    //http_response_code($code);  
    $res = ['code' => $code, 'msg' => $msg];  
    if ($data !== null) {  
        $res['data'] = $data;  
    }  
    $jsonOutput = json_encode($res);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  
    exit;  
}  
  
// 检查必要的参数  
if ($index==null || $id==null || $openid==null) {  
    sendResponse(400, '缺少必要的参数');  
}   
  
try {  
    // 获取用户信息  
    $user = $db->getUserInfo($openid);  
    if (!$user || !isset($user['equipmentEquippedIndex']) || !is_array($user['equipmentEquippedIndex'])) {  
        sendResponse(404, '用户信息或装备信息不存在');  
    }  
   
  
  
    // 更新装备信息  
    if (isset($user['equipmentEquippedIndex'][$index])) {  
        //判断装备是否已经装备在装备列表
        if($user['equipmentEquippedIndex'][$index]->id == (int)$id){
            sendResponse( 0, '装备已经装备在这个位置');
        }
        //不能重复装备
        if(in_array($id, array_column($user['equipmentEquippedIndex'], 'id'))){
            sendResponse( 0, '装备已经装备在其他位置');
        }



        $user['equipmentEquippedIndex'][$index]->id = (int)$id;  
        $equipmentEquippedJson = json_encode($user['equipmentEquippedIndex']);  
  
        $sql = 'UPDATE users SET equipmentEquipped = :equipmentEquipped WHERE openid = :openid';  //更新用户装备信息
        $stmt = $pdo->prepare($sql);  
        $stmt->bindParam(':equipmentEquipped', $equipmentEquippedJson, PDO::PARAM_STR);  //绑定装备信息参数
        $stmt->bindParam(':openid', $openid, PDO::PARAM_STR);  //绑定用户ID参数
        $stmt->execute();  //执行更新操作
        //重新获取装备信息
         $newUser = $db->getUserInfo($openid); 
        // $
     $newequipmentEquippedJson = json_encode($user['equipmentEquippedIndex']);  
        //$success = $stmt->rowCount() > 0;  
        
        if($stmt->rowCount() > 0){
            
            //  $res = $db->getUserInfo($openid); 
            //  $data['equipmentDetails'] = $res['equipmentDetails'];
            //  $data['equipmentEquippedIndex'] = $res['equipmentEquippedIndex'];
            //  $data['equipmentEquipped'] = $newequipmentEquippedJson;

            sendResponse(1 , '装备更新成功', $newequipmentEquippedJson);  
        }else{
            sendResponse( 0, '未找到要更新的用户或装备信息或装备本身就是这个');
        }
        
    } else {  
        sendResponse(-3, '指定的装备索引不存在');  
    }  
} catch (Exception $e) {  
    // 错误处理：记录日志并发送响应  
    error_log('服务器内部错误: ' . $e->getMessage());  
    sendResponse(500, '服务器内部错误');  
}  


//获取当前用户的装备列表
function getEquippedItems($openid) {
     global $db;//数据库连接
     $user = $db->getUserInfo($openid);
     $equipped = [];
    // TODO: 根据实际业务查询已装备装备
    return $equipped;
}
?>