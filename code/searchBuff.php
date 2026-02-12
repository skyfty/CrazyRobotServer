<?php  
header("Access-Control-Allow-Origin: *");

    //include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  
    include('serverPDO.php'); // 确保pdo.php文件正确设置了PDO连接  
    $sqlbm = "SELECT * FROM bm";  
    $sqloncreate = "SELECT * FROM buffbmoncreate";  
    $sqlonremove = "SELECT * FROM buffbmonremove"; // remove
    $sqlontick= "SELECT * FROM buffbmontick";//tick
    $sqlonrepeatadd = "SELECT * FROM buffbmonrepeatadd"; 
    $sqlbuffs = "SELECT * FROM buffs";  
     
    
    $rbm = $pdo->query($sqlbm);  
    $rbuffs = $pdo->query($sqlbuffs);  
    $roncreate = $pdo->query($sqloncreate);  
    $rontick= $pdo->query($sqlontick);
    $ronremove = $pdo->query($sqlonremove); // 执行新增的查询  
    $ronrepeatadd=$pdo->query($sqlonrepeatadd); // 执行新增的查询 

    $bm = $rbm->fetchAll(PDO::FETCH_ASSOC);  
    $buffs = $rbuffs->fetchAll(PDO::FETCH_ASSOC);  
    $oncreate = $roncreate->fetchAll(PDO::FETCH_ASSOC);  
    $onremove = $ronremove->fetchAll(PDO::FETCH_ASSOC); // 获取移除关联的数据  
    $ontick=$rontick->fetchAll(PDO::FETCH_ASSOC);
    $onrepeatadd=$ronrepeatadd->fetchAll(PDO::FETCH_ASSOC);

   
  
    

    // 处理数据，使用临时数组来存储更新后的buffs  
    $updatedBuffs = [];  
    foreach ($buffs as $buff) {  
        $updatedBuff = $buff;  
        $oncreateItems = array_filter($oncreate, function ($item) use ($buff) {  
            return $item['buff_id'] == $buff['id'];  
        });  
        $onremoveItems = array_filter($onremove, function ($item) use ($buff) {  
            return $item['buff_id'] == $buff['id'];  
        });  
        $ontickItems = array_filter($ontick, function ($item) use ($buff) {  
            return $item['buff_id'] == $buff['id'];  
        });  
        $onrepeataddItems = array_filter($onrepeatadd, function ($item) use ($buff) {  
            return $item['buff_id'] == $buff['id'];  
        });  
        
        $updatedBuff['OnCreate'] = [];  
        $updatedBuff['OnRemove'] = []; 
        $updatedBuff['OnTick'] = []; 
        $updatedBuff['OnRepeatAdd'] = [];
    
        // 处理OnCreate  
        processBuffEvents($updatedBuff, $oncreateItems, 'OnCreate', $bm);  
    
        // 处理OnRemove，注意这里使用$onRemoveItem  
        processBuffEvents($updatedBuff, $onremoveItems, 'OnRemove', $bm);  

        processBuffEvents($updatedBuff,$ontickItems,'OnTick',$bm);
        processBuffEvents($updatedBuff,$onrepeataddItems,'OnRepeatAdd',$bm);
    
        $updatedBuffs[] = $updatedBuff;  
    }  
    
    
    // 输出更新后的buffs数组  
    $jsonOutput = json_encode($updatedBuffs);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  

 // 辅助函数，用于查找ID对应的元素  
 function findById($array, $id, $key = 'id') {  
    foreach ($array as $item) {  
        if ($item[$key] == $id) {  
            return $item;  
        }  
    }  
    return null;  
}  
// 处理buff的OnCreate和OnRemove事件  
function processBuffEvents(&$updatedBuff, $items, $eventType, $bm) {  
    $result = [];  
    foreach ($items as $item) {  
        $bmItem = findById($bm, $item['bm_id']);  
        if ($bmItem) {  
            $result[] = [

                'bm_id' => $item['bm_id'],  
                'name' => $bmItem['name'],  
                'hp' => $bmItem['hp'],  
                'atk' => $bmItem['atk'],  
                'speed' => $bmItem['speed'],  
                'typeid' => $bmItem['typeid'],  
                'stackNum' => $bmItem['stackNum'],  
            ]; 
        }  
    }  
    $updatedBuff[$eventType] = $result;  
}  


    // function renderProperty($updatedBuff,$bm,$Item){
    //     $bmItem = findById($bm, $Item['bm_id']);  
    //     if ($bmItem) {  
    //         $updatedBuff['OnRemove'][] = [  
    //             'bm_id' => $Item['bm_id'],  
    //             'name' => $bmItem['name']  ,
    //             'hp' =>$bmItem['hp'] ,
    //             'hp' =>$bmItem['hp'] ,
    //             'hp' =>$bmItem['hp'] ,
    //         ];  
    //     }  
    // }
    // 处理数据，使用临时数组来存储更新后的buffs  
    // $updatedBuffs = [];  
    // foreach ($buffs as $buff) {  
    //     $updatedBuff = $buff; // 复制当前buff到临时变量  
    //     $oncreateItems = array_filter($oncreate, function ($item) use ($buff) {  
    //         return $item['buff_id'] == $buff['id'];  
    //     });  
    //     $onremoveItems = array_filter($onremove, function ($item) use ($buff) {  
    //         return $item['buff_id'] == $buff['id'];  
    //     });  
    
    //     $updatedBuff['OnCreate'] = [];  
    //     $updatedBuff['OnRemove'] = []; // 初始化onremove数组  
    
    //     // 假设您想为每个bm_id添加bm表中的所有字段，包括新字段new_field  
    //     $bmIdsOnCreate = array_map(function ($item) { return $item['bm_id']; }, $oncreateItems);  
    //     $bmIdsOnRemove = array_map(function ($item) { return $item['bm_id']; }, $onremoveItems);  
    
    //     // 查找所有相关的bm项，包括新字段  
    //     $relevantBms = [];  
    //     foreach (array_merge($bmIdsOnCreate, $bmIdsOnRemove) as $bmId) {  
    //         $bmItem = findById($bm, $bmId);  
    //         if ($bmItem) {  
    //             $relevantBms[$bmItem['id']] = $bmItem; // 使用id作为键来避免重复  
    //         }  
    //     }  
    
    //     // 处理oncreate  
    //     foreach ($oncreateItems as $onCreateItem) {  
    //         $bmId = $onCreateItem['bm_id'];  
    //         if (isset($relevantBms[$bmId])) {  
    //             $bmItem = $relevantBms[$bmId];  
    //             $updatedBuff['OnCreate'][] = [  
    //                 'bm_id' => $bmId,  
    //                 'name' => $bmItem['name'],  
    //                 'hp' => $bmItem['hp'] // 添加新字段  
    //             ];  
    //         }  
    //     }  
    
    //     // 处理onremove（与处理oncreate类似）  
    //     foreach ($onremoveItems as $onRemoveItem) {  
    //         $bmId = $onRemoveItem['bm_id'];  
    //         if (isset($relevantBms[$bmId])) {  
    //             $bmItem = $relevantBms[$bmId];  
    //             $updatedBuff['OnRemove'][] = [  
    //                 'bm_id' => $bmId,  
    //                 'name' => $bmItem['name'],  
    //                 'hp' => $bmItem['hp'] // 添加新字段  
    //             ];  
    //         }  
    //     }  
    
    //     $updatedBuffs[] = $updatedBuff; // 将更新后的buff添加到临时数组中  
    // }  
    
    // // 输出更新后的buffs数组  
    // $jsonOutput = json_encode($updatedBuffs, JSON_PRETTY_PRINT); // 使用JSON_PRETTY_PRINT使输出更易于阅读  
    // header('Content-Type: application/json');  
    // echo $jsonOutput;  
    ?>