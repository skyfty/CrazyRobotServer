<?php
    include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  

    $name = isset($_POST['name']) ? $_POST['name'] : null;
if($name==null){
    echo 'name error';
}
else{
    $sql = "SELECT * FROM users WHERE name = ? LIMIT 1";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute([$name]);  
  
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // 只获取一条数据  

    
    if (!empty($user)) {  
        $jsonOutput = json_encode($user);  
        header('Content-Type: application/json');  
        echo $jsonOutput;  
    }
    else { 
        //echo "ELse Name::::".$name;
        // 没有数据  
        //echo "没有找到任何数据。";
        //注册用户 非常危险，上线要改
        
        // 准备SQL语句  
        $res["code"]=0;
        $res["msg"]="NO Equipment";
        $jsonOutput = json_encode($res);  
        header('Content-Type: application/json');  
        echo $jsonOutput;  
    }
}


?>