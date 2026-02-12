<?php  
header("Access-Control-Allow-Origin: *");

    include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  

    $sql = "SELECT * FROM buff"; 
    
    $r = $pdo->query($sql);  


    $buff = $r->fetchAll(PDO::FETCH_ASSOC);  
    
    
    // 输出更新后的buffs数组  
    $jsonOutput = json_encode($buff);  
    header('Content-Type: application/json');  
    echo $jsonOutput;  

  
    ?>