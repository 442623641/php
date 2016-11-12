<?php
//
while (true) {
    $dt=date("H:i");

    if($dt>"08:58"&& $dt<"22:30"){
//陕西11�&#65533;5、排列三
        for($i=0;$i<2;$i++){
            comm(1,$i);
            comm(2,$i);
            comm(3,$i);
        }
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
//    $username = 'lottery';
//    $password = 'caixiang518';
    $username='lottery';
    $password='caixiang518';
    $db_name = 'xklottery';
    $dsn = "mysql:host=" . $server_address . ";dbname=" . $db_name;
    try {
        //
        $db = new PDO($dsn, $username, $password);
        //
        if ($flag == 1) { //表示查询
            $query_order = $db->query($sql);
echo "$sql\n";
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

//
function comm($play,$lotteryID){//$play表示玩法�&#65533;1为自购，2为追号，3为合�&#65533;;$lotteryID表示彩种编号
    //获取上一期的开奖时间，用以判断购彩是否失败
    $lastIssue=getLastIssue($lotteryID);
    //
    $query='';
    $comment='';
    $arr1=array();//需要修改的字段
    $arr2=array();//条件
    if($play==1){//自购
        $query=getFailOrder('lotteryuser','ID,userID,money',$lotteryID,$lastIssue);
        $comment='自购退�&#65533;';
        //更改状态等
        array_push($arr1,"state=12");
        array_push($arr2,"state in (0,11)");
        array_push($arr2,"issue<".$lastIssue);
        updateData("lotteryuser",$arr1,$arr2);
    }else if($play==2){//追号
        $comment='追号退�&#65533;';
        //查询表lotterychase里的订单
        $query=getFailOrder('lotterychase','ID,chaseID,userID,money',$lotteryID,$lastIssue);
        foreach($query as $v){
            array_push($arr1,"state=6");
            array_push($arr1,"realMoney=realMoney-".$v['money']);
            array_push($arr1,"chasePhases=chasePhases-1");
            array_push($arr2,"ID=".$v['chaseID']);
            //
            updateData("chaseuser",$arr1,$arr2);
            //释放数组
            unset($arr1);
            unset($arr2);
        }
    }else if($play==3){//合买
        $comment='合买退�&#65533;';
        //
        $query=getFailOrder('lotteryparticipants','ID,lotteryJointID,userID,money',$lotteryID,$lastIssue);
        foreach($query as $v){
            array_push($arr1,"state=9");
            array_push($arr2,"ID".$v["lotteryJointID"]);
            updateData("lotteryparticipants",$arr1,$arr2);
            updateData("lotteryjoint",$arr1,$arr2);
            //释放数组
            unset($arr1);
            unset($arr2);
        }
    }
    //调用平台接口
    foreach($query as $v){
        $response=do_post_request("http://api.xiangw.net/money/order/pay","app=cai-piao&order_id=".$v['ID']."&cost=0&comment".$comment);
        while(!$response){
            $response=do_post_request("http://api.xiangw.net/money/order/pay","app=cai-piao&order_id=".$v['ID']."&cost=0&comment".$comment);
        }
    }

}

//获取当前期号，用以判断购彩失败的订单
function getLastIssue($lotteryID){
    $sql="select issue from lotteryissue where lotteryID=".$lotteryID;
    $lastIssue=exec_sql($sql,1);

    return $lastIssue[0]['issue'];
}

//
function getFailOrder($table,$sel,$lotteryID,$issue){
    $sql="select ".$sel." from ".$table." where lotteryID=".$lotteryID." and state in(0,8,11) and issue<".$issue;
    $query=exec_sql($sql,1);

    return $query;
}

//修改数据库表
function updateData($table,$arr1,$arr2){
    $set1=implode(',',$arr1);
    $set2=' and '.implode(' and ', $arr2);
    $sql="update ".$table." set ".$set1." where 1=1 ".$set2;

    exec_sql($sql,2);
}

//退�&#65533;
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