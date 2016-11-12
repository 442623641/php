<?php
//
while (true) {
    $dt=date("H:i");

    if($dt>"08:00"&& $dt<"22:30"){
//陕西11�&#65533;5
        comm(0);
//排列�&#65533;
        comm(1);
//休眠
        sleep(60);
    }else{
        break;
    }
}

//
function exec_sql($sql, $flag)
{
    $server_address = 'localhost';
    //$my_serverport = '3306';

    $username = 'lottery';
    $password = 'caixiang518';
    $db_name = 'xklottery';
    $dsn = "mysql:host=" . $server_address . ";dbname=" . $db_name;
    try {
        //
        $db = new PDO($dsn, $username, $password);
        //
        if ($flag == 1) { //表示查询
            $query_order = $db->query($sql);
echo $sql."\n";
            $arr = $query_order->fetchAll();
            return $arr;
        } else if ($flag == 2) { //表示执行
            $db->exec($sql);
            return '';
        }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
    }
}

//�&#65533;6分之后运行，如果合买成功，则下单，否则，不处理（由撤单的服务处理�&#65533;
function comm($lotteryID)
{ //$lotteryID表示彩种编号
    //获取上一期的开奖时间，用以判断购彩是否失败
    $lastIssue = getIssue($lotteryID);
    //
//    $query = '';
//    $comment = '';
//    $arr1 = array(); //需要修改的字段
//    $arr2 = array(); //条件

//    $comment = '合买退�&#65533;';
    //
    $query = getOrder('lotteryjoint', 'ID,userID,ensure,money,sell,financeID', $lotteryID, $lastIssue);
    $mins = date("i");
    $min = $mins % 10;
    if($min>=6 && $min< 8) {
        foreach ($query as $v) {
             if ($v['money'] <= $v['sell'] + $v['ensure']) { //合买成功(需要加保底部分才成功的)，在6分以后运�&#65533;
                if ($v['money'] == $v['sell']) {
                    $money = $v['launcherBuy'];
                } else {
                    $money = $v['money'] - $v['sell'] + $v['launcherBuy'];
                }
                $id = date("Ymdhism") . "04";
                $lotteryJointID = $v["ID"];
                $userID = $v['userID'];
                $createDate = date("Y-m-d H:i:s");
                $updateDate = $createDate;
                //$state = '';
                $financeID = $v["financeID"];
                $sql = "insert into lotteryparticipants(ID,lotteryJointID,userID,money,createDate,financeID,state,updateDate) values('" . $id . "','" . $lotteryJointID . "'," . $userID . "," . $money . ",'" . $createDate . "','" . $financeID . "',0,'" . $updateDate . "')";
                exec_sql($sql, 2);
                //
                $sql = "update lotteryjoint set state=0 where ID='" . $v['ID'] . "'";
                exec_sql($sql, 2);  
        }else{
                //
                $sql = "update lotteryjoint set state=9 where ID='" . $v['ID'] . "'";
                exec_sql($sql, 2);
       }
    }

  }
}

//获取当前期号，用以判断购彩失败的订单
function getIssue($lotteryID)
{
    $sql = "select issue from lotteryissue where lotteryID=" . $lotteryID;
    $lastIssue = exec_sql($sql, 1);

    return $lastIssue[0]['issue'];
}

//
function getOrder($table, $sel, $lotteryID, $issue)
{
    $sql = "select " . $sel . " from " . $table . " where lotteryID=" . $lotteryID . " and state=8 and issue=" . $issue;
    $query = exec_sql($sql, 1);

    return $query;
}