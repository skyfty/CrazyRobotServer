<?php
$server='localhost';
$username='root';
$password='root';
$dbname='phpSql';
$charset='utf8';

$conn=mysqli_connect($server,$username,$password,$dbname);
if(!$conn){
    echo "数据库连接失败！";
    die();
}
else{
    print('数据库连接成功!');
    //die();
}
mysqli_set_charset($conn,$charset);
?>