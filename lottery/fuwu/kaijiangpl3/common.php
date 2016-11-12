<?php
set_time_limit(0);

// exec sql
function execsql($sql, $flag)
{
    //
//    $server = 'localhost';
    $server = 'localhost';
    $server_port = '3306';
//    $username = 'lottery';
//    $password = 'caixiang518';
    $username = 'lottery';
    $password = 'caixiang518';
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

//exec sql 2
function execsqls($sql)
{
    //
//    $server = 'localhost';
    $server = 'localhost';
    $server_port = '3306';
//    $username = 'caipiaokong';
//    $password = 'caixiang518';
    $username = 'caipiaokong';
    $password = 'caixiang518';
    $dbname = 'caipiaokong';
    $dsn = "mysql:host=" . $server . ";dbname=" . $dbname;
    //
    try {
        $db = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        $db = new PDO($dsn, $username, '');
    }
    try {
        $query_order = $db->query($sql);
        $arr = $query_order->fetchAll();

        return $arr;
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        return false;
    }
}

//lotteryID为彩种编号（1表示排列三）�&#65533;&#65533;
//$tableID为获取未开奖订单编号的表名
//$tableText为获取投注号码的表名
//$play为玩法名称：1自购�&#65533;&#65533;2追号�&#65533;&#65533;3合买
//$LotteryID为投注内容表中关联投注表ID的字段名
function comm($lotteryID, $tableID, $tableText, $play, $LotteryID)
{
    try {
        //11�&#65533;&#65533;5当前可以开奖的期号和号�&#65533;&#65533;
        $sql_issue = "select issue,num from currentlottery where lotteryID=" . $lotteryID;
        $query_issue = execsqls($sql_issue, 1);
        $issue = $query_issue[0]['issue'];
        $num = $query_issue[0]['num']; //开奖号�&#65533;&#65533;
//        echo $issue;exit;
        //
        $sql = '';
        if ($play == 1) {
            //判断是否有历史为开奖的记录，如果有，进行开�&#65533;&#65533;
            $sql_exist = "select ID,multiple,issue from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue<" . $issue;
            $query_exist = execsql($sql_exist, 1);
            if ($query_exist) {
                runLotteryHis($query_exist, 1, $tableText, $LotteryID);
            }
//            exit;
            //当前�&#65533;&#65533;
            $sql = "select ID,multiple from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue=" . $issue;
        } else if ($play == 2) { //追号的订单需要先判断当期之前有没有未开奖的订单
            //判断是否有历史为开奖的记录，如果有，进行开�&#65533;&#65533;
            $sql_exist_z = "select ID,chaseID,money,`index`,multiple,issue from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue<" . $issue;
            $query_exist_z = execsql($sql_exist_z, 1);
            if ($query_exist_z) {
                runLotteryHis($query_exist_z, 2, $tableText, $LotteryID);
            }
            //
            $sql = "select ID,chaseID,money,`index`,multiple from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue=" . $issue;
        } else if ($play == 3) {
            //
            $sql_exist_h = "select ID,multiple,money,take,issue from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue<" . $issue;
            $query_exist_h = execsql($sql_exist_h, 1);
            if ($query_exist_h) {
                runLotteryHis($query_exist_h, 3, $tableText, $LotteryID);
            }
            //
            $sql = "select ID,multiple,money,take from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue=" . $issue;
        }
        $query = execsql($sql, 1);
        //
        if ($query) {
            runLottery($query, $play, $tableText, $LotteryID, $num,$issue);
        }

        return true;
    } catch (Exception $e) {
//        $access=date('Y-m-d H:i:s');
//        error_log("[$access] '出错: ".$e->getMessage()."\n", 3, CX_API_LOG);

        return false;
    }
}

//执行开�&#65533;&#65533;
function runLottery($query, $play, $tableText, $LotteryID, $num,$issue)
{
    foreach ($query as $j => $k) {
        //ȡ��Ͷע����
        $sql_text = "select text from " . $tableText . " where " . $LotteryID . "='" . $k['ID'] . "'";
        $query_text = execsql($sql_text, 1);
        if ($play == 1) {
            getNum($query_text[0]['text'], $k['ID'], $num, 1, '', '', $k['multiple'], $issue);
        } else if ($play == 2) {
            getNum($query_text[0]['text'], $k['ID'], $num, 2, $k['chaseID'], $k['money'], $k['multiple'], $issue);
        } else if ($play == 3) {
            getNum($query_text[0]['text'], $k['ID'], $num, 3, $k['take'], $k['money'], $k['multiple'], $issue);
        }

    }
}

//执行历史开�&#65533;&#65533;
function runLotteryHis($query, $play, $tableText, $LotteryID)
{

    foreach ($query as $j => $k) {
        //ȡ��Ͷע����
        $sql_text = "select text from " . $tableText . " where " . $LotteryID . "='" . $k['ID'] . "'";
        $query_text = execsql($sql_text, 1);
        $num = getHisNum($k['issue']);
        if ($num) {
            if ($play == 1) {
                getNum($query_text[0]['text'], $k['ID'], $num, 1, '', '', $k['multiple'], $k['issue']);
            } else if ($play == 2) {
                getNum($query_text[0]['text'], $k['ID'], $num, 2, $k['chaseID'], $k['money'], $k['multiple'], $k['issue']);
            } else if ($play == 3) {
                getNum($query_text[0]['text'], $k['ID'], $num, 3, $k['take'], $k['money'], $k['multiple'], $k['issue']);
            }
        }
    }
}

//查询历史开奖号码，用于历史开�&#65533;&#65533;
function getHisNum($issue)
{
    $sql = "select LotteryValue from plsan where LotteryIssue='" . $issue . "'";
//    echo $sql."=============";
    $query = execsqls($sql);
    $num = $query[0]['LotteryValue']; //开奖号�&#65533;&#65533;
//    echo $num;
    return $num;
}

//根据玩法拆分号码
function getNum($param, $id, $num, $play, $chaseID, $money, $time,$issue)
{
    $array = json_decode($param, true);
    $bonus = 0; //中奖奖金
    $flag=0;
    for ($i = 0; $i < count($array); $i++) {
//        echo $array[$i][0];//玩法
        $isSplit = splitNum($array[$i][1]); //得到拆分后的号码
        if ($array[$i][0] == 0) { //直�&#65533;
            $flag = winZ($num, $isSplit); //$flag为中奖的注数
        } else if ($array[$i][0] == 1) { //�&#65533;&#65533;3
            $flag = win($num, $isSplit);
        } else if ($array[$i][0] == 2) { //�&#65533;&#65533;6
            $flag = win($num, $isSplit);
        }
        $bonus += getBonus($array[$i][0], $time, $flag); //中奖金额

    }
    operateState($bonus, $play, $id, $chaseID, $money, $num,$issue); //修改中奖状态和中奖金额
}

//是否需要拆分号�&#65533;&#65533;(排列�&#65533;&#65533;)
function splitNum($p2)
{ //$p2为投注号�&#65533;&#65533;
    $arrs = explode('|', $p2);
    $arr1 = array(); //第一位的投注号码
    $arr2 = array(); //第二位的投注号码
    $arr3 = array(); //第三位的投注号码
    foreach ($arrs as $k => $n) {
        $a = array();
        //判断是否多注
        $pos = strpos($n, ',');
        if ($pos) { //多注
            $a = explode(',', $n);

        } else {
            array_push($a, $n);
        }
        switch ($k) {
            case 0:
                $arr1 = $a;
                break;
            case 1:
                $arr2 = $a;
                break;
            case 2:
                $arr3 = $a;
                break;
        }
    }
    $array = array();
    foreach ($arr1 as $i) {
        foreach ($arr2 as $j) {
            foreach ($arr3 as $k) {
                array_push($array, $i . "," . $j . "," . $k);
            }
        }
    }

    //判断是否是多�&#65533;&#65533;
    return $array;
}

//判断是否中奖，不考虑直选，返回中奖的注�&#65533;&#65533;
function win($num, $array)
{ //$num为开奖号码，$array为投注号�&#65533;&#65533;
    $flag = 0; //是否中奖的标志位
    $winNum = explode(',', $num);
    foreach ($array as $m => $n) {
        $arr = explode(',', $n);
        if($winNum==$arr){
            $flag++;
        }
    }

    return $flag;
}

//直选是否中�&#65533;&#65533;
function winZ($num, $array)
{ //$num为开奖号码，$array为投注号�&#65533;&#65533;,$play为玩�&#65533;&#65533;
    $flag = 0; //是否中奖的标志位
    foreach ($array as $n) {
        if ($num == $n) {
            $flag++; //中奖标识,当有中奖号码出现，加�&#65533;&#65533;1�&#65533;&#65533;
        }
    }

    return $flag;
}

//计算奖金
function getBonus($play, $time, $num)
{ //$play：玩法，用来获取奖金�&#65533;&#65533;$time:投注倍数�&#65533;&#65533;$num:中奖号码的注�&#65533;&#65533;
    $reward = bonus($play) * $time * $num;

    return $reward;
}

//奖金
function bonus($play)
{
    $b=0;
    switch ($play) {
        case 0: //直�&#65533;
            $b = 1000;
            break;
        case 1: //组三
            $b = 346;
            break;
        case 2: //组六
            $b = 173;
            break;
    }

    return $b;
}

//修改中奖状态；如果是追号追中还需要撤�&#65533;&#65533;
function operateState($bonus, $flag, $id, $chaseID, $money, $num,$issue)
{ //$bonus为中奖金额；$flag为订单类型：自购、追号、合�&#65533;&#65533;;$id为订单编�&#65533;&#65533;
    //更改中奖状�&#65533;
    if ($bonus > 0) { //中奖
        operateMoney($bonus, $flag, $id, 3, $chaseID, $num);      
        
    } else { //未中�&#65533;&#65533;
        operateMoney($bonus, $flag, $id, 4, $chaseID, $num);
    }

    

    //合买奖金分配
    if ($flag == 3) {
        //取出lotteryparticipants表中lotteryJointID为订单编号的所有用�&#65533;&#65533;
        $sql_part = "select ID,money from lotteryparticipants where lotteryJointID='" . $id . "'";
        $query_part = execsql('$sql_part', 1);
        //如果奖金�&#65533;&#65533;0，则所有这些参与者的奖金均为0
        //
        foreach ($query_part as $q) {
            if ($bonus > 0) { //如果中奖，按照用户购买的比例分配奖金(除去发起者的提成)
                //$bonus-$bonus*$chaseID:这个是参与分配的奖金�&#65533;&#65533;$bonus*$chaseID是发起者的提成($chaseID在这里实际是提成比例)
                //先分配奖金，再将提成返给发起�&#65533;&#65533;
                //奖金�&#65533;&#65533;($bonus-$bonus*$chaseID)*$q['money']/$money
                updateParam('lotteryparticipants', 'winMoney', ($bonus - $bonus * $chaseID) * $q['money'] / $money, 'state', 3, 'ID', $q['ID']);
            } else { //未中�&#65533;&#65533;
                updateParam('lotteryparticipants', 'winMoney', 0, 'state', 4, 'ID', $q['ID']);
            }
        }
        //
        if ($bonus > 0) { //返还提成
            $sql_lead = "select userID from lotteryjoint where ID=" . $id;
            $query_lead = execsql($sql_lead, 1);
            //
            $sql_lead_dis = "update lotteryparticipants set winMoney=winMoney+" . $bonus . " where userID=" . $query_lead[0]['userID'] . " and lotteryJointID=" . $id;
            execsql($sql_lead_dis, 2);
        }
        //
    }

}

//判断追号是否完成
function isFinish($chaseID){
    $sql="select state from chaseuser where ID='".$chaseID."'";
    $query_state=execsql($sql,1);
    echo $query_state[0]['state'];
    if($query_state[0]['state']==6){//追号完成
        return true;
    }else{
        return false;
    }
}
//修改中奖状态和中奖金额
function operateMoney($bonus, $flag, $id, $f, $chaseID, $num)
{ //$bonus为中奖金额；$flag为订单类型：自购、追号、合�&#65533;&#65533;;$id为订单编�&#65533;&#65533;;$num为开奖号�&#65533;&#65533;
    //更改中奖金额
    switch ($flag) {
        case 1: //自购
            updateParam('lotteryuser', 'winMoney', $bonus, 'state', $f, 'ID', $id);
            updateParams('lotteryuserentity', 'winMoney', $bonus, 'state', $f, 'runNumbers', $num, 'lotteryUserID', $id);
            break;
        case 2: //追号
            updateParam('lotterychase', 'winMoney', $bonus, 'state', $f, 'ID', $id);
            updateParams('lotterychaseentity', 'winMoney', $bonus, 'state', $f, 'runNumbers', $num, 'lotteryUserID', $id);
            if ($bonus > 0) {
                $sql = "select isWinners,issues  from chaseuser where ID='" . $chaseID . "'";
                $cancel = execsql($sql, 1);
                if ($cancel[0]['isWinners'] == 1) { //中奖后停止追号的标识
                    $sql_update = "update chaseuser set state=6,winMoney=winMoney+" . $bonus . " where ID='" . $chaseID . "'";
                    execsql($sql_update, 2);
//
                    
                    $array = json_decode($cancel [0]['issues'], true);
                    foreach($array as $i=>$n){
                        if($n[2]==13){
                            $array[$i][2]=5;
                        }
                    }
                    $issues=json_encode($array);
                    $sql_is="update chaseuser set issues='".$issues."' where ID='".$chaseID."'" ;
                    execsql($sql_is,2);
                }else{
                    $sql_update = "update chaseuser set winMoney=winMoney+" . $bonus . " where ID='" . $chaseID . "'";
                    execsql($sql_update, 2);
                }
            }
            break;
        case 3: //合买
            updateParam('lotteryjoint', 'winMoney', $bonus, 'state', $f, 'ID', $id);
            updateParams('lotteryjointentity', 'winMoney', $bonus, 'state', $f, 'runNumbers', $num, 'lotteryJointID', $id); //整个方案的奖�&#65533;&#65533;
            //个人的奖�&#65533;&#65533;
            break;
        default:
            break;
    }
}

//根据玩法名称取出对应的userID
function getUser($play, $id)
{
    $table = '';
    switch ($play) {
        case 1:
            $table = 'lotteryuser';
            break;
        case 2:
            $table = 'lotterychase';
            break;
        case 3:
            $table = 'lotteryparticipants';
            break;
    }
    $sql_user = "select userID,winMoney from " . $table . " where ID=" . $id;
    $query_user = execsql($sql_user, 1);
    if(!$query_user){
        $query_user=execsql($sql_user, 1);
    }
    if(!$query_user){
        echo "查询".$table."表用户订�&#65533;".$id."时出�&#65533;";
        return "";
    }

    return $query_user;
}

//开奖后修改chaseuser表中已追期数chasePhase、当前期号currentIssue、实际投注金额realMoney
function operateChase($money, $chaseID)
{
    //
    $sql_query = "select issue from lotteryissue where lotteryID=1";
    $res = execsql($sql_query, 1);
    $currentIssue = $res[0]['issue'];
    //
    $sql_update = "update chaseuser set chasePhase=chasePhase+1,currentIssue=" . $currentIssue . ",realMoney=realMoney+" . $money . " where ID=" . $chaseID;
    execsql($sql_update, 2);
}

//修改数据库表1
function update($table, $key, $value, $k, $v)
{
    $sql = "update " . $table . " set " . $key . "=" . $value . " where " . $k . "=" . $v;

    execsql($sql, 2);
}

//修改数据库表2
function updateParam($table, $key1, $value1, $key2, $value2, $k, $v)
{
    $sql = "update " . $table . " set " . $key1 . "=" . $value1 . "," . $key2 . "=" . $value2 . " where " . $k . "='" . $v . "'";
//    echo $sql;
    execsql($sql, 2);
}

//修改数据库表3
function updateParams($table, $key1, $value1, $key2, $value2, $key3, $value3, $k, $v)
{
    $sql = "update " . $table . " set " . $key1 . "=" . $value1 . "," . $key2 . "=" . $value2 . "," . $key3 . "='" . $value3 . "' where " . $k . "='" . $v . "'";
//    echo $sql;
    execsql($sql, 2);
}

//修改开奖后财务流水中的数据，通过post传ID到接�&#65533;&#65533;
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
//    if ($response === false) {
//        throw new Exception("Problem reading data from $url, $php_errormsg");
//    }

    return $response;
}