<?php
set_time_limit(0);
//comm(0,'lotterychase','lotterychaseentity',2,'lotteryUserID');
// exec sql

while (true) {
    $dt = date("H:i");
    if ($dt > "08:00" && $dt < "22:30") {
        getOrder();
        sleep(30);
    } else {
        break;
    }
}

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

//根据当前开奖的追号订单，获取主表chaseuser中的数据
function getOrder()
{
    try {
        //
        $sql_getOrders = "select * from chaseuser where state=7";
        $query_orders = execsql($sql_getOrders, 1);

        foreach ($query_orders as $j => $m) {
            $query_issues = json_decode($m['issues']);
            $lotteryID = $m['lotteryID'];
            $userID = $m['userID'];
            $money_total = $m['money'];
            $financeID = $m['financeID'];
            $text = $m['text'];
            $time = 0;
            $chaseID = $m['ID'];
            //
            $query_issue = "select issue from lotteryissue where lotteryID=" . $lotteryID;
            $query = execsql($query_issue, 1);
            $issue = $query[0]['issue'];
            //ji suan zong bei shu
            $conFlag=0;//判断是否有未下单的订单存在，没有，追号完成
            foreach ($query_issues as $i => $n) {
                $time += $n[1];
                if($n[2] == 13){
                    $conFlag++;
                }
            }
            if($conFlag==0){
                updateChaseState($chaseID);
                continue;
            }
            //
            foreach ($query_issues as $i => $n) {
                if ($n[0] == $issue && $n[2] == 13) {
                    $getFlag = selectOrder($chaseID,$i, $query_issues);
                    if ($getFlag == 1||$m['isWinners'] ==0) {
                        //上一期已经开奖或者手工撤单
                        $index = $i + 1;
                        $multiple = $n[1];
                        $money = $money_total * $multiple / $time;
                        $flag = insertOrder($chaseID, $lotteryID, $index, $userID, $issue, $multiple, $money, $financeID, $text);
                        if ($flag) {
                            $query_issues[$i][2] = 0;
                            $issues = json_encode($query_issues);
                            updateOrder($issues, $issue, $money, $chaseID);
                            if ($i + 1 == count($query_issues)) {
                                updateChaseState($chaseID);
                            }
                        }
                        break;
                    }
                } else if (($n[0] < $issue && $n[2] == 13)||($i+1==count($query_issues)&&$n[0] < $issue)) {
//                    $isDeal=selectOrderState($chaseID, $query_issues);
//                    if ($m['isWinners'] == 1 && $isDeal==1)
                        if ($m['isWinners'] == 1) {
                        for ($j = $i; $j < count($query_issues); $j++) {
                            if ($query_issues[$j][2] == 13) {
                                $query_issues[$j][2] = 11;
                            }
                        }
                        $issues = json_encode($query_issues);
                        updateChase($issues, $chaseID);
                        //短信提醒
                        $mess = '';
                        switch ($lotteryID) {
                            case 0:
                                $mess = '陕西11选5';
                                break;
                            case 1:
                                $mess = '排列三';
                                break;
                            case 2:
                                $mess = '排列五';
                                break;
                            case 3:
                                $mess = '七星彩';
                                break;
                            case 4:
                                $mess = '大乐透';
                                break;
                            case 5:
                                $mess = '安徽11选5';
                                break;
                            case 6:
                                $mess = '浙江11选5';
                                break;

                        }
                        $data = 'user=' . $userID . '&message=您购买的' . $mess . '第' . $n[0] . '期('.$chaseID.')出票失败，请及时查看并重新购买';
                        $apiUrl = "http://api.xiangw.com.cn/";
                        $response = do_post_request($apiUrl . "sms/send", $data);

                        break;
                    }
                }
            }
        }

    } catch (Exception $e) {
        echo $e;
    }
}


//判断是否继续追号
function selectOrder($chaseID,$index, $issues)
{
    $flag = 0;
    $countIs=count($issues);
    //
    for ($i = $countIs - 1; $i >= 0; $i--) {

        if ($issues[$i][2] == 0) {
            for ($j = $i; $j < $index; $j++) {
                if ($issues[$j][2] == 13) { //之前有未下单的
                    return $flag;
                }
            }
            $sql_state = "select state from lotterychase where chaseID='" . $chaseID . "' and issue='" . $issues[$i][0] . "'";
            $query_state = execsql($sql_state, 1);
            if ($query_state[0]['state'] == 3) {
                $sql_cancel = "select isWinners from chaseuser where ID='" . $chaseID . "'";
                $cancel = execsql($sql_cancel, 1);
                if ($cancel[0]['isWinners'] == 0) { //中奖后停止追号的标识
                    //
                    $flag = 1;
                }
            } else if ($query_state[0]['state'] == 4) {
                $flag = 1;
            }
            return $flag;
        }

    }

    return $flag;
}

function selectOrderState($chaseID, $issues)
{
    $flag = 0;
    //
    for ($i = count($issues) - 1; $i >= 0; $i--) {
        if ($issues[$i][2] == 0) {
            $sql_state = "select state from lotterychase where chaseID='" . $chaseID . "' and issue='" . $issues[$i][0] . "'";
            $query_state = execsql($sql_state, 1);

            if ($query_state[0]['state'] >= 3) {
                $flag = 1;

                return $flag;
            }
        }
    }

    return $flag;
}

//下单
function insertOrder($chaseID, $lotteryID, $index, $userID, $issue, $multiple, $money, $financeID, $text)
{
    try {
        //ID、chaseID、index、lotteryID、userID、state、issue、multiple、money、financeID、flag、text
        $dt = date("Y-m-d H:i:s");
        $id = createID($lotteryID);
        $sql_order = "insert into lotterychase(ID,chaseID,`index`,lotteryID,userID,state,issue,multiple,money,financeID,createDate) values('" . $id . "','" . $chaseID . "'," . $index . "," . $lotteryID . "," . $userID . ",0," . $issue . "," . $multiple . "," . $money . ",'" . $financeID . "','" . $dt . "')";
        $sql_entity = "insert into lotterychaseentity(ID,lotteryUserID,state,money,text,createDate) values(" . $id . "," . $id . ",0,'" . $money . "','" . $text . "','" . $dt . "')";
//        echo $sql_order;
//        echo "**********";
//        echo $sql_entity;
        execsql($sql_order, 2);
        execsql($sql_entity, 2);
    } catch (Exception $e) {
        return false;
    }
    return true;
}

//更改订单状态
function updateOrder($issues, $issue, $money, $id)
{
    //issues中当前期状�&#65533;
    $sql = "update chaseuser set issues='" . $issues . "',chasePhases=chasePhases+1,currentIssue='" . $issue . "',realMoney=realMoney+" . $money . " where ID='" . $id . "'";
    execsql($sql, 2);
}

//
function updateChase($issues, $id)
{
    $sql = "update chaseuser set issues='" . $issues . "',state=6 where ID='" . $id . "'";
    execsql($sql, 2);
}

//
function updateChaseState($id)
{
    $sql = "update chaseuser set state=6 where ID='" . $id . "'";
    execsql($sql, 2);
}

//create ID
function createID($lotteryID)
{
    $dt = date("Ymdhism");
    $r = rand(10000, 90000);
    $id = $dt . $r . $lotteryID . '3';

    return $id;
}

function do_post_request($url, $data, $optional_headers = null)
{
    $params = array('http' => array(
        'method' => 'POST',
        'content' => $data
    ));
    if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
    }
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
    }
    return json_decode($response, true);
}