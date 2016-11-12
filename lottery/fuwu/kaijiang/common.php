<?php
set_time_limit(0);

// exec sql
function execsql($sql, $flag)
{
    //
    $server = 'localhost';
//    $server = '192.168.1.10';
    $server_port = '3306';
    $username = 'lottery';
    $password = 'caixiang518';
//    $username = 'root';
//     $password = 'root';
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
    $server = 'localhost';
//    $server = '192.168.1.10';
    $server_port = '3306';
    $username = 'caipiaokong';
    $password = 'caixiang518';
//    $username = 'root';
//    $password = 'root';
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

//lotteryID为彩种编号（1表示11�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;5）；
//$play为玩法名称：1自购�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;2追号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;3合买
function comm($lotteryID,$play)
{
    try {
        //11选5当前可以开奖的期号和号码
        $sql_issue = "select issue,num from currentlottery where lotteryID=" . $lotteryID;
//        echo $sql_issue;exit;
        $query_issue = execsqls($sql_issue, 1);
        $issue = $query_issue[0]['issue'];
        $num = $query_issue[0]['num']; //开奖号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
//        echo $issue;exit;
//        error_log('******issue*****'.$issue,3, '/var/tmp/ex_log.log');
        //
        $sql = '';
        if ($play == 1) {
            //判断是否有历史为开奖的记录，如果有，进行开�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            $sql_exist = "select a.ID,a.multiple,a.issue,b.`text` from lotteryuser a inner join lotteryuserentity b on b.lotteryUserID=a.ID where a.state<3 and a.lotteryID=" . $lotteryID . " and a.issue<" . $issue;
            $query_exist = execsql($sql_exist, 1);
//            error_log('*****lishizi*****'.$sql_exist.'*****lishizi*****',3, '/var/tmp/ex_log.log');
            if ($query_exist) {
                runLotteryHis($query_exist, 1, $lotteryID);
            }
//            exit;
            //当前�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            $sql = "select a.ID,a.multiple,a.issue,b.`text` from lotteryuser a inner join lotteryuserentity b on b.lotteryUserID=a.ID where a.state<3 and a.lotteryID=" . $lotteryID . " and a.issue=" . $issue;
        } else if ($play == 2) { //追号的订单需要先判断当期之前有没有未开奖的订单
            //判断是否有历史为开奖的记录，如果有，进行开�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
//            $sql_exist_z = "select ID,chaseID,money,`index`,multiple,issue from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue<" . $issue;
            $sql_exist_z = "select a.ID,a.chaseID,a.money,a.multiple,a.issue,a.lotteryID,d.`text` from lotterychase a
             INNER join lotterychaseentity d on  d.lotteryUserID=a.ID
            where a.state<3 and a.lotteryID=" . $lotteryID . " and a.issue<" . $issue;
//            error_log('*****lishizhuihao*****'.$sql_exist_z.'*****lishizhuihao*****',3, '/var/tmp/ex_log.log');
            $query_exist_z = execsql($sql_exist_z, 1);
            if ($query_exist_z) {
                runLotteryHis($query_exist_z, 2, $lotteryID);
            }
            //
//            $sql = "select ID,chaseID,money,`index`,multiple from " . $tableID . " where state<3 and lotteryID=" . $lotteryID . " and issue=" . $issue;
            $sql = "select a.ID,a.chaseID,a.money,a.multiple,a.issue,a.lotteryID,d.`text` from lotterychase a
             INNER join lotterychaseentity d on d.lotteryUserID=a.ID
            where a.state<3 and a.lotteryID=" . $lotteryID . " and a.issue=" . $issue;
        }
//        error_log('*******dangqianqi*******'.$issue.'*******dangqianqi*******',3, '/var/tmp/ex_log.log');
        $query = execsql($sql, 1);
        //
        if ($query) {
            runLottery($query, $play, $num, $issue,$lotteryID);
        }

        return true;
    } catch (Exception $e) {
//        $access=date('Y-m-d H:i:s');
//        error_log("[$access] '出错: ".$e->getMessage()."\n", 3, CX_API_LOG);

        return false;
    }

}

//执行开�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
function runLottery($query, $play, $num, $issue,$lottery)
{
    foreach ($query as $j => $k) {
        //取出投注内容
        if ($play == 1) {
            getNum($k['text'], $k['ID'], $num, 1, '', '', $k['multiple'], $issue,$lottery);
        } else if ($play == 2) {
            getNum($k['text'], $k['ID'], $num, 2, $k['chaseID'], $k['money'], $k['multiple'], $issue,$lottery);
        }

    }

}

//执行历史开�&#65533;&#65533;
function runLotteryHis($query, $play, $lottery)
{
    //
    foreach ($query as $j => $k) {
        //取出投注内容
        $num = getHisNum($k['issue'],$lottery);
//        error_log('++++++++'.$num,3, '/var/tmp/ex_log.log');
        if ($num) {
            if ($play == 1) {
                getNum($k['text'], $k['ID'], $num, 1, '', '', $k['multiple'], $k['issue'],$lottery);
            } else if ($play == 2) {
                getNum($k['text'], $k['ID'], $num, 2, $k['chaseID'], $k['money'], $k['multiple'], $k['issue'],$lottery);
            }
        }
    }

}

//查询历史开奖号码，用于历史开�&#65533;&#65533;
function getHisNum($issue,$lottery)
{
    $table='';
    switch($lottery){
        case 0://����11ѡ5
            $table='sxsyxw';
            break;
        case 5://����11ѡ5
            $table='ahsyxw';
            break;
        case 6://�㽭11ѡ5
            $table='zjsyxw';
            break;
    }
    $sql = "select LotteryValue from ".$table." where LotteryIssue='" . $issue . "'";
    $query = execsqls($sql);
//    error_log('--------getHisNum----------'.$sql,3, '/var/tmp/err_log.log');
//    error_log('--------getHisNum----------'.$query[0]['LotteryValue'],3, '/var/tmp/err_log.log');

    $num='';
    if($query){
        $num = $query[0]['LotteryValue']; //开奖号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
    }

    return $num;
}

//根据玩法拆分号码
function getNum($param, $id, $num, $play, $chaseID, $money, $time, $issue,$lottery)
{
    $array = json_decode($param, true);
    $bonus = 0; //中奖奖金
    for ($i = 0; $i < count($array); $i++) {
//        echo $array[$i][0];//彩种
        //$isSplit = splitNum($array[$i][0], $array[$i][2]); //得到拆分后的号码
    if($array[$i][1]==2){//����
            $isSplit = splitD($array[$i][0], $array[$i][2]);
        }else{//
            $isSplit = splitNum($array[$i][0], $array[$i][2]); //�õ���ֺ�ĺ���

//            error_log('============='.$isSplit,3, '/var/tmp/errs_log.log');
        }
        if ($array[$i][0] == 0 || $array[$i][0] == 8 || $array[$i][0] == 10) { //直�&#65533;
            $flag = winZ($num, $isSplit, $array[$i][0]); //$flag为中奖的注数
        }else if ($array[$i][0] == 9 || $array[$i][0] == 11) {
            $flag = winZu($num, $isSplit, $array[$i][0]);
        }  else { //组�&#65533;
            $flag = win($num, $isSplit);
        }
        $bonus += getBonus($lottery,$array[$i][0], $time, $flag); //中奖金额

    }
//    error_log('============='.$bonus,3, '/var/tmp/errs_log.log');
    operateState($bonus, $play, $id, $chaseID, $money, $num, $issue); //修改中奖状态和中奖金额
}

//��ֵ���
function splitD($p1,$p2){//$p1Ϊ���淨��$p2ΪͶע����
    if ($p1 < 8) { //$num��ÿ���淨��ɵĺ���ĸ������磺������5��������ɣ��˴���������1
        $num = $p1;
    } else if ($p1 < 10) {
        $num = 1;
    } else {
        $num = 2;
    }
    $arr=explode('#',$p2);//$arr[0]Ϊ���룬$arr[1]Ϊ���룬Ҫ����������ɸ�����������淨Ҫ���Ͷע�������
    $count=$num-substr_count($arr[0],',');
    $arr1=explode(',',$arr[1]);
    $arrs=splits($arr1, $count);
    $array=array();
    foreach($arrs as $i=>$n){
        array_push($array, $arr[0].",".$n);
    }

    return $array;
}

//是否需要拆分号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;(11�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;5)
function splitNum($p1, $p2)
{ //$p1为子玩法的ID�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;$p2为投注号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
//    $num=0;
    if ($p1 < 8) { //$num：每种玩法组成的号码的个数，例如：任五由5个号码组成，此处个数均减了�&#65533;1�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
        $num = $p1;
    } else if ($p1 < 10) {
        $num = 1;
    } else {
        $num = 2;
    }
    //
    $pos = strpos($p2, '|'); //判断是否多注
//    echo $pos;
//    exit(0);
    if ($pos) {
        $arrs=explode('|',$p2);
        $array = array();
        $count=count($arrs);
        switch ($count) {
            case 2:
                $pp1=explode(',', $arrs[0]);
                $pp2=explode(',', $arrs[1]);
                var_dump($pp1);
                for ($i = 0; $i < count($pp1); $i++)
                    for ($j = 0; $j < count($pp2); $j++)
                        array_push($array, $pp1[$i] . ',' . $pp2[$j]);
                break;
            case 3:
                $pp1=explode(',', $arrs[0]);
                $pp2=explode(',', $arrs[1]);
                $pp3=explode(',', $arrs[2]);
                for ($i = 0; $i < count($pp1); $i++)
                    for ($j = 0; $j < count($pp2); $j++)
                        for($k = 0; $k < count($pp3); $k++)
                            array_push($array, $pp1[$i] . ',' . $pp2[$j] . ',' . $pp3[$k]);
                break;
        }
    } else {
        //
        $count = substr_count($p2, ','); //号码个数(�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;1才等于实际个数，因为玩法也要�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;1才能得到个数，所以不再加1)
        $arr = explode(',', $p2);
        if ($count > $num) { //需要拆�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            $array = splits($arr, $num + 1);
        } else { //不需要拆�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            $array = array();
            array_push($array, $p2);
        }
    }

    return $array;
}

//11�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;5拆分号码
function splits($p1, $p2)
{ //$p1为投注号码，$p2为拆分成的个�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
    $arr = array();
    if($p2>=5){
        $p2=5;
    }
    switch ($p2) {
        case 1:
            for ($i = 0; $i < count($p1); $i++)
                array_push($arr, $p1[$i]);
            break;
        case 2:
            for ($i = 0; $i < count($p1) - 1; $i++)
                for ($j = $i + 1; $j < count($p1); $j++)
                    array_push($arr, $p1[$i] . ',' . $p1[$j]);
            break;
        case 3:
            for ($i = 0; $i < count($p1) - 2; $i++)
                for ($j = $i + 1; $j < count($p1) - 1; $j++)
                    for ($k = $j + 1; $k < count($p1); $k++)
                        array_push($arr, $p1[$i] . ',' . $p1[$j] . ',' . $p1[$k]);
            break;
        case 4:
            for ($i = 0; $i < count($p1) - 3; $i++)
                for ($j = $i + 1; $j < count($p1) - 2; $j++)
                    for ($k = $j + 1; $k < count($p1) - 1; $k++)
                        for ($m = $k + 1; $m < count($p1); $m++)
                            array_push($arr, $p1[$i] . ',' . $p1[$j] . ',' . $p1[$k] . ',' . $p1[$m]);
            break;
        case 5:
            for ($i = 0; $i < count($p1) - 4; $i++)
                for ($j = $i + 1; $j < count($p1) - 3; $j++)
                    for ($k = $j + 1; $k < count($p1) - 2; $k++)
                        for ($m = $k + 1; $m < count($p1) - 1; $m++)
                            for ($n = $m + 1; $n < count($p1); $n++)
                                array_push($arr, $p1[$i] . ',' . $p1[$j] . ',' . $p1[$k] . ',' . $p1[$m] . ',' . $p1[$n]);
            break;

    }

    return $arr;
}

//判断是否中奖，不考虑直选，返回中奖的注�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
function win($num, $array)
{ //$num为开奖号码，$array为投注号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
    $flag = 0; //是否中奖的标志位
    $winNum = explode(',', $num);

    foreach ($array as $m => $n) {
        $arr = explode(',', $n);
        if (count($winNum) > count($arr)) { //开奖号码比玩法号码多的情况，如：任二、任三等
            if ($arr == array_intersect($arr, $winNum)) {
                $flag++; //中奖标识,当有中奖号码出现，加�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;1�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            }
        } else { //开奖号码比玩法号码少的情况，如：任六、任七等
            if ($winNum == array_intersect($winNum, $arr)) {
                $flag++; //中奖标识,当有中奖号码出现，加�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;1�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
            }
        }
    }

    return $flag;
}
function winZu($num, $array, $play)
{ //$numΪ�������룬$arrayΪͶע��&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
    $flag = 0; //�Ƿ��н��ı�־λ
    if ($play == 11) {
        $num = substr($num, 0, 8);
    } else if ($play == 9) {
        $num = substr($num, 0, 5);
    }
    $winNum = explode(',', $num);
    var_dump($winNum);
    foreach ($array as $m => $n) {
        $arr = explode(',', $n);
        if ($arr == array_intersect($arr, $winNum)) {
            $flag++; //�н���ʶ,�����н��������
        }
    }

    return $flag;
}

//直选是否中奖，任一、前二直选、前三直�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
function winZ($num, $array, $play)
{ //$num为开奖号码，$array为投注号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;,$play为玩�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
    $flag = 0; //是否中奖的标志位
    $p=0;
    switch ($play) {
        case 0:
            $p = 2;
            break;
        case 8:
            $p = 5;
            break;
        case 10:
            $p = 8;
            break;
    }
    $num = substr($num, 0, $p);
    $winNum = str_replace(",","",$num);
    $winNum=str_replace("，","",$winNum);
    foreach ($array as $m => $n) {
        $n=str_replace(",","",$n);
        $n=str_replace("，","",$n);
        if($winNum==$n){
            $flag++;
        }
    }

    return $flag;
}

//计算奖金
function getBonus($lotteryID,$play, $time, $num)
{
    try{
        //$play：玩法，用来获取奖金;$time:投注倍数;$num:中奖号码的注数;
        $data = 'lotteryID='.$lotteryID."&virtual_flag=2&gameplay=".$play;
        //    $apiUrl=apiUrl;
        $apiUrl='http://api.xiangw.com.cn/';
//    error_log('--------------------'.$data,3, '/var/tmp/err_log.log');
        $response=do_post_request($apiUrl."backend/config/list_play", $data);
        if($response['fail']!=0){
            return array("fail"=>1,"mess"=>"接口访问失败");
        }else{
            $reward=$response['data'][0]['reward'];
            if(empty($reward)){
                return array("fail"=>2,"mess"=>"未获取到奖金信息");
            }
        }
        $reward = $reward * $time * $num;
//    error_log('------======------'.$reward,3, '/var/tmp/err_log.log');
        return $reward;
    }catch (Exception $e){
        error_log('--------------------'.$e,3, '/var/tmp/err_log.log');
    }

}

//修改中奖状态；如果是追号追中还需要撤�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
function operateState($bonus, $flag, $id, $chaseID, $money, $num, $issue)
{ //$bonus为中奖金额；$flag为订单类型：自购、追号、合�&#65533;;$id为订单编�&#65533;
    //更改中奖状�&#65533;
    if ($bonus > 0) { //中奖
        operateMoney($bonus, $flag, $id, 3, $chaseID, $num);
//
    } else { //未中�&#65533;
        operateMoney($bonus, $flag, $id, 4, $chaseID, $num);
    }

}

//修改中奖状态和中奖金额
function operateMoney($bonus, $flag, $id, $f, $chaseID, $num)
{ //$bonus为中奖金额；$flag为订单类型：自购、追号、合�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;;$id为订单编�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;;$num为开奖号�&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
//更改中奖金额
    switch ($flag) {
        case 1: //自购
            updateParam('lotteryuser', 'winMoney', $bonus, 'state', $f, 'ID', $id);
            updateParams('lotteryuserentity', 'winMoney', $bonus, 'state', $f, 'runNumbers', $num, 'lotteryUserID', $id);
            break;
        case 2: //追号
            updateParam('lotterychase', 'winMoney', $bonus, 'state', $f, 'ID', $id);
            updateParams('lotterychaseentity', 'winMoney', $bonus, 'state', $f, 'runNumbers', $num, 'lotteryUserID', $id);
            //
            if ($bonus > 0) {
                //
                $sql_update_win = "update chaseuser set winMoney=winMoney+" . $bonus . " where ID='" . $chaseID . "'";
                execsql($sql_update_win, 2);
                //
                $sql = "select isWinners,issues from chaseuser where ID='" . $chaseID . "'";
                $cancel = execsql($sql, 1);
                if ($cancel[0]['isWinners'] == 1) { //中奖后停止追号的标识
                    //
                    $array = json_decode($cancel[0]['issues'], true);
                    foreach ($array as $i => $n) {
                        if ($n[2] == 13) {
                            $array[$i][2] = 5;
                        }
                    }
                    $issues = json_encode($array);
                    $sql_is = "update chaseuser set state=6,issues='" . $issues . "' where ID='" . $chaseID . "'";
                    execsql($sql_is, 2);
                }
            }
            break;
        default:
            break;
    }
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

//更改订单状�&#65533;-chaseuser�&#65533;&#65533;
function updateOrder($issues, $issue, $money, $id)
{
    //issues中当前期状�&#65533;;13->0
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