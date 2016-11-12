<?php

function execsql($sql, $flag)
{
    //
    $server = 'localhost';
//    $server = '192.168.1.10';
    $server_port = '3306';
    $username = 'lottery';
    $password = 'caixiang518';
//    $username = 'root';
//    $password = 'root';
    $dbname = 'xklottery';
    $dsn = "mysql:host=" . $server . ";dbname=" . $dbname;
    //
    try {
        $db = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        $db = new PDO($dsn, $username, '');
    }
    try {
        if ($flag == 1) { //表示查询
            $query_order = $db->query($sql);
            $arr = $query_order->fetchAll();
            return $arr;
        } else if ($flag == 2) { //表示执行
            $db->exec($sql);
            return '';
        }

    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        return false;
    }
}
