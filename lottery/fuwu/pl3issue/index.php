<?php
//

//
$arrs = getQH();
$issue = $arrs["issue"]; //获取当前可以购买的期�&#65533;
$stopTime = $arrs["stopTime"]; //获取停止购买时间
$nextTime=$arrs['nextTime'];
$nextIssue=$arrs['nextIssue'];
//echo $issue.'***'.$stopTime.'***'.$nextIssue.'***'.$nextTime;exit;
//
$sql = 'select ID from lotteryissue where ID=1 and issue=' . $issue; //查询数据库中是否存在
$exist = exec_sql($sql, 1);
if (!$exist) {
    //更新最新期�&#65533;
    $query = "update  `lotteryissue` set issue=" . $issue . ",stopTime='" . $stopTime . "',nextIssue=".$nextIssue.",nextTime='".$nextTime."' where lotteryID=1";
    $count = exec_sql($query, 2);
    echo date("Y-m-d").":".$query;
}

//获取当前可以购买的期号和停止购买时间
function getQH() //排列三晚�&#65533;8点停止销售，休市期间不生成期号，期号的前两位是年份的后两位，期号的后三位是编�&#65533;
{
    $arr = array();
    $stopTime = getStopTime(1);
    $nextTime = getStopTime(2);
    $issue = getIssue();
//        echo 'issue:'.$issue;
    $nextIssue=$issue+1;
    //判断是否休市
    $sql = "select beginDate,endDate from lotterystop where lotteryID=1";
    $query = exec_sql($sql, 1);

    if (!empty($query[0]['beginDate'])) {
        $n = date("Y-m-d");
        $now = date("Y-m-d", strtotime("$n+1 day"));
        $tom = date("Y-m-d", strtotime("$n+2 day"));
        $beginDate = $query[0]['beginDate'];
        $beginDate1 = substr($query[0]['beginDate'],0,10);
        $endDate1 = $query[0]['endDate'];
        $endDate2 = substr($endDate1, 0, 10);
        $endDate = date("Y-m-d", strtotime("$endDate2+1 day"));
        $endDates = date("Y-m-d", strtotime("$endDate2+2 day"));
//        echo $now."**".$beginDate;exit;
        if ($tom >= $beginDate1 && $tom <= $endDate2 && $now < $beginDate1) { //第三天休�&#65533;
            $flag=judgeTime();
            if(!$flag){
                $nextTime = $endDate . " 20:00";
            }
//            echo "OK1";exit;
        } else if ($beginDate1 <= $now && $now <= $endDate2) { //第二天休市，则生成休市后两天的停购时�&#65533;
            $stopTime = $endDate . " 20:00";
            $nextTime = $endDates . " 20:00";
//            echo "OK2";exit;
        }
    }
//    echo "OK3";exit;
    if(substr($nextTime,5,5)=='01-01'){
        $y=date("Y");
        $y=substr($y,2,2);
        $y++;
        $nextIssue=$y.'001';
    }

    $arr['stopTime'] = $stopTime;
    $arr['nextTime'] = $nextTime;
    $arr['issue'] = $issue;
    $arr['nextIssue']=$nextIssue;

    return $arr;
}

//获取期号
function getIssue()
{
    //
    $judgeTime=date("H:i:s");
    //
    $now = date("Ymd");
    if($judgeTime>'19:29:00'){//晚上七点半之前启动服务的生成当天的期号。否则。生成第二天的期�&#65533;
        $now = date("Ymd", strtotime("$now+1 day"));
    }
    $y = substr($now, 2, 2);
    $sub_now = substr($now, 0, 4);
    $date = $sub_now . '0101';
    $date_now = strtotime($now);
    $date_begin = strtotime($date);

    $days = ceil(($date_now - $date_begin) / 86400)+1;
    //
    $sql = 'select beginDate,endDate from lotterystop where lotteryID=1';
    $query = exec_sql($sql, 1);

    if(!empty($query[0]['beginDate'])){
        $date3 = strtotime($query[0]['beginDate']);
        $date5=$query[0]['endDate'];
        $date4 = strtotime($date5);
        $day_stop = ceil(($date4 - $date3) / 86400);
        $sub_stop = substr($query[0]['beginDate'], 0, 4);
        if ($sub_now == $sub_stop) {
            $today=date('Y-m-d');
            $today1=date('Y-m-d',strtotime("$today+1 day"));
            if($today1>=$query[0]['beginDate']&&$today<=$query[0]['endDate']){
                $ed=date('Y-m-d',strtotime("$date5+1 day"));
                $eds=strtotime($ed);
                $days=ceil(($eds - $date_begin) / 86400)-$day_stop;
            }else if($today>=$query[0]['endDate']){
                $days=$days-$day_stop;
            }
        }
    }
    //
    if($days<10){
        $days='00'.$days;
    }else if($days<100){
        $days='0'.$days;
    }

    //
    $issue = $y . $days;

    return $issue;
}

//获取停止购买时间
function getStopTime($i)
{
    //
//    $judgeTime=date("H:i:s");
    $flag=judgeTime();
    //当前时间
    $now = date("Y-m-d");
    $stopTime = date("Y-m-d", strtotime("$now+" . $i . " day")) . " 20:00";
    if($flag){//晚上七点半之前启动服务的生成当天的期号。否则。生成第二天的期�&#65533;
        switch($i){
            case 1:
                $stopTime = $now. " 20:00";
                break;
            case 2:
                $stopTime = date("Y-m-d", strtotime("$now+1 day")) . " 20:00";
                break;
        }
    }


    return $stopTime;
}

function judgeTime(){
    $time=date("H:i:s");
    if($time<'19:30:00'){
        return true;
    }

    return false;
}

// exec sql
function exec_sql($sql, $flag)
{
    $server_address = 'localhost';
//    $server_address = '192.168.1.10';
    //$my_serverport = '3306';
//    $username = 'lottery';
//    $password = 'CAIxiang@518';
//    $db_name = 'lottery';
    $username = 'lottery';
    $password = 'caixiang518';
    $db_name = 'xklottery';
//    $username = 'root';
//    $password = 'root';
//    $db_name = 'vlottery';
    //	print_r($_SERVER);
    //	if(isset($_SERVER['SERVER_NAME'])) echo $_SERVER['SERVER_NAME'];
    $dsn = "mysql:host=" . $server_address . ";dbname=" . $db_name;
    try {
        $db = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        echo "link to : $dsn ,$username, $password \n";
        print "Error!: " . $e->getMessage() . "<br/>\n";
    }
    try {
//        $count = $db->exec($sql);
//        return $count;
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