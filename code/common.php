<?php  
include('DefendSlimmingPdo.php');  
function PrintMsg($msg){
    $jsonOutput = json_encode($msg);
    header('Content-Type: application/json');  
    echo $jsonOutput;  
}
function Select($tablename,$pdo){
    $sql="select * from ".$tablename;
    $result=$pdo->query($sql);
    $data=$result->fetchAll(PDO::FETCH_ASSOC);
    return $data;
}
function GetItemById($List,$Id){
    foreach ($List as $value) {
        if($value['id']==$Id){
            return $value;
        }   
    }return null;
}
?>
