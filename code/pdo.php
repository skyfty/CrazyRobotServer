<?php
    ///$dsn ="mysql:host=localhost;charset=utf8;dbname=phpsql";
    $dsn ="mysql:host=localhost;charset=utf8;dbname=defend2";
    $pdo = new PDO($dsn,'defend2','defend2',[  
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
    PDO::ATTR_EMULATE_PREPARES   => false,  
]);
    if(!$pdo){echo "数据库连接失败！";die();}
    //echo "数据库连接成功！！！！！！";
?>