<?php
	while (true) {
		//deal_hm_launcher();  //合买发起者处理
		deal_hm_participant();  //合买参与者处理
		sleep(250);
	}
	
	function deal_hm_launcher(){
		$conn = new mysqli('localhost','lottery','caixiang518');
		$conn->select_db('xklottery');
		
		$pay_url = 'http://api.xiangw.com.cn/money/order/pay';
		$reward_url = 'http://api.xiangw.com.cn/money/appfee/reward';
		$refund_url = 'http://api.xiangw.com.cn/money/order/decreasecost';
		
		$sql = " SELECT a.*,(a.launcherBuy+b.money) AS smoney FROM lotteryjoint a INNER JOIN lotteryparticipants b ON b.lotteryJointID=a.ID WHERE a.state IN(3,4,9) AND a.order_state!=3 ";
		$res = $conn->query($sql);
		$lotterys = array('0'=>"陕西十一选五",'1'=>"排列三",'2'=>"排列五",'3'=>"七星彩",
						  '4'=>"大乐透",'5'=>"安徽十一选五",'6'=>"浙江十一选五");
		while($row=$res->fetch_assoc()){
			if($row['state']==3){    //已中奖
				if($row['order_state']==0){  //订单未处理
					$data = 'order_id='.$row['ID'].'&app=cai-piao';
					$response1 = do_post_request($pay_url,$data);   //支付接口
					 //合买发起人派奖
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					//$launcherMoney = $row['launcherBuy']*$row['winMoney']/$row['money']*100;
					$launcherMoney = $row['smoney']*100;
					$data = 'app=cai-piao&comment='.$comment.'&user='.$row['userID'].'&amount='.$launcherMoney;
					$response2 = do_post_request($reward_url,$data); 
					$state = 3;
					if($response1['fail']==0){
						if($response2['fail']==0){
							$state = 3;  //已处理
						}else{
							$state = 1;  //已支付未派奖
						}
					}else if($response2['fail']==0){
						$state = 2;   //已派奖未支付
					}
					updateOrderState($conn,'lotteryjoint',$state,$row['ID']);  //更新订单状态
				}
				if($row['order_state']==1){  //已支付未派奖
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					$launcherMoney = $row['launcherBuy']*100;
					$data = 'app=cai-piao&comment='.$comment.'&user='.$row['userID'].'&amount='.$launcherMoney;
					$response = do_post_request($reward_url,$data); 
					if($response['fail']==0){
						updateOrderState($conn,'lotteryjoint','3',$row['ID']);  //更新订单状态
					}
				}
				if($row['order_state']==2){  //已派奖未支付
					$data = 'order_id='.$row['ID'].'&app=cai-piao';
					$response = do_post_request($pay_url,$data);   //支付接口
					if($response['fail']==0){
						updateOrderState($conn,'lotteryjoint','3',$row['ID']);  //更新订单状态
					}
				}
			}
			
			if($row['state']==4){    //未中奖
				$data = 'order_id='.$row['ID'].'&app=cai-piao';
				$response = do_post_request($pay_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'lotteryjoint','3',$row['ID']);  //更新订单状态
				}
			}

			if($row['state']==9){  //合买失败
				$launcherMoney = $row['launcherBuy']+$row['ensure'];
				//$comment = '合买失败发起者退款';
				$data = 'order_id='.$row['ID'].'&app=cai-piao&amount='.$launcherMoney;
				$response = do_post_request($refund_url,$data);   //退款接口
				if($response['fail']==0){
					updateOrderState($conn,'lotteryjoint','3',$row['ID']);  //更新订单状态
				}
			}
		}
	}
	
	function deal_hm_participant(){
		$conn = new mysqli('localhost','lottery','caixiang518');
		$conn->select_db('xklottery');
		
		$pay_url = 'http://api.xiangw.com.cn/money/order/pay';
		$reward_url = 'http://api.xiangw.com.cn/money/appfee/reward?app=cai-piao';
		
		$sql = " SELECT b.order_state,b.state,b.ID,b.userID,b.winMoney,b.money,a.issue,a.lotteryID FROM lotteryjoint a INNER JOIN lotteryparticipants b ON b.lotteryJointID=a.ID where b.state in(3,4,9) AND b.order_state!=3 ";
		$res = $conn->query($sql);
		$lotterys = array('0'=>"陕西11选5",'1'=>"排列三",'2'=>"排列五",'3'=>"七星彩",
						  '4'=>"大乐透",'5'=>"安徽11选5",'6'=>"浙江11选5");
		while($row=$res->fetch_assoc()){
			if($row['state']==3){    //已中奖
				if($row['order_state']==0){  //未处理
					$data = 'order_id='.$row['ID'].'&app=cai-piao';
					$response1 = do_post_request($pay_url,$data);   //支付接口
					//参与者派奖
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					$winMoney = $row['winMoney']*100;
					$data = 'comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney;
					$response2 = do_post_request($reward_url,$data); 
					$state = 3;
					if($response1['fail']==0){
						if($response2['fail']==0){
							$state = 3;  //已中奖已完成
						}else{
							$state = 1;  //已支付未派奖
						}
					}else if($response2['fail']==0){
						$state = 2;   //已派奖未支付
					}
					updateOrderState($conn,'lotteryparticipants',$state,$row['ID']);  //更新订单状态
				}
				if($row['order_state']==1){   //已支付未派奖
					$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
					$winMoney = $row['winMoney']*100;
					$data = 'comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney;
					$response = do_post_request($reward_url,$data); 
					if($response['fail']==0){
						updateOrderState($conn,'lotteryparticipants','3',$row['ID']);  //更新订单状态
					}
				}
				if($row['order_state']==2){   //已派奖未支付
					$data = 'order_id='.$row['ID'].'&app=cai-piao';
					$response = do_post_request($pay_url,$data);   //支付接口
					if($response['fail']==0){
						updateOrderState($conn,'lotteryparticipants','3',$row['ID']);  //更新订单状态
					}
				}
			}
			
			if($row['state']==4){    //未中奖
				$data = 'order_id='.$row['ID'].'&app=cai-piao';
				$response = do_post_request($pay_url,$data);   //支付接口
				if($response['fail']==0){
					updateOrderState($conn,'lotteryparticipants','3',$row['ID']);  //更新订单状态
				}
			}
			  
			if($row['state']==9){  //合买失败
				$money = $row['money']*100+ $row['ensure']*100;	
				//$comment = '合买失败参与者退款';
				$data = 'order_id='.$row['ID'].'&app=cai-piao&amount='.$money;
				$response = do_post_request($refund_url,$data);   //退款接口
				if($response['fail']==0){
					updateOrderState($conn,'lotteryparticipants','3',$row['ID']);  //更新订单状态
				}
			}
		}		
	}
	
	function updateOrderState($conn,$tablename,$state,$id){
		$sql = "UPDATE $tablename a SET a.order_state='$state' where a.ID='$id'";
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