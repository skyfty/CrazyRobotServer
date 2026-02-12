<?php
    include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  

    

    $sql = "SELECT * FROM servers";

    $r = $pdo->query($sql);  

    $servers = $r->fetchAll(PDO::FETCH_ASSOC);  
    
    // 输出更新后的buffs数组  
    $jsonOutput = json_encode($servers);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  

?>