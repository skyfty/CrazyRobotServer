<?php  

include('common.php');  
  
$SignInActivity = Select("SignInActivity", $pdo);  
$BagList = Select("BagList", $pdo);  
// 初始化结果数组  
$res = [];  
  
// 根据获取的SignInActivity的ItemId在BagList中查询到相应的数据并合并  
foreach ($SignInActivity as $value) {  
    $itemId = $value['ItemId']; 
    $Item=GetItemById($BagList,$itemId);
    if ($Item!=null) {
        $res[] = array_merge( $Item,$value); // 合并数据  
    }  
    else {
        echo "No matching BagItem for ItemId: " . $itemId . "\n"; // 调试输出
    }
}
PrintMsg($res);
    
?>