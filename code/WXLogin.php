<?php  
$code = isset($_GET['code']) ? $_GET['code'] : 0;  
    //$id = isset($_GET['id']) ? $_GET['id'] : '0';
  
// 定义常量  
const APPID = "wx735861abbd3b7cd5";  
const SECRET = "bbdc336df79fa8887378a78c5d919da6";  
const GRANT_TYPE = "authorization_code";  
  
// 完整的URI，包含协议和路径  
$uri = "https://api.weixin.qq.com/sns/jscode2session";  
  
// 构造查询字符串  
$queryString = http_build_query([  
    'appid' => APPID,  
    'secret' => SECRET,  
    'js_code' => $code,  
    'grant_type' => GRANT_TYPE  
]);  
  
// 将查询字符串附加到URI  
$fullUrl = $uri . '?' . $queryString;  
  
// 初始化cURL会话  
$ch = curl_init($fullUrl);  
  
// 设置cURL选项  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
// 在生产环境中应该启用SSL证书验证  
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
// 如果需要，设置CA证书文件路径  
// curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');  
  
// 执行cURL会话  
$response = curl_exec($ch);  
  
// 检查是否有错误发生  
if (curl_errno($ch)) {  
    $error_msg = curl_error($ch);  
    echo "cURL Error: " . $error_msg;  
} else {  
    // 关闭cURL会话  
    curl_close($ch);  
  
    // 尝试将响应解析为JSON    
$decodedResponse = json_decode($response, true);    
    if (json_last_error() === JSON_ERROR_NONE) {    
        // 检查是否有errcode，如果有则输出code=0，否则输出code=1  
        if (isset($decodedResponse['errcode'])) {  
            $output = ['code' => 0, 'errcode'=>$decodedResponse['errcode'],'message' => 'Error occurred: ' . $decodedResponse['errmsg']];  
        } else {  
            // 假设openid是存在的（实际上应该检查openid是否存在）  
            $output = ['code' => 1, 'openid' => $decodedResponse['openid'], 'session_key' => $decodedResponse['session_key']];  
        }  
          
        header('Content-Type: application/json');    
        echo json_encode($output);    
    } else {    
        // 响应不是有效的JSON    
        echo "Invalid JSON Response: " . $response;    
    }    
}  
?>