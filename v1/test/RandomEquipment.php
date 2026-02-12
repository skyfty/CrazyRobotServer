<?php  
//抽奖接口
include('pdo.php'); // 确保pdo.php文件正确设置了PDO连接  
$num = isset($_GET['num']) ? $_GET['num'] : '1';

$sql = "SELECT * FROM  specialequipments";  
$stmt = $pdo->prepare($sql);  
$stmt->execute();  
$equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    if (empty($equipments)) {  
         $res["code"]=0;
         $res["error"]='No equipments found.';
    } else { 
        for ($i = 0; $i < $num; $i++) {
        $rand = mt_rand(1, 100);  
            if ($rand <= 10) {  
                $randomIndex = mt_rand(1, count($equipments) - 1);  
                $temp[$i] = $equipments[$randomIndex];
                $temp[$i]['level']=1;
            }  
            else{
                $temp[$i] = $equipments[0];  
                $temp[$i]['level']=1;
            }
        }
        
        //$res["code"]=1;
        $res['code']=1;
        $res['equipments']=$temp;
    }
           

    $jsonOutput = json_encode($res);
    header('Content-Type: application/json');  
    echo $jsonOutput;  

?>