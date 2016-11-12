<?php
class PayControl {
	protected $userID=0;
	protected $order_id;
	var $payConfig;
	//const LOGFLODER='logDetail';
	public function __construct($payConfig,$_order_id) {
		//require_once ("payConfig.php");
		require_once("lib/alipay_core.function.php");
		$this->order_id=$_order_id;
		$this->userID=$this->getUserIdFromOrderID($_order_id);
		$this->payConfig = $payConfig;
		$this->log("","****************开始******执行日期:".strftime("%Y%m%d%H%M%S",time()));
	}
	public function aliPay($params){
		//echo 111;
		if(!$this->recharge($params['order_id'], $params['amount'], $params['comment'])){
			//logResult('xxxxxxx用户充值fun[lotteryPay/recharge]xxxxx\n:['+json_encode($params)+']结束xxxxx\n',LOGFLODER);
			$this->log('支付款打入余额失败fun[lotteryPay/recharge]',json_encode($params));
			return false;
		}
		if(!$this->updateOrder($params['order_id'])){
			//logResult('**********更新订单失败fun[lotteryPay/updateOrder]*******\n:['+json_encode($params)+']结束********\n',LOGFLODER);
			$this->log('更新订单失败,支付款已打入余额fun[lotteryPay/updateOrder]',json_encode($params));
		}
		if(!$this->pending($params['order_id'],$params['amount'],$params['comment'])){
			//logResult('YYYYYYYYY用户冻结失败fun[lotteryPay/pending]YYYYYYYYY\n:['+json_encode($params)+']结束YYYYYYYYY\n',LOGFLODER);
			$this->log('平台冻结用户支付款失败fun[lotteryPay/pending]',json_encode($params));
			return false;
		}
		return true;
	}
	public function xkPay($params){
		//冻结
		$res=$this->pending($params['order_id'],$params['amount'],$params['comment']);
		if(!$res){
			$ret['fail']=1;
			$ret['mess']='支付失败,请重新支付';
			echo $this->decodeUnicode(json_encode($ret));
			exit;
		}
		if($res['fail']!=0){
			echo $this->decodeUnicode(json_encode($res));
			exit;
		}
		//更新
		$res=$this->updateOrder($params['order_id']);
		if(!$res){
			//logResult('**********更新订单失败fun[lotteryPay/updateOrder]*******\n:['+json_encode($params)+']结束********\n',LOGFLODER);
			//$this->log('更新订单失败,支付款已打入余额fun[lotteryPay/updateOrder]',json_encode($params));
			$ret['fail']=1;
			$ret['mess']='支付失败,请重新支付';
			echo $this->decodeUnicode(json_encode($ret));
			exit;
		}
		if($res['fail']!=0){
			echo $this->decodeUnicode(json_encode($res));
			exit;
		}
		return true;
	}
	/**
	  * 参数说明
	  * string   $class	  请求模块  $class=user,$class=admin/user
	  * string   $function	  请求方法
	  * string/array	 $parameter	    请求参数，两种传值模式
	  *	  普通模式：
	  *	  'username = test&pass =123456’
	  *	  数组模式：
	  *	  array(username =test, pass =123456)
	  */
	private  function request($url,$class, $function,$parameter) {
		$retArr = array('fail' => 0);
		//参数处理
		//$url = $this->domain;
		//$url=substr(trim($url),-1)=='/'?substr($url,0,strlen($url)-1):$url;
		$url=$url.'/'.$class.'/'.$function;
		//var_dump($parameter);
		//echo $url;exit;
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			$file_contents = curl_exec($ch);
			curl_close($ch);
			return json_decode($file_contents,true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			$retArr['url'] =$url.'/'.$class.'/'.$function;
			$retArr['params']=$parameter;
		}
		return $retArr;
	}
	public function recharge($order_id,$amount,$comment)
	{
		$parameter=array();
		$url=$this->escapeUrl($this->payConfig['api_url']);
		$parameter['user']=$this->userID;
		$parameter['amount']=$amount;
		$parameter['order_id']=$order_id;
		$parameter['comment']=$comment;
		$response=$this->request($url,'money', 'user/deposit', $parameter);
		//var_dump($response);
		if(!$response){
			$parameter['apiUrl']=$url.'/money/user/deposit';
			//$parameter['comment']='彩票支付状态更新';
			$parameter['apiResponse']=$response;
			$this->log('用户充值失败[fun/recharge]',json_encode($parameter));
			return false;
		}
		if($response['fail']!=0){
			$parameter['apiUrl']=$url.'/money/order/pay';
			$parameter['apiResponse']=$response;
			$this->log('用户充值失败[fun/recharge]',json_encode($parameter));
			return false;
		}
		$this->log('用户充值成功,apiReponse:',json_encode($response));
		return 	true;
	}
	private function updateOrder($order_id)
	{
		$parameter=array();
		$url=$this->escapeUrl($this->payConfig['lottery_url']);
		$parameter['userID']=$this->userID;
		$parameter['id']=$order_id;
		$response=$this->request($url.'/index.php','api/OrderPay', 'update', $parameter);
		//var_dump($response);
		//exit; 
		if(!$response){
			$parameter['apiUrl']=$url.'/index.php/api/OrderPay/update';
			$parameter['comment']='彩票支付状态更新';
			$parameter['apiResponse']=$response;
			$this->log('彩票更新[fun/updateOrder]',json_encode($parameter));
			return false;
		}
		if($response['fail']!=0){
			$parameter['comment']='彩票支付状态更新';
			$parameter['apiUrl']=$url.'/index.php/api/OrderPay/update';
			$parameter['apiResponse']=$response;
			$this->log('彩票更新[fun/updateOrder]',json_encode($response));
			return false;
		}
		$this->log('彩票支付状态更新成功,apiReponse:',json_encode($response));
		return $response;
	}	
	/**
	 * 正式支付,平台冻结资金
	 * @param  [type] user,用户名(手机号），
	 * @param  [type] pending_id=, 流水号(由 /money/pending 产生)
	 * @param  [type]  shop=,店铺
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	protected  function pending($order_id,$amount,$comment){
		//$params['amount']=$amount;
		//$params['money']=$amount;
		$params['user']=$this->userID;
		$params['app']='cp-'.substr(trim($order_id),-2,1);	
		//$params['shop']='';//@$_REQUEST['shop']; 	
		$params['orderID']=$order_id;
		$params['comment']=$comment;
		//$params['directpay']=1;
		$url=$this->escapeUrl($this->payConfig['api_url']);
		$response=$this->request($url,'money', 'pending/confirm', $params);
		//$pending_id=@$response['pending_id'];		
		//$response=$this->request($url,'money/order', 'pay', $params);
		if(!$response){
			$parameter['apiUrl']=$url.'money/pending/confirm';
			//$parameter['comment']='彩票支付状态更新';
			$this->log('平台冻结[fun/pending]',json_encode($response));
			return false;
		}
		if($response['fail']!=0){
			$parameter['apiUrl']=$url.'money/pending/confirm';
			$parameter['apiResponse']=$response;
			$this->log('平台冻结[fun/pending]',json_encode($response));
			return false;
		}
		$this->log('平台冻结支付款成功,apiReponse:',json_encode($response));
		return $response;//@$response['pending_id'];
	}
	/**
	* 冻结资金,出票后正式付款
 	* @param  [type] user=用户名(手机号），
  	* @param  [type] shop=, 店铺
  	* @param  [type] app=，  app子功能号
   	* @param  [type] amount=,金额
   	* @param  [type] comment=, 备注
   	* @param  [type] orderID=, 相关的订单ID
   	* @param  [type] callback=,成功后的回调(可选)
	* @return [json] fail:0-成功; other-error_code; mess:提示信息
	*/
	public function generate($chaseID,&$pending_id,$comment,$lotteryID){	
		//$lotteryConfig=@config_item('lottery');
		$ret=array('fail'=>0);
		$params['amount']=abs($_REQUEST['money'])*100;
		$params['credit']=abs(@floatval(($_REQUEST['credit'])));
		$params['money']=abs(@floatval($_REQUEST['cash']))*100;
		$params['user']=$this->userID;
		if(!empty($_REQUEST['sms_code'])||!empty($_REQUEST['pending_id'])){
			Extension::params_valid(array('sms_code'=>'验证码','pending_id'=>'支付编号'));
			$params['sms_code']=$_REQUEST['sms_code'];	
			$params['pending_id']=$_REQUEST['pending_id']; 
			$response=$this->request('money', 'pending/confirmSMS', $params);	
			$pending_id=$_REQUEST['pending_id'];		
		}else{
			$lotteryID=$lotteryID==null?$_REQUEST['lotteryID']:$lotteryID;
			$lottery=$this->getLotteryInfo($lotteryID);
			$params['app']='cp-'.$lotteryID;	
			$params['shop']=@$_REQUEST['shop']; 	
			$params['orderID']=$chaseID;
			$params['comment']=$lottery['name']."-".$comment;
			$response=$this->request('money', 'pending/generate', $params);
			$pending_id=@$response['pending_id'];
		}	
		//var_dump($params);
		if(empty($response)){
			$ret['fail']=1;
			$ret['mess']='网络延时,稍后再试';
			echo decodeUnicode(json_encode($ret));
			exit;
		}
		//验证码支付
		if($response['fail']==1){
			$response['fail']=4000;
			echo decodeUnicode(json_encode($response));
			exit;
		}
		//异常
		if($response['fail']!=0){
			echo decodeUnicode(json_encode($response));
			exit;
		}	
		return true;
		
	}
	/**
	 * 写日志，方便查错
	 * 注意：服务器需要开通fopen配置
	 * @param $suject 要写入日志里的主题 
	 * @param $text 要写入日志里的文本内容 默认值：空值;
	 * @param $path 要写入日志里的文本路径 默认值：log\detail;
	 */
	public function log($suject='',$text='',$path='../log/detail') {
		$path=empty($path)?$path:$path.'/';
		$fp = fopen($path.date("Ymd").".log","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"[".$suject."]:\n{".$this->decodeUnicode($text)."}\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	public function decodeUnicode($str)
	{
	    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
	        create_function(
	            '$matches',
	            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
	        ),
	        $str);
	}	
	//生产流水订单号
	public function make_seek($userID)
	{
		return date("Ymdhis").str_pad($userID,6,"0",STR_PAD_LEFT).'99';//.$this->userID;
	}
	//生产流水订单号
	private function getUserIdFromOrderID($orderID)
	{
		return $this->userID==0?(int)substr($orderID,14,6):$this->userID; //(int)date("Ymdhis").str_pad($userID,6,"0",STR_PAD_LEFT).'99';//.$this->userID;
	}
	//获取渠道号
	public function get_channelType($channelType=null)
	{
		$res=channelType=='web'? '08':07;
		return $res;
	}
	private function escapeUrl($url){
		return substr(trim($url),-1)=='/'?substr($url,0,strlen($url)-1):$url;
	}
}
?>
