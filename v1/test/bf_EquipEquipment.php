<?php  
include('pdo.php');  
include('database.php');  
  
$db = new DB($pdo);  
  
// 输入验证和清理  
$index = isset($_POST['index']) ? $_POST['index'] : null;  
$id = isset($_POST['id']) ? $_POST['id'] : null;  
$openid = isset($_POST['openid']) ? $_POST['openid'] : null;  
  
// 辅助函数：设置响应并退出  
function sendResponse($code, $msg) {  
    //http_response_code($code);  
    $res = ['code' => $code, 'msg' => $msg];  
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
        $user['equipmentEquippedIndex'][$index]->id = (int)$id;  
        $equipmentEquippedJson = json_encode($user['equipmentEquippedIndex']);  
  
        $sql = 'UPDATE users SET equipmentEquipped = :equipmentEquipped WHERE openid = :openid';  
        $stmt = $pdo->prepare($sql);  
        $stmt->bindParam(':equipmentEquipped', $equipmentEquippedJson, PDO::PARAM_STR);  
        $stmt->bindParam(':openid', $openid, PDO::PARAM_STR);  
        $stmt->execute();  
        
  
        //$success = $stmt->rowCount() > 0;  
        
        if($stmt->rowCount() > 0){
            sendResponse(1 , '装备更新成功');  
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
?>