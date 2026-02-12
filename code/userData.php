<?php
    include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  

    $id = isset($_POST['id']) ? $_POST['id'] : null;
    if($id==null){
        echo 'id error';
    }
    else{
        
    
    
    $sql = "SELECT * FROM users WHERE id = ?";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute([$id]);  
  
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // 只获取一条数据  

    
    if (!empty($user)) {  
        $jsonOutput = json_encode($user);  
        header('Content-Type: application/json');  
        echo $jsonOutput;  
    }
    else { 
        echo "没有找到任何数据。";
        
    }
}


?>