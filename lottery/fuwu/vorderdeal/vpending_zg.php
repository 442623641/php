<?php
	while (true) {
		deal_zg();
		sleep(120);
	}
	
	function deal_zg(){
		$time1 = strtotime("now");
		$conn = new mysqli('localhost','apiadmin','123465');
		$conn->select_db('vlottery');
		
		$pay_url = 'http://api.xiangw.com.cn/money/order/pay';
		$reward_url = 'http://api.xiangw.com.cn/money/appfee/reward';
		$refund_url = 'http://api.xiangw.com.cn/money/order/decreasecost';
		
		$sql = "  SELECT * FROM lotteryuser a WHERE a.state IN(3,4,12) and a.payState!=3 ";
		$res = $conn->query($sql);
		$lotterys = array('0'=>"陕西11选5",'1'=>"排列三",'2'=>"排列五",'3'=>"七星彩",
						  '4'=>"大乐透",'5'=>"安徽11选5",'6'=>"浙江11选5");
		while($row=$res->fetch_assoc()){
			$app = 'vcp-'.$row['lotteryID'];
			if($row['state']==3){    //已中�&#65533;
				if($row['payState']==0){   //未处理（未支付未派奖状态）
					$data = 'order_id='.$row['ID'].'&app='.$app;
					$response1 = do_post_request($pay_url,$data);   //支付接口
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					$winMoney = $row['winMoney'];
					$data = 'app='.$app.'&comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney.'&credit='.$winMoney.'&money=0';
					$response2 = do_post_request($reward_url,$data);  //派奖接口
					$state = 0;
					if($response1['fail']==0){
						if($response2['fail']==0){
							$state = 3;  //已处�&#65533;
						}else{
							$state = 1;  //已支付未派奖
						}
					}else if($response2['fail']==0){
						$state = 2;   //已派奖未支付
					}
					updateOrderState($conn,$state,$row['ID']); //更新订单状�&#65533;
				}
				if($row['payState']==1){    //已支付未派奖
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					$winMoney = $row['winMoney'];
					$data = 'app='.$app.'&comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney.'&credit='.$winMoney.'&money=0';
					$response = do_post_request($reward_url,$data);  //派奖接口
					if($response['fail']==0){
						updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
					}
				}
				if($row['payState']==2){    //已派奖未支付
					$data = 'order_id='.$row['ID'].'&app='.$app;
					$response = do_post_request($pay_url,$data);   //支付接口
					if($response['fail']==0){
						updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
					}
				}

			}
	
			if($row['state']==4){  //未中�&#65533;
				$data = 'order_id='.$row['ID'].'&app='.$app;
				$response = do_post_request($pay_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
				}
			}
			
			if($row['state']==12){  //购彩失败退�&#65533;
				$money = $row['money'];
				$data = 'order_id='.$row['ID'].'&app='.$app.'&amount='.$money;
				$response = do_post_request($refund_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
				}
			}
		}
		$time2 = strtotime("now");
		$time = $time2-$time1;
		echo $time;
	}
	
	function updateOrderState($conn,$state,$id){
		$sql = "UPDATE lotteryuser a SET a.payState='$state' where a.ID='$id'";
		$conn->query($sql);
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
	    return json_decode($response,true);
	}
?>