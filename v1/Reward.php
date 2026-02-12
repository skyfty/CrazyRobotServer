<?php  
include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  
  
$gold = isset($_POST['gold']) ? intval($_POST['gold']) : 0; // 确保gold是整数  
$playerId = isset($_POST['playerId']) ? intval($_POST['playerId']) : 1; // 确保playerId是整数  
$checkpointId=isset($_POST['checkpointId']) ? intval($_POST['checkpointId']) : 0;  
$shouldUpdateCheckpoint=isset($_POST['shouldUpdateCheckpoint']) ? intval($_POST['shouldUpdateCheckpoint']) : 0;  
// 首先检查用户是否存在  
$sql = "SELECT * FROM users WHERE id = :playerId";  
$stmt = $pdo->prepare($sql);  
$stmt->bindParam(':playerId', $playerId, PDO::PARAM_INT);  
$stmt->execute();  
$user = $stmt->fetch(PDO::FETCH_ASSOC);  
  
if (empty($user)) {  
    $res["code"] = 0;  
    $res["error"] = 'No user found.';  
    $jsonOutput = json_encode($res);  
    header('Content-Type: application/json');  
echo $jsonOutput;  
    exit; // 如果用户不存在，则退出脚本  
}  
  
  if($gold==0){
    $res["code"] = 1;  
    $res["msg"] = "更新成功，奖励已添加。"; // 修改了消息内容，使其更清晰  
    $jsonOutput = json_encode($res);  
    header('Content-Type: application/json');  
    echo $jsonOutput;   
    exit;
  }
  
  // 用户存在，检查是否需要更新checkpointId（这里只是简单比较，实际逻辑可能更复杂）  
$currentCheckpointId = $user['passCheckpointId'];  
$shouldUpdateCheckpointValue = ($checkpointId > $currentCheckpointId); 
 
// 用户存在，执行更新操作  
///$updateSql = "UPDATE users SET gold = gold + :gold WHERE id = :playerId";  

if ($shouldUpdateCheckpoint!=0&&$shouldUpdateCheckpointValue) {  
    $updateSql = "UPDATE users    
            SET gold = gold + :gold,    
                passCheckpointId = :newCheckpointId    
            WHERE id = :playerId";  
} else {  
    $updateSql = "UPDATE users    
            SET gold = gold + :gold    
            WHERE id = :playerId";  
}  
$updateStmt = $pdo->prepare($updateSql);  
$updateStmt->bindParam(':gold', $gold, PDO::PARAM_INT); // 直接传递gold值，不需要在这里加  
$updateStmt->bindParam(':playerId', $playerId, PDO::PARAM_INT);  
if($shouldUpdateCheckpoint!=0&&$shouldUpdateCheckpointValue){
    $updateStmt->bindParam(':newCheckpointId',$checkpointId,PDO::PARAM_INT);
}
$updateStmt->execute();  
  
if ($updateStmt->rowCount() > 0) {  
    $res["code"] = 1;  
    $res["msg"] = "更新成功，奖励已添加。"; // 修改了消息内容，使其更清晰  
} else {  
    $res["code"] = 0;  
    $res["msg"] = "更新失败，但用户确实存在。"; // 修正了错误信息  
}  
  
$jsonOutput = json_encode($res);  
header('Content-Type: application/json');  
echo $jsonOutput;  
?>