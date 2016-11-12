<?php
class OrderPay extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotteryissue_model');
	}
	//订单验证
	public function verify(){
		//echo 11;
		//exit;
		$params=array('id'=>'订单号','payType'=>'支付方式','product'=>'产品类型');
		Extension::params_valid($params);
		$fun=$_REQUEST['product'];
		
		$order=$this->$fun($_REQUEST['id']);
		if(empty($order)){
			$this->retFailed('订单不存在或产品类型字典错误');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']))*100;
		$credit=abs(floatval(@$_REQUEST['credit']));
		$money=abs($order['money'])*100;
		if($cash+$credit!=$money){
			$this->retFailed('支付金额有误,订单金额应为'.$order['money'].'元');
		}
		$lotteryID=$order['lotteryID'];
		//var_dump($response);
		$issueData=$this->Lotteryissue_model->getIssue($lotteryID);
		if(empty($issueData)){
			$this->retFailed('无法获取正确期号');
		}
		$lotteryConfig=$this->getLotteryInfo($lotteryID);
		$interval=$lotteryConfig['payInterval'];
		if($order['issue']==$issueData['issue']){
			if((strtotime($issueData['stopTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				$res['fail']=6001;
				$res['mess']='订单失效,'.$lotteryConfig['name'].'-'.$order['issue'].'期已停止支付';
				$res['issue']=$issueData['nextIssue'];
				echo json_encode($res);
				exit;
				//$this->retFailed('订单失效,'.$lotteryConfig['name'].'-'.$order['issue'].'期已停止支付',6001);
			}
		}elseif($order['issue']==$issueData['nextIssue']){
		if((strtotime($issueData['nextTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				$this->retFailed('订单失效,'.$lotteryConfig['name'].'-'.$order['issue'].'期已停止支付');
			}
		}elseif($order['issue']<$issueData['issue']){
				//$this->retFailed('订单失效,'.$lotteryConfig['name'].'-'.$order['issue'].'期已停止支付');
				$res['fail']=6001;
				$res['mess']='订单失效,'.$lotteryConfig['name'].'-'.$order['issue'].'期已停止支付';
				$res['issue']=$issueData['issue'];
				echo json_encode($res);
				exit;
		}
		$this->session->set_userdata($order['ID'], array('payType'=>$_REQUEST['payType'],'id'=>$order['ID'],'cash'=>$cash,'credit'=>$credit,'time'=>date("Y-m-d H:i:s")));
		//确认支付
		$response=$this->prepay($order['ID'],$cash,$credit,$money,$lotteryID);
		$data['id']=$order['ID'];
		$data['money']=$order['money'];
		$data['secCode']=session_id();
		$data['credit']=$credit;
		$data['cash']=$cash;
		//$data['pending_id']=$response['pending_id'];
	 	$this->retAcess($data);
	}
	//支付完成，订单更新
	public function update(){
		$this->load->Model('Orderpay_model');
		$params=array('id'=>'订单号');
		Extension::params_valid($params);
		$data=$this->Orderpay_model->pay_pro_excute($_REQUEST['id']);
		//var_dump($data);
		if(!$data){
			$this->retFailed('更新失败');
		}
		if($data['outcount']<1) {
		 	$this->retFailed('更新失败'.json_encode($data));
		}
		$data['desc']='订单更新完成';
		$data['state']=$data['outcount'];
		$this->retAcess($data);
	}
	public  function lotteryuser($id){
		$this->load->Model('Lotteryuser1_model');
		$order=$this->Lotteryuser1_model->get_lotteryUser($id);
		if(empty($order)){
			$this->retFailed('订单不存在');
		}
		return $order;
	}
	public function lotterychase($id){
		$this->load->Model('Lotterychase1_model');
		$order=$this->Lotterychase1_model->get_chaseUser($id);
		if(empty($order)){
			$this->retFailed('订单不存在');
		}
		$first=$this->Lotterychase1_model->get_lotteryChase($id);
		if(empty($first)){
			$this->retFailed('追号第一期不存在');
		}
		$order['issue']=$first['issue'];
		return $order;
	}
	
	public function lottreyjoint($id){
		$order=$this->LotteryJoin1_model->get_lotteryUser($id);
		if(empty($order)){
			$this->retFailed('订单不存在');
		}
		return $order;
	}
	public function test(){
		$userID='10010';
		$payType=$_REQUEST['payType'];
		$addUrl=base_url('api/lotteryUser/add?');
		$verUrl=base_url('api/lotteryUser/verify?');
		$updUrl=base_url('api/lotteryUser/update?');
		$alipayUrl='http://pay.xiangw.com:82/pay/alipay/Notify_url.php?';
		$xkpayUrl='http://pay.xiangw.com:82/pay/xkpay/pay.php?';
		$issueData=$this->Lotteryissue_model->getIssue(0);
		$issue=$issueData['issue'];
		//下单
		$para='multiple=1&issue='.$issue.'&money=2&lotteryID=0&text=[[4,1,"01,08,10,02,09",1,2]]&userID='.$userID.'&';
		$readd=$this->requestOfurl($addUrl.$para, '', '');
		if(empty($readd)){
			echo $addUrl.$para;
			$this->retFailed($addUrl.$para);
		}
		if($readd['fail']!=0){
			$this->retFailed($readd['mess']);
		}	
		
		//验证
		$id=$readd['data']['ID'];
		$money=$readd['data']['money'];
		$para='userID='.$userID.'&id='.$id.'&payType='.$payType.'&cash=1&credit=100&';
		$rever=$this->requestOfurl($verUrl.$para, '', '');
		if(empty($rever)){
			$this->retFailed($verUrl.$para);
		}
		if($rever['fail']!=0){
			echo $verUrl.$para;
			$this->retFailed($rever['mess']);
		}
		//echo 111;
		//exit();
		//支付
		if($payType=='zfb'){
			$para='test=1&out_trade_no='.$id.'&trade_no=934789&trade_status=TRADE_SUCCESS&body=山西11选5-1511090120期-自购-支付宝-支付&total_fee=1&';
			echo  '<a href="'.$alipayUrl.$para.'">'.$alipayUrl.$para.'</a></br><a href="http://www.xklottery.com:82/index.php/api/OrderPay/test?userID=10001">http://www.xklottery.com:82/index.php/api/OrderPay/test?userID='.$userID.'</a>';
			exit;
		}elseif($payType=='xkzf'){
			$para='test=1&out_trade_no='.$id.'&trade_no=934789&trade_status=TRADE_SUCCESS&body=山西11选5-1511090120期-自购-余额-支付&total_fee=1&';
			//echo  '<a href="'.$notifyUrl.$para.'">'.$notifyUrl.$para.'</a></br><a href="http://www.xklottery.com:82/index.php/api/OrderPay/test?userID=10001">http://www.xklottery.com:82/index.php/api/OrderPay/test?userID='.$userID.'</a>';
			//exit;
			$repay=$this->requestOfurl($xkpayUrl.$para, '', '');
			///echo $xkpayUrl.$para;
			echo json_encode($repay);
			exit;
			//if(empty($rever)){
			//	$this->retFailed($alipayUrl.$para);
			//}
			//if($rever['fail']!=0){
			//	$this->retFailed($readd['mess']);
			//}
		}
	}
}
