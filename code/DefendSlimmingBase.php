<?php
    class Base{
        public static function Select($tablename,$pdo){
            $sql="select * from ".$tablename;
            $result=$pdo->query($sql);
            $data=$result->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }
    }
   