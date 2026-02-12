<?php    
include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接    

$id = isset($_POST['id']) ? $_POST['id'] : null;  
$level = isset($_POST['level']) ? $_POST['level'] : null;  
$openid = isset($_POST['openid']) ? $_POST['openid'] : null;  
  
if ($openid == null || $id == null || $level == null  ) {  
    $res["code"] = 0;  
    $res["msg"] = "信息有误";  
} else {  
  
  
    // 准备 SQL 语句来读取现有的 equipment 数据  
    $selectSql = "SELECT equipment FROM users WHERE openid = :openid";  
  
    // 使用 PDO 准备和执行 SQL 语句来读取数据  
    try {  
        $stmt = $pdo->prepare($selectSql);  
        $stmt->bindParam(':openid', $openid, PDO::PARAM_STR);  
        $stmt->execute();  
  
        $row = $stmt->fetch(PDO::FETCH_ASSOC);  
        if ($row && !empty($row['equipment'])) {  
            // 解析现有的 equipment 数据  
            $existingEquipment = json_decode($row['equipment'], true);  
  
            // 检查解析是否成功  
            if (is_array($existingEquipment)) {  
                // 检查新数据的 id 是否已经存在  
                $idExists = false;  
                foreach ($existingEquipment as $item) {  
                    if ($item['id'] == $id) {  
                        $idExists = true;  
                        break;  
                    }  
                }  
  
                if (!$idExists) {  
                    // 将新数据添加到现有数据中  
                    $existingEquipment[] = $newEquipmentItem;  
  
                    // 准备 SQL 语句来更新 equipment 数据  
                    $updateSql = "UPDATE users SET equipment = :equipment WHERE openid = :openid";  
  
                    // 使用 PDO 准备和执行 SQL 语句来更新数据  
                    $updateStmt = $pdo->prepare($updateSql);  
                    $updateStmt->bindParam(':equipment', json_encode($existingEquipment), PDO::PARAM_STR);  
                    $updateStmt->bindParam(':openid', $openid, PDO::PARAM_STR);  
                    $updateStmt->execute();  
  
                    // 检查是否有行被更新（可选）  
                    if ($updateStmt->rowCount() > 0) {  
                        $res["code"] = 1;  
                        $res["msg"] = "更新成功，新数据已添加到 equipment 中";  
                    } else {  
                        $res["code"] = 0;  
                        $res["msg"] = "更新失败，但读取 existingEquipment 成功";  
                    }  
                } else {  
                    // ID 已存在  
                    $res["code"] = 2;  
                    $res["msg"] = "ID 已存在于 equipment 中";  
                }  
            } else {  
                // existingEquipment 不是有效的数组，可能是空的 JSON 字符串或其他错误  
                $res["code"] = 0;  
                $res["msg"] = "解析 existingEquipment 失败，请检查数据库中的数据";  
            }  
        } else {  
            // 如果没有找到现有的 equipment 数据或 equipment 字段为空  
            // 你可以选择直接插入新数据作为新数组，或者进行其他逻辑处理  
            $res["code"] = 0;  
            $res["msg"] = "未找到 existingEquipment，可能是新用户或 equipment 字段为空";  
            // ...（可选：直接插入新数组作为 equipment）  
        }  
    } catch (PDOException $e) {  
        $res["code"] = 0;  
        $res["msg"] = "数据库错误：" . $e->getMessage();  
    }  
}  
  
$jsonOutput = json_encode($res);  
header('Content-Type: application/json');  
echo $jsonOutput;  
?>