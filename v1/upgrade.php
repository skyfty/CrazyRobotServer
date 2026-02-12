<?php
//升级接口
    include('pdo.php'); 
    include('database.php');
    $db=new DB($pdo);
    $openid = isset($_POST['openid']) ? $_POST['openid'] : null;
    $res = $db->getUserInfo($openid); 
    $type = isset($_POST['type']) ? $_POST['type'] : null;//0升级数据 1装备
    $id=isset($_POST['id']) ? $_POST['id'] : null;//升级数据id或装备id
    if($type==null||$id==null){
        $result['code']=0;
        $result['msg']='data error';
    }
    else{
        /*type 
        0 upgradedata 
        1 equipment;
        */
        $res['equipment']=json_decode($res['equipment']);
        $res['upgradeData']=json_decode($res['upgradeData']);
        //print_r($res) ;
        //print_r($res['upgradeData']) ;
        //echo "gold::".$res['gold'];
        $listDetailsName='upgradeDatas';//升级数据详情
        $listDataName='upgradeData';//升级数据
        $haveGold='0';
        switch ($type) {
            case 0:
                $listDetailsName='upgradeDatas';
                $listDataName='upgradeData';
                break;
            case 1:
                $listDetailsName='equipmentDetails';
                $listDataName='equipment';
                break;
            default:
                // code...
                break;
        }
        
        
        
        //echo $res[$listDetailsName][$id]['name']
        //echo $res[$listDetailsName][$id]['name'];
        $haveGold=getDetailsfromid($id,$res[$listDetailsName])['upgradegold'];
        //echo getlevelfromid($id,$res[$listDataName])->level;
        if($res['gold']<$haveGold){
            $result['code']=3;
            $result['msg']="钱不够";
            $jsonOutput = json_encode($result);  
            header('Content-Type: application/json');  
            echo $jsonOutput; 
            return;
        }
        $updateGoldSql = "UPDATE users SET gold = :gold WHERE openid = :openid";  
        $stmt = $pdo->prepare($updateGoldSql);  
        $stmt->execute(['gold' => $res['gold']-$haveGold, 'openid' => $openid]);  
          
        
        //echo $res['upgradeData'][$id]['upgradegold'];
        foreach ($res[$listDataName] as $value) {  
            if($value->id==$id){
                $value->level=++getlevelfromid($id,$res[$listDataName])->level;
            }
        }  
        //print_r($res['upgradeData']) ;
        //echo json_encode($res[$listDataName]);
        $sql="UPDATE users SET `$listDataName` = :upgradeData WHERE openid = :openid";
        $stmt = $pdo->prepare($sql);  
        $stmt->execute(['upgradeData' => json_encode($res[$listDataName]), 'openid' => $openid]);  
        
        $result['code']=1;
        $result['msg']="升级成功";
    }
    $jsonOutput = json_encode($result);  
    header('Content-Type: application/json');  
    echo $jsonOutput; 
    function getDetailsfromid($id,$arr){
        foreach ($arr as $value) {  
            if($value['id']==$id){
                return $value;
            }
        }  
        return null;
    }
    function getlevelfromid($id,$arr){
        foreach ($arr as $value) {  
            if($value->id==$id){
                return $value;
            }
        }  
        return null;
    }
?>