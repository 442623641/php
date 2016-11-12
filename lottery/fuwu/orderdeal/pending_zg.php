<?php
	while (true) {
		deal_zg();
		sleep(300);
	}
	
	function deal_zg(){
		$conn = new mysqli('localhost','lottery','caixiang518');
		$conn->select_db('xklottery');
		
		$pay_url = 'http://api.xiangw.com.cn/money/order/pay';
		$reward_url = 'http://api.xiangw.com.cn/money/appfee/reward';
		$refund_url = 'http://api.xiangw.com.cn/money/order/decreasecost';
		
		$sql = "  SELECT * FROM lotteryuser a WHERE a.state IN(3,4,12) and a.order_state!=3";
		$res = $conn->query($sql);
		$lotterys = array('0'=>"陕西11�&#65533;5",'1'=>"排列�&#65533;",'2'=>"排列�&#65533;",'3'=>"七星�&#65533;",
						  '4'=>"大乐�&#65533;",'5'=>"安徽11�&#65533;5",'6'=>"浙江11�&#65533;5");
		while($row=$res->fetch_assoc()){
			$app = 'cp-'.$row['lotteryID'];
			if($row['state']==3){    //已中�&#65533;&#65533;
				if($row['order_state']==0){   //未处理（未支付未派奖状态）
					$data = 'order_id='.$row['ID'].'&app='.$app;
					$response1 = do_post_request($pay_url,$data);   //支付接口
					echo $response1;
					$comment = $lotterys[$row['lotteryID']].'-�&#65533;'.$row['issue'].'期中�&#65533;';
					$winMoney = $row['winMoney']*100;
					$data = 'app='.$app.'&comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney.'&order_id='.$row['ID'];
					$response2 = do_post_request($reward_url,$data);  //派奖接口
					echo $response2;
					$state = 0;
					if($response1['fail']==0){
						if($response2['fail']==0){
							$state = 3;  //已处�&#65533;&#65533;
						}else{
							$state = 1;  //已支付未派奖
						}
					}else if($response2['fail']==0){
						$state = 2;   //已派奖未支付
					}
					updateOrderState($conn,$state,$row['ID']); //更新订单状�&#65533;
				}
				if($row['order_state']==1){    //已支付未派奖
					$comment = $lotterys[$row['lotteryID']].'-�&#65533;'.$row['issue'].'期中�&#65533;';
					$winMoney = $row['winMoney']*100;
					$data = 'app='.$app.'&comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney;
					$response = do_post_request($reward_url,$data);  //派奖接口
					if($response['fail']==0){
						updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
					}
				}
				if($row['order_state']==2){    //已派奖未支付
					$data = 'order_id='.$row['ID'].'&app='.$app;
					$response = do_post_request($pay_url,$data);   //支付接口
					if($response['fail']==0){
						updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
					}
				}

			}
	
			if($row['state']==4){  //未中�&#65533;&#65533;
				$data = 'order_id='.$row['ID'].'&app='.$app;
				$response = do_post_request($pay_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
				}
			}
			
			if($row['state']==12){  //购彩失败退�&#65533;&#65533;
				$money = $row['money']*100;
				$data = 'order_id='.$row['ID'].'&app='.$app.'&amount='.$money;
				$response = do_post_request($refund_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'3',$row['ID']); //更新订单状�&#65533;
				}
			}
		}
	}
	
	function updateOrderState($conn,$state,$id){
		$sql = "UPDATE lotteryuser a SET a.order_state='$state' where a.ID='$id'";
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