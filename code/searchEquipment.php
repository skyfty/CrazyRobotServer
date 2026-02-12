<?php
    //include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  
    include('serverPDO.php'); // 确保pdo.php文件正确设置了PDO连接  
    $sqlequipment = "SELECT * FROM equipment";
    $requipment = $pdo->query($sqlequipment);  
    $equipments = $requipment->fetchAll(PDO::FETCH_ASSOC);  
    // 输出更新后的buffs数组  
    $jsonOutput = json_encode($equipments);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  
?>