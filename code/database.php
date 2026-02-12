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
                $item = $this->getUpgradeData($value['id'],$value['level']); // 使用 $this 调用类内方法  
                $combined = array_merge($value, $item);  
                $details[] = $combined;   
            }  
        }
        return $details;  
    }  
    
     function getUpgradeData($upgradeId,$level) {    
        $sql = "SELECT * FROM upgrade WHERE id = ?";    
        $stmt = $this->pdo->prepare($sql);  // 使用 $this->pdo  
        $stmt->execute([$upgradeId]);    
        $res=$stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $upgradeLevelDatas=$this->getUpgradeLevelDatas();
        // foreach ($res as $value) {  
                
        //     }  
        // print_r($res); 
        // print_r($upgradeLevelDatas); 
        $name=$this->getUpgradeName($upgradeId);
        // echo $name;
        $res['upgradegold']=$upgradeLevelDatas[$level]['upgradeGold'];
        // $res['value']=$upgradeLevelDatas->where('level',$level)->find()??0;
        $res['value'] = array_values(array_filter($upgradeLevelDatas, fn($v) => $v['level'] == $level))[0][$name]??0;
        
        // $res['value']=$level>0?$upgradeLevelDatas[$level-1][$name]:0;
        // if($res){    [$name]
        //     $res[0]['upgradegold']=$upgradeLevelDatas['BarrelAttackFrequency']['upgradeGold'];
        //     $res[1]['upgradegold']=$upgradeLevelDatas['BarrelAttackDistance']['upgradeGold'];
        //     $res[2]['upgradegold']=$upgradeLevelDatas['BarrelAccuracyLevel']['upgradeGold'];
        //     $res[3]['upgradegold']=$upgradeLevelDatas['BaseDefenseValue']['upgradeGold'];
        //     $res[4]['upgradegold']=$upgradeLevelDatas['BaseExpandedConfiguration']['upgradeGold'];
        //     $res[5]['upgradegold']=$upgradeLevelDatas['BaseExpansionSequence']['upgradeGold'];
        //     $res[6]['upgradegold']=$upgradeLevelDatas['BaseMagazine']['upgradeGold'];
        // }


        return $res ?: []; // 确保返回数组  
    }  
    function getUpgradeName($upgradeId){
        switch ($upgradeId) {
            case 1:
                return 'BarrelAttackFrequency';
                break;
            case 2:
                return 'BarrelAttackDistance';
                break;
            case 3:
                return 'BarrelAccuracyLevel';
                break;
            case 4:
                return 'BaseDefenseValue';
                break;
            case 5:
                return 'BaseExpandedConfiguration';
                break;
            case 6:
                return 'BaseExpansionSequence';
                break;
            case 7:
                return 'BaseMagazine';
                break;
            default:
                return 'BarrelAttackFrequency';
                break;
        }
    }
    function getUpgradeLevelDatas(){
        $sql = "SELECT * FROM upgradeLevel";    
        $stmt = $this->pdo->prepare($sql);  // 使用 $this->pdo  
        $stmt->execute();
        return $stmt->fetchAll() ?: []; // 确保返回数组  
    }
}  
?>