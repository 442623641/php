<?php

	getRunNum();
	function getRunNum(){
		//echo "进入函数".'<br>';
	    //$data = file_get_contents("http://api.yssh365.com//Lotterys.ashx?type=sx115");
	    $url = 'http://api.yssh365.com//LotteryDay.ashx?type=pls&type=plw&type=qxc&type=dlt';
	    $ch = curl_init(); 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10); 
		$data = curl_exec($ch); 
	    $array = json_decode($data,true);

		if(!$array){
			echo "获取数据失败";
			getRunNum();
		}
		$cur_issue = '';
		$cur_num = '';
		$cur_dt = '';

		$conn = new mysqli('localhost','caipiaokong','caixiang518');
		$conn->select_db('caipiaokong');
		//$conn2 = new mysqli('localhost','lottery','caixiang518');
		//$conn2->select_db('xklottery');
		
		$m = date('m')<10?substr(date('m'),1):date('m');
		$d = date('d')<10?substr(date('d'),1):date('d');
		$now = date('Y').$m.$d; 
		$tables = array('pls'=>"plsan",'plw'=>"plwu",'qxc'=>"qx",'dlt'=>"dlt");  //表名配置
		$lotteryids = array('pls'=>'1','plw'=>'2','qxc'=>'3','dlt'=>'4');		 //彩种编号配置
		$insertnum = 0;
		foreach ($array['rows'] as $res) {
			$cur_issue = (int)$res['LotteryIssue'];
			$cur_num = $res['LotteryValue'];
			$cur_dt = $res['LotteryDate'];
			$type = $res['LotteryType'];
			
			$sql = "SELECT MAX(LotteryIssue) sum FROM $tables[$type]";
			$res = $conn->query($sql);
			$maxissue = 0;
			while($row=$res->fetch_assoc()){
				$maxissue = isset($row['sum'])?$row['sum']:0;
			}
			if($cur_issue>$maxissue){
				$sql = "insert into $tables[$type] (LotteryIssue,LotteryValue,LotteryDate,CreateDate) values('$cur_issue','$cur_num','$cur_dt',now())";
				$conn->query($sql);
echo $sql;
					$sql = "UPDATE currentlottery SET issue='$cur_issue',num='$cur_num',dt='$cur_dt' WHERE lotteryID='$lotteryids[$type]'";
					$conn->query($sql);
echo $sql;
				$hour = substr($cur_dt,0,9);
				$hour = str_replace('/','',$hour);
				if($hour==$now){

					$insertnum++;
				}

			}
		}
		//echo "插入最新数据条数：".$insertnum;
		$conn->close();
		//$conn2->close();
		$dt = date("H:i");
    if (($dt > "20:00" && $dt < "23:59")||($dt > "00:00" && $dt < "01:00")) {
        //echo 'δץȡ��ȫ���������ݣ�';
        sleep(600);			getRunNum();
		}
	}
	

 
?>