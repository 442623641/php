<?php
	while (true) {
		deal_zh_pay();   //追号支付（根据追号主表）
		deal_zh_reward();  //追号派奖（根据追号明细表）
		sleep(120);
	}
	
	function deal_zh_pay(){
		try {
			$conn = new mysqli('localhost','apiadmin','123465');
			$conn->select_db('vlottery');
			$pay_url = 'http://api.xiangw.com.cn/money/order/pay';
			$sql = " SELECT a.ID,a.lotteryID,SUM(b.money) totalmoney FROM chaseuser a,lotterychase b WHERE a.ID=b.chaseID AND a.state=6 AND a.payState!=3 AND b.state IN (3,4) GROUP BY b.chaseID ";
			$res = $conn->query($sql);
			while($row=$res->fetch_assoc()){
					$comment = '追号完成后真实扣款';
					$realMoney = $row['totalmoney'];
					$app = 'vcp-'.$row['lotteryID'];
					$data = 'order_id='.$row['ID'].'&app='.$app.'&cost='.$realMoney.'&comment='.$comment;
					$response = do_post_request($pay_url,$data);   //支付接口
					if($response['fail']==0){
						$sql = "UPDATE chaseuser a SET a.payState='3' where a.ID='".$row['ID']."'";
						$conn->query($sql);					
					}
				}		
		}
		catch (Exception $e) {
			echo '支付错误：'.$e;
		}
	 }
	
	 function deal_zh_reward(){
		$conn = new mysqli('localhost','apiadmin','123465');
		$conn->select_db('vlottery');
		$reward_url = 'http://api.xiangw.com.cn/money/appfee/reward';
		
		$sql = "SELECT b.lotteryID,a.issue,a.winMoney,b.userID,a.ID,a.chaseID FROM lotterychase a,chaseuser b WHERE a.chaseID=b.ID AND  a.state=3 AND a.payState!=3";
		$res = $conn->query($sql);
		$lotterys = array('0'=>"陕西11选5",'1'=>"排列三",'2'=>"排列五",'3'=>"七星彩",
						  '4'=>"大乐透",'5'=>"安徽11选5",'6'=>"浙江11选5");
		while($row=$res->fetch_assoc()){
				$comment = $lotterys[$row['lotteryID']].'-第'.$row['issue'].'期中奖';
				$winMoney = $row['winMoney'];
				$app = 'vcp-'.$row['lotteryID'];
				$data = 'app='.$app.'&comment='.$comment.'&user='.$row['userID'].'&amount='.$winMoney.'&order_id='.$row['chaseID'].'&credit='.$winMoney.'&money=0';
				echo 'paijiangurl:'.$data.'<br>';
				$response = do_post_request($reward_url,$data);  //派奖接口
				if($response['fail']==0){
					$sql = "UPDATE lotterychase a SET a.payState='3' where a.ID='".$row['ID']."'";
					$conn->query($sql);					
				}

			}
			
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