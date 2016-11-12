<?php
	while (true) {
    $dt = date("H:i");
    if ($dt > "08:00" && $dt < "22:30") {
        getShanxi11x5Kjhm();
        sleep(30);
    } else {
        break;
    }
}

	function getShanxi11x5Kjhm(){
	    $url = 'http://api.yssh365.com//Lotterys.ashx?type=sx115';
	    $ch = curl_init(); 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10); 
		$data = curl_exec($ch); 
	    $array = json_decode($data,true);

		if(!$array){
			return false;
		}
		$cur_issue = '';
		$cur_num = '';
		$cur_dt = '';
		$order_num = '';

		$conn = new mysqli('localhost','caipiaokong','caixiang518');
		$conn->select_db('caipiaokong');
		//$conn2 = new mysqli('localhost','lottery','caixiang518');
		//$conn2->select_db('xklottery');
        //
		$sql = "SELECT MAX(LotteryIssue) sum FROM sxsyxw";
		$res = $conn->query($sql);
		$maxissue = 0;
		while($row=$res->fetch_assoc()){
			$maxissue = (int)$row['sum'];
		}
        //
        $lotteryID = 0;
        $insertnum = 0;
        $temp = substr(date('i'),0,1).'0';  //当前时间（分钟数取整�&#65533;
        foreach ($array as $res) {
            $cur_issue = (int)$res['LotteryIssue'];
            $cur_num = $res['LotteryValue'];
            $cur_dt = $res['LotteryDate'];
            $order_num = $res['LotteryOrderValue'];

            if($cur_issue>$maxissue){  //抓取的期号大于数据库中最大期�&#65533;
                $sql = "insert into sxsyxw(LotteryIssue,LotteryValue,LotteryDate,LotteryDateStr,LotteryOrderValue,CreateDate) values('$cur_issue','$cur_num','$cur_dt','$cur_dt','$order_num',now())";
                $conn->query($sql);
                $sql = "UPDATE currentlottery SET issue='$cur_issue',num='$cur_num',dt='$cur_dt',order_num='$order_num' WHERE lotteryID='$lotteryID'";
                $conn->query($sql);
//                $start_position=strpos($cur_dt,':');
//                $hour = substr($cur_dt,$start_position+1,2);   //抓取号码的开奖时�&#65533;
//                if($hour>$temp){    //抓取的期号大于当前整点时间（即为最新的开奖号码）
//                    //echo '获取到最新数�&#65533;';
//                    $insertnum++;
//                }
            }
        }
//        if($insertnum==0){
//            //echo '暂无最新数据！';
//            return false;
//        }
        $conn->close();
        //$conn2->close();
        return true;
	}
	

 
?>