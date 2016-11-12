<?php
//	$flag=0;
//	for ($i = 0; $i < 30; $i++) 
//	{ 
//		$flag++;
//		if(!getShanxi11x5Kjhm()){
//			sleep(5);
//			continue;
//		}else{
//			break;
//		}
//	}
//	echo $flag;
	getShanxi11x5Kjhm();
	function getShanxi11x5Kjhm(){
		$name = 'sxsyxw';
		$uid = '105010';
		$token = 'ed8e27b2be3cfbe88c5a58ab03e61a03d1a337d5';
		$num = 1;
	    //���ò���
	    $data = file_get_contents("http://api.caipiaokong.com/lottery/?name=".$name."&format=json&uid=".$uid."&token=".$token."&num=".$num."");
	    //$data����
	    $array = json_decode($data,true);
		if(!empty(array_filter($array))||count($array)==0){
			echo "获取数据失败";
			return false;
		}
		$cur_issue = '';
		$cur_num = '';
		$cur_dt = '';
		$flag = 0;
		foreach ($array as $key => $value) {
			if($flag==0){
				$cur_issue = $key;
				$cur_num = $array[$key]['number'];
				$cur_dt = $array[$key]['dateline'];
			}else{
				break;
			}
			$flag++;
		}
		$nums = explode(',',$cur_num);
		sort($nums); 
		$order_num = '';
		for ($i= 0;$i< count($nums); $i++){
			$order_num .= ",".$nums[$i];
		}
		$order_num = substr($order_num,1);
		$conn = new mysqli('192.168.10.21','root','root');
		$conn->select_db('lotteryhistory');
		
		if($cur_issue==''||$cur_issue=='status'){
				echo "获取数据失败";
				return false;
		}
		$sql = "SELECT COUNT(*) sum FROM shanxi11x5 WHERE issue='$cur_issue'";
		$res = $conn->query($sql);
		while($row=$res->fetch_assoc()){
			$count = $row['sum'];
			if($count>0){
				echo "当前开奖信息已存在";
				return false;
			}else{
				$sql = "insert into shanxi11x5(issue,num,dt,num_order) values('$cur_issue','$cur_num','$cur_dt','$order_num')";
				$conn->query($sql);
			}
		}
		$conn->close();
		$conn2 = new mysqli('192.168.10.21','root','root');
		$conn2->select_db('lottery');
		$sql = "SELECT id FROM lottery WHERE name='陕西11x5'";
		$res2 = $conn2->query($sql);
		$lotteryID = '';
		while($row=$res2->fetch_assoc()){
			$lotteryID = $row2['id'];
		}
		$lotteryID = 1;
		$sql = "SELECT COUNT(*) sum FROM currentlottery WHERE lotteryID='$lotteryID'";
		$res2 = $conn2->query($sql);
		while($row=$res2->fetch_assoc()){
			$count = $row['sum'];
			if($count>0){
				$sql = "UPDATE currentlottery SET issue='$cur_issue',num='$cur_num',dt='$cur_dt',order_num='$order_num' WHERE lotteryID='$lotteryID'";
				$conn2->query($sql);
			}else{
				$sql = "insert into currentlottery(lotteryID,issue,num,dt,order_num) values('$lotteryID','$cur_issue','$cur_num','$cur_dt','$order_num')";
				$conn2->query($sql);
			}
		}

		$conn2->close();
		return true;
	}
	

 
?>
