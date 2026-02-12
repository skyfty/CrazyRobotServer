<?php
    //include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  
    include('serverPDO.php'); // 确保pdo.php文件正确设置了PDO连接  
    $id = isset($_GET['id']) ? $_GET['id'] : '0';

    


    if($id==0){
        $sqlcheckpoint = "SELECT * FROM checkpoint";
    }
    else{
        $sqlcheckpoint = "SELECT * FROM checkpoint where id=1";
    }
    $sqlwave = "SELECT * FROM wave";
    $sqlenemy = "SELECT * FROM enemy";

    $rcheckpoint = $pdo->query($sqlcheckpoint);  
    $rwave = $pdo->query($sqlwave);  
    $renemy = $pdo->query($sqlenemy);

    $checkpoints = $rcheckpoint->fetchAll(PDO::FETCH_ASSOC);  
    $waves = $rwave->fetchAll(PDO::FETCH_ASSOC);  
    $enemies = $renemy->fetchAll(PDO::FETCH_ASSOC);  
    
    $result = [];  
    foreach ($checkpoints as $checkpoint) {  
        $checkpoint['waves'] = [];  
        foreach ($waves as $wave) {  
            if ($wave['checkpoint_id'] == $checkpoint['id']) {  
                $wave['enemies'] = [];  
                foreach ($enemies as $enemy) {  
                    if ($enemy['wave_id'] == $wave['id']) {  
                        $wave['enemies'][] = $enemy;  
                    }  
                }  
                $checkpoint['waves'][] = $wave;  
            }  
        }  
        $result[] = $checkpoint;  
    } 
    // 输出更新后的buffs数组  
    $jsonOutput = json_encode($result);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  

?>