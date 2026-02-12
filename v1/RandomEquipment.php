<?php  
// 合并后的抽奖和添加装备接口
include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  

// 获取参数
$action = 'draw_and_add';//isset($_POST['action']) ? $_POST['action'] : 'draw'; // 'draw' 或 'add'
$num = isset($_POST['num']) ? intval($_POST['num']) : 1;
$openid = isset($_POST['openid']) ? $_POST['openid'] : null;  
$id = isset($_POST['id']) ? $_POST['id'] : null;  
$level = isset($_POST['level']) ? $_POST['level'] : null;  

// 验证openid
if ($openid == null) {
    $res["code"] = 0;
    $res["msg"] = "openid不能为空";
    echoJson($res);
    exit;
}
// 验证num
if ($num <= 0) {
    $res["code"] = 0;
    $res["msg"] = "抽奖数量必须大于0";
    echoJson($res);
    exit;
}
//判断用户是否有足够的砖石
$diamondSql = "SELECT diamond FROM users WHERE openid = :openid";
$diamondStmt = $pdo->prepare($diamondSql);
$diamondStmt->execute(['openid' => $openid]);
$userDiamond = $diamondStmt->fetchColumn();

if ($userDiamond < $num *1) {
    $res["code"] = 0;
    $res["msg"] = "用户砖石不足";
    echoJson($res);
    exit;
}

// 根据action执行不同操作
switch ($action) {
    case 'draw':
        handleDraw();
        break;
    case 'add':
        handleAdd();
        break;
    case 'draw_and_add':
        handleDrawAndAdd();
        break;
    default:
        $res["code"] = 0;
        $res["msg"] = "未知的操作类型";
        echoJson($res);
        break;
}

// 处理抽奖
function handleDraw() {
    global $pdo, $num, $openid, $res;
    
    $sql = "SELECT * FROM specialequipments";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute();  
    $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    
    if (empty($equipments)) {  
        $res["code"] = 0;
        $res["error"] = '未找到装备数据';
    } else { 
        $temp = array();
        for ($i = 0; $i < $num; $i++) {
            $rand = mt_rand(1, 100);  
            if ($rand <= 10) {  
                $randomIndex = mt_rand(1, count($equipments) - 1);  
                $temp[$i] = $equipments[$randomIndex];
                $temp[$i]['level'] = 1;
            } else {
                $temp[$i] = $equipments[0];  
                $temp[$i]['level'] = 1;
            }
        }
        
        $res['code'] = 1;
        $res['msg'] = '抽奖成功';
        $res['equipments'] = $temp;
        $res['count'] = count($temp);
    }
    
    echoJson($res);
}

// 处理添加装备
function handleAdd() {
    global $pdo, $openid, $id, $level, $res;
    
    if ($id == null || $level == null) {  
        $res["code"] = 0;  
        $res["msg"] = "装备信息不完整";  
        echoJson($res);
        exit;
    }
    
    // 准备要添加的装备数据
    $newEquipmentItem = array(
        'id' => $id,
        'level' => $level
        // 'add_time' => date('Y-m-d H:i:s')
    );
    
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
                    if (isset($item['id']) && $item['id'] == $id) {  
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
  
                    // 检查是否有行被更新  
                    if ($updateStmt->rowCount() > 0) {  
                        $res["code"] = 1;  
                        $res["msg"] = "装备添加成功";  
                        $res["equipment"] = $newEquipmentItem;
                    } else {  
                        $res["code"] = 0;  
                        $res["msg"] = "更新失败，用户可能不存在";  
                    }  
                } else {  
                    // ID 已存在  
                    $res["code"] = 2;  
                    $res["msg"] = "该装备已存在于您的仓库中";  
                }  
            } else {  
                // existingEquipment 不是有效的数组  
                $res["code"] = 0;  
                $res["msg"] = "解析装备数据失败";  
            }  
        } else {  
            // 如果没有找到现有的 equipment 数据或 equipment 字段为空  
            // 创建新的装备数组
            $newEquipmentArray = array($newEquipmentItem);
            
            // 检查用户是否存在，如果不存在则插入新用户
            $checkUserSql = "SELECT id FROM users WHERE openid = :openid";
            $checkStmt = $pdo->prepare($checkUserSql);
            $checkStmt->bindParam(':openid', $openid, PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                // 用户存在，更新装备
                $updateSql = "UPDATE users SET equipment = :equipment WHERE openid = :openid";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':equipment', json_encode($newEquipmentArray), PDO::PARAM_STR);
                $updateStmt->bindParam(':openid', $openid, PDO::PARAM_STR);
                $updateStmt->execute();
                
                $res["code"] = 1;  
                $res["msg"] = "装备添加成功（首次添加）";
                $res["equipment"] = $newEquipmentItem;
            } else {
                // 用户不存在，插入新用户
                $insertSql = "INSERT INTO users (openid, equipment) VALUES (:openid, :equipment)";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->bindParam(':openid', $openid, PDO::PARAM_STR);
                $insertStmt->bindParam(':equipment', json_encode($newEquipmentArray), PDO::PARAM_STR);
                $insertStmt->execute();
                
                $res["code"] = 1;  
                $res["msg"] = "用户创建成功，装备已添加";
                $res["equipment"] = $newEquipmentItem;
            }
        }  
    } catch (PDOException $e) {  
        $res["code"] = 0;  
        $res["msg"] = "数据库错误：" . $e->getMessage();  
    }  
    
    echoJson($res);
}

// 处理抽奖并自动添加装备
function handleDrawAndAdd() {
    global $pdo, $num, $openid, $res;
    
    // 先抽奖
    $sql = "SELECT * FROM specialequipments";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute();  
    $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    
    if (empty($equipments)) {  
        $res["code"] = 0;
        $res["error"] = '未找到装备数据';
        echoJson($res);
        exit;
    }
    
    $drawnEquipments = array();
    for ($i = 0; $i < $num; $i++) {
        $rand = mt_rand(1, 100);  
        if ($rand <= 10) {  
            $randomIndex = mt_rand(1, count($equipments) - 1);  
            $equipment = $equipments[$randomIndex];
            $equipment['level'] = 1;
        } else {
            $equipment = $equipments[0];  
            $equipment['level'] = 1;
        }
        $drawnEquipments[] = $equipment;
    }
      // 根据num减少用户砖石
        $expenditure = $num*1; 
        $diamondSql = "UPDATE users SET diamond = diamond - :expenditure WHERE openid = :openid";
        $diamondStmt = $pdo->prepare($diamondSql);
        $diamondStmt->bindParam(':expenditure', $expenditure, PDO::PARAM_INT);
        $diamondStmt->bindParam(':openid', $openid, PDO::PARAM_STR);
        $diamondStmt->execute();
        
        // 检查是否成功更新
        if ($diamondStmt->rowCount() == 0) {
            $res["code"] = 0;
            $res["msg"] = "用户不存在或砖石不足";
            echoJson($res);
            exit;
        }
    
    // 获取用户现有装备
    $selectSql = "SELECT equipment FROM users WHERE openid = :openid";  
    $stmt = $pdo->prepare($selectSql);  
    $stmt->bindParam(':openid', $openid, PDO::PARAM_STR);  
    $stmt->execute();  
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);  
    $existingEquipment = array();
    
    if ($row && !empty($row['equipment'])) {  
        $existingEquipment = json_decode($row['equipment'], true);  
        if (!is_array($existingEquipment)) {
            $existingEquipment = array();
        }
    }
    
    // 添加抽到的装备
    $addedCount = 0;
    $duplicateCount = 0;
    $addedEquipments = array();
    
    foreach ($drawnEquipments as $equipment) {
        $idExists = false;
        foreach ($existingEquipment as $item) {
            if (isset($item['id']) && $item['id'] == $equipment['id']) {
                $idExists = true;
                $duplicateCount++;
                break;
            }
        }
        
        if (!$idExists) {
            $newItem = array(
                'id' => $equipment['id'],
                'level' => 1,
                // 'name' => isset($equipment['name']) ? $equipment['name'] : '',
                // 'add_time' => date('Y-m-d H:i:s'),
                // 'draw_time' => date('Y-m-d H:i:s')
            );
            $existingEquipment[] = $newItem;
            $addedEquipments[] = $newItem;
            $addedCount++;
        }
    }
    
    // 更新数据库
    try {
      
        
        // 更新装备
        $updateSql = "UPDATE users SET equipment = :equipment WHERE openid = :openid";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':equipment', json_encode($existingEquipment), PDO::PARAM_STR);
        $updateStmt->bindParam(':openid', $openid, PDO::PARAM_STR);
        $updateStmt->execute();
        
        $res["code"] = 1;
        $res["msg"] = "抽奖并添加完成";
        $res["draw_results"] = $drawnEquipments;// 抽奖结果
        // $res["added_count"] = $addedCount;// 新增装备数量
        // $res["duplicate_count"] = $duplicateCount;// 重复装备数量
        // $res["added_equipments"] = $addedEquipments;// 新增装备详情
       // $res["total_equipments"] = count($existingEquipment);// 总装备数量
        
    } catch (PDOException $e) {
        $res["code"] = 0;
        $res["msg"] = "数据库更新错误：" . $e->getMessage();
        $res["draw_results"] = $drawnEquipments;
    }
    
    echoJson($res);
}

// 输出JSON的辅助函数
function echoJson($data) {
    header('Content-Type: application/json');  
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>