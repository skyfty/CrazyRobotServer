<?php
include('pdo.php');
include('database.php');

$db=new DB($pdo);

$openid = isset($_POST['openid']) ? $_POST['openid'] : null;

if ($openid === null) {
    echo json_encode(['code' => 0, 'message' => 'openid error']);
    exit;
}

// 查询用户信息
 $res = $db->getUserInfo($openid); 

if ($res['code']==1) {  
    $res['code'] = 2; // 用户已存在
    
    $jsonOutput = json_encode($res);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  
} else { 
    // 注册新用户
    $registersql = "INSERT INTO users (openid, gold, diamond, equipment,upgradeData,equipmentEquipped) VALUES (?, ?, ?, ? , ?,?)";  
    $stmt = $pdo->prepare($registersql);  
    $stmt->execute([$openid, 0, 100, '[{"id": "1", "level": 1}]','[{"id": 1, "level": 0}, {"id": 2, "level": 0}, {"id": 3, "level": 0}, {"id": 4, "level": 0}, {"id": 5, "level": 0}, {"id": 6, "level": 0}, {"id": 7, "level": 0}]','[{"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}]']);  

    // 检查是否成功插入  
    if ($stmt->rowCount() > 0) {  
        // 获取新用户信息
        $res = $db->getUserInfo($openid); 
        $jsonOutput = json_encode($res);  
        header('Content-Type: application/json');  
        echo $jsonOutput;  
    } else {  
        echo json_encode(['code' => 0, 'message' => '注册失败']);  
    }  
}
?>