<?php  
class DB {    
    private $pdo;    
    
    public function __construct($pdo) {    
        $this->pdo = $pdo;    
    }    
  
    // 获取用户装备详细信息的函数    
    function getUserEquipmentDetails($equipmentJson) {  
        $equipmentArray = json_decode($equipmentJson, true);  
        $details = [];  
          
        foreach ($equipmentArray as $value) {  
            $equipmentDetails = $this->getEquipmentDetails($value['id']); // 使用 $this 调用类内方法  
            $combined = array_merge($value, $equipmentDetails);  
            $details[] = $combined;   
        }  
      
        return $details;  
    }  
      
      
    // 获取用户信息的函数    
    function getUserInfo($openid) {  
        $sql = "SELECT * FROM users WHERE openid = ? LIMIT 1";    
        $stmt = $this->pdo->prepare($sql);  // 使用 $this->pdo  
        $stmt->execute([$openid]);    
        $res = $stmt->fetch(PDO::FETCH_ASSOC); // 只获取一条数据    
      
        if ($res) {  
            $res['code'] = 1; // 查询成功  
            $res['equipmentDetails'] = $this->getUserEquipmentDetails($res['equipment']); // 使用 $this 调用类内方法  
            $res['upgradeDatas']=$this->getUserUpgradeDatas($res['upgradeData']);
            $res['equipmentEquippedIndex']=json_decode($res['equipmentEquipped']);
        } else {  
            $res = ['code' => 0, 'message' => '用户不存在'];  
        }  
      
        return $res;  
    }  
      
    // 获取装备详细信息的函数    
    function getEquipmentDetails($equipmentId) {    
        $sql = "SELECT * FROM specialequipments WHERE id = ?";    
        $stmt = $this->pdo->prepare($sql);  // 使用 $this->pdo  
        $stmt->execute([$equipmentId]);    
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // 确保返回数组  
    }   
    
    // 获取用户装备详细信息的函数    
    function getUserUpgradeDatas($Json) {  
        $Array = json_decode($Json, true);  
        $details = [];  
        if($Array){
            foreach ($Array as $value) {  
                $item = $this->getUpgradeData($value['id']); // 使用 $this 调用类内方法  
                $combined = array_merge($value, $item);  
                $details[] = $combined;   
            }  
        }
        return $details;  
    }  
    
     function getUpgradeData($upgradeId) {    
        $sql = "SELECT * FROM upgrade WHERE id = ?";    
        $stmt = $this->pdo->prepare($sql);  // 使用 $this->pdo  
        $stmt->execute([$upgradeId]);    
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // 确保返回数组  
    }  
}  
?>