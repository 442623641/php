<?php

//
//$res=issueStop(0);
// get issue
$lotteryArr = array(0, 5, 6); //彩种编号�&#65533;&#65533;0：陕�&#65533;&#65533;11�&#65533;&#65533;5,5：安�&#65533;&#65533;11�&#65533;&#65533;5,6：浙�&#65533;&#65533;11�&#65533;&#65533;5
$dt=date("Y-m-d H:i:s");
foreach ($lotteryArr as $a) {
    $res=issueStop($a,$dt);
//    echo $res;exit;
    if($res==0){
        $issue = getQH($a); //获取当前可以购买的期�&#65533;&#65533;
        $stopTime = getStopTime($a); //获取停止购买时间
        insertIssue($issue, $stopTime, $a);
    }else{
        $da = date('Ymd', strtotime($res));
        $da=substr($da,2,6);
        $issue=$da.'01';
        $stopTime=date('Y-m-d',strtotime($res));
        switch($a){
            case 0:
                $st=" 08:57:30";
                break;
            case 5:
                $st=" 08:37:30";
                break;
            case 6:
                $st=" 08:47:30";
                break;
        }
        $stopTime=$stopTime.$st;
        insertIssue($issue, $stopTime, $a);
    }
}

function insertIssue($issue, $stopTime, $lottery)
{
    $totalIssue=0;
    $initTime="";
    switch ($lottery) {
        case 0:
            $totalIssue = 79;
            $initTime = " 08:57:30";
            break;
        case 5:
            $totalIssue = 81;
            $initTime = " 08:37:30";
            break;
        case 6:
            $totalIssue = 80;
            $initTime = " 08:47:30";
            break;
    }
//    echo $issue.'***'.substr($issue, 6, 2);exit;
    if (substr($issue, 6, 2) == $totalIssue) {
        $now = date("Y-m-d");
        $dt1 = date("Ymd", strtotime("$now+1 day"));
        $dt2 = date("Y-m-d", strtotime("$now+1 day"));
        $res=issueStop($lottery,$dt1);
        if($res==0){
            $nextIssue = substr($dt1, 2, 6) . "01";
            $nextTime = $dt2 . $initTime;
        }else{
            $nextIssue = date("Ymd", strtotime($res)) . "01";
            $nextTime = $res . $initTime;
        }

    } else {
        $time = date("Y-m-d H:i");
        $nextTime = date("Y-m-d H:i", strtotime("$stopTime+10 min")) . ":30";
        $nextIssue = $issue + 1;
    }
//echo 'lottery'.$lottery.'**issue'.$issue.'***stop'.$stopTime.'**nissue'.$nextIssue.'**ntime'.$nextTime;exit;

    if ($issue) { // 得到正确的期�&#65533;&#65533;
        $sql = "select ID from lotteryissue where lotteryID=" . $lottery . " and issue=" . $issue; //查询数据库中是否存在�&#65533;&#65533;
        $exist = exec_sql($sql, 1);
        if (!$exist) {
            //更新最新期�&#65533;&#65533;
            $query = "update  `lotteryissue` set issue=" . $issue . ",stopTime='" . $stopTime . "',nextTime='" . $nextTime . "',nextIssue=" . $nextIssue . " where lotteryID=" . $lottery;
            exec_sql($query, 2);
        }
    }
}

//获取当前可以购买的期�&#65533;&#65533;
function getQH($lottery) //陕西11�&#65533;&#65533;5,早上9点开奖，晚上10点结束，共计79�&#65533;&#65533;,休市期间不生成期�&#65533;&#65533;
{
    $init_dt1 = "";
    $init_dt2 = "";
    $init_dt4 = "";
    $init_q=0;
    $less_nine=0;
    switch ($lottery) {
        case 0:
            $init_dt1 = " 08:57:30";
            $init_dt2 = " 21:58";
            $init_dt4 = " 09:00:00";
            $init_q = 2;
            break;
        case 5:
            $init_dt1 = " 08:37:30";
            $init_dt2 = " 21:58";
            $init_dt4 = " 08:40:00";
            $init_q = 4;
            $less_nine=2;
            break;
        case 6:
            $init_dt1 = " 08:47:30";
            $init_dt2 = " 21:58";
            $init_dt4 = " 08:50:00";
            $init_q = 3;
            $less_nine=3;
            break;
    }
    //
    $now = date("Y-m-d");
    $dt1 = date("Y-m-d", strtotime("$now+1 day")) . $init_dt1;
    $dt2 = date("Y-m-d") . $init_dt2;
    $dt3 = date("Y-m-d") . $init_dt1;
    $dt4 = date("Y-m-d") . $init_dt4;
    $nt = date("Y-m-d H:i");
    $date = date('Ymd');
    if ($dt2 < $nt && $nt < $dt1) { //
        $date = date("Ymd", strtotime("$date+1 day"));
        //
        $res=issueStop($lottery,$date);
        if($res>0){
            $issue=$res."01";
        }else{
            //
            $date = substr($date, 2, 6);
            $issue = $date . "01";
        }
    } else if ($nt > $dt3 && $nt < $dt4) {
        $date = substr($date, 2, 6);
        $issue = $date . "02";
    } else  if($nt > $dt4 && $nt < $dt2){
        //
        $date = substr($date, 2, 6);
//    $time=date('H:i:s');
        $hour = date('H');
        $issue = null;
        if ($hour >= 9) {
            $h = $hour - 9;
            $min = date('i') + 2; //提前两分钟停止售�&#65533;&#65533;
            $t = $h * 60 + $min;
            $q = floor($t / 10) + $init_q;
            if ($q < 10) {
                $q = '0' . $q;
            }
            $issue = $date . $q;
        } else if ($hour >= 8) {//针对安徽11�&#65533;&#65533;5
            $min = date('i') + 2; //提前两分钟停止售�&#65533;&#65533;
            $q = floor($min / 10) - $less_nine;
            $q = '0' . $q;
            $issue = $date . $q;
        }
    }

    return $issue;
}

//获取停止购买时间
function getStopTime($lottery)
{
    $init_dt1="";
    $init_dt2="";
    $init_st="";
    switch($lottery){
        case 0:
            $init_dt1=" 08:58";
            $init_dt2=" 21:58";
            $init_st=" 08:57:30";
            break;
        case 5:
            $init_dt1=" 08:38";
            $init_dt2=" 21:58";
            $init_st=" 08:37:30";
            break;
        case 6:
            $init_dt1=" 08:58";
            $init_dt2=" 21:58";
            $init_st=" 08:47:30";
            break;
    }

    //当前时间
    $time = date("Y-m-d H:i");
    $now = date("Y-m-d");
    $dt1 = date("Y-m-d", strtotime("$now+1 day")) . $init_dt1;
    $dt2 = $now . $init_dt2;
    if ($time > $dt2 && $time < $dt1) {
        $res=issueStop($lottery,$dt1);
        if($res>0){
            $stopTime = $res . $init_st;
        }else{
            $stopTime = date("Y-m-d", strtotime("$now+1 day")) . $init_st;
        }
    } else {
        $min = date('i');
        $dt = $min % 10;
        $pt = 0;
        if ($dt >= 7) {
            $pt = 17 - $dt;
        } else {
            $pt = 7 - $dt;
        }
        //停止购买时间
        $stopTime = date("Y-m-d H:i", strtotime("$time+$pt min")) . ":30";
    }

    return $stopTime;
}

// exec sql
function exec_sql($sql, $flag)
{
    $server_address = 'localhost';
//    $server_address = '192.168.1.10';
    //$my_serverport = '3306';
//    $username = 'apiadmin';
//    $password = '123465';
    $username = 'lottery';
    $password = 'caixiang518';
//    $username = 'root';
//    $password = 'root';
    $db_name = 'xklottery';
//    $db_name = 'vlottery';
    //	print_r($_SERVER);
    //	if(isset($_SERVER['SERVER_NAME'])) echo $_SERVER['SERVER_NAME'];
    $dsn = "mysql:host=" . $server_address . ";dbname=" . $db_name;
    try {
        $db = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        $db = new PDO($dsn, $username, '');
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

//休市
function issueStop($lottery, $dt)
{
//        $sql = "select beginDate,endDate from lotterystop where lotteryID=" . $lottery . " and '" . $dt . "'>=beginDate and '" . $dt . "'<=endDate";
    $sql = "select beginDate,endDate from lotterystop where lotteryID=" . $lottery ;
    $query = exec_sql($sql, 1);

    if (empty($query[0]["endDate"])) {
        return 0;
    } else {
        $date = substr($query[0]["endDate"], 0, 10);
        $dtD=date("Y-m-d",strtotime($dt));
        $dtT=date("H:i:s",strtotime($dt));

        $dtb=date("Y-m-d", strtotime("$dt+1 day"));
        $dte = date("Y-m-d", strtotime("$date+1 day"));
        $begin=substr($query[0]["beginDate"], 0, 10);

        if($dtD>=$begin&&$dtD<=$date){
//            echo "OK1";exit;
            return $dte;
        }else if($dtb==$begin){
            $time='';
            switch($lottery){
                case 0:
                    $time='21:57:30';
                    break;
                case 5:
                    $time='21:57:30';
                    break;
                case 6:
                    $time='21:57:30';
                    break;
            }
//            echo "OK2";exit;
            if($dtT>=$time){
                return $dte;
            }
        }

        return 0;
    }
}