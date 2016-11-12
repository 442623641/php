<?php
	require_once '../payControl.php';
	require_once '../payConfig.php';
	$payControl=new payControl($pay_config,$order_id);
	//商户订单号
	if(@$_REQUEST['out_trade_no']){
		$ret['fail']=1;
		$ret['mess']='订单号不能为空';
		echo $payControl->decodeUnicode(json_encode($ret));
		exit;
	}
	if(@$_REQUEST['body']){
		$ret['fail']=1;
		$ret['mess']='订单描述不能为空';
		echo $payControl->decodeUnicode(json_encode($ret));
		exit;
	}
	if(@$_REQUEST['total_fee']){
		$ret['fail']=1;
		$ret['mess']='订单金额不能为空';
		echo $payControl->decodeUnicode(json_encode($ret));
		exit;
	}
	//描述
	$param=array();
	$param['order_id']=$_REQUEST['out_trade_no'];
	$param['comment']=$_REQUEST['body'];
	$param['amount']=$_REQUEST['total_fee'];
	//$param['credit']=@$_REQUEST['cash'];
	//$pending_id = @$_REQUEST['pending_id'];
	if (strstr($comment, '支付')){
		if($payControl->xkPay($param)){
			$ret['fail']=0;
			$ret['mess']='支付成功';
			echo $payControl->decodeUnicode(json_encode($ret));
			exit;
		}
	}elseif(strstr($comment, '积分'))
	
	
?>