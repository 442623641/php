<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

#--------------------------------------------------------------
# 公共类
# -------------------------------------------------------------
# zhangshuai created
# -------------------------------------------------------------
# 2015-7-21
#--------------------------------------------------------------

class Common_Controller extends CI_Controller {
	protected $lotteryInfo;
	public function __construct() {
		parent::__construct();
		$this->load->library('Extension');
		$this->load->switch_web_on();
		
	}
	/**
	 * POST请求接口
	 * @param  [type] $class     接口类名
	 * @param  [type] $function  接口方法名
	 * @param  [type] $parameter 传递post参数
	 * @param  [type] $type      请求类型HTTP, HTTPS
	 * @return [type]            请求结果
	 */
	public function request($class, $function, $parameter, $type=CURLPROTO_HTTP) {
		$retArr = array('fail' => 0);
		$url =config_item("api_url") . '/' .$class . '/' . $function;	
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			//echo $url;
			//var_dump($parameter);
			//exit();
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$responses = curl_exec($ch);
			curl_close($ch);
			$responses=explode("\n\n\n",$responses);
			$retArr= json_decode(count($responses)>1?$responses[1]:$responses[0],true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			//$retArr = json_encode($retArr);
		}
		return $retArr;
	}
	
	/**
	 * POST请求接口
	 * @param  [type] $class     接口类名
	 * @param  [type] $function  接口方法名
	 * @param  [type] $parameter 传递post参数
	 * @param  [type] $type      请求类型HTTP, HTTPS
	 * @return [type]            请求结果
	 */
	public function requestOfurl($url,$function,$parameter, $type=CURLPROTO_HTTP){
		$retArr = array('fail' => 0);
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url.'/'.$function);
			//echo $url;
			//var_dump($parameter);
			//exit();
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$responses = curl_exec($ch);
			curl_close($ch);
			$retArr=json_decode($responses,true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			//$retArr = json_encode($retArr);
		}
		return $retArr;
	}
	/**
	 * 返回错误
	 */
	protected  function retFailed($msg,$code=1){
		header("Content-type: text/html; charset=utf-8");
		Extension::export($msg,$code);
	}
	/**
	 * 返回成功
	 */
	protected  function retAcess($msg,$code=0){
		header("Content-type: text/html; charset=utf-8");
		$ret['fail']=$code;
		$ret['data']=$msg;
		echo Extension::decodeUnicode(json_encode($ret));
		exit;
		
	}
}


/**
 * web展示模块控制器
 * zhangshuai created
 * yulei updated 2015-7-1
 */
class Home_Controller extends Common_Controller{
	/**
	 * 访问权限控制
	 */
	public function __construct() {
		parent::__construct();
		if(empty($_REQUEST['userID'])){
			$this->retFailed('用户编号不能为空');
		}
		$this->userID = $_REQUEST['userID'];
		$this->user = '测试';
	}	
	/**
	 * 取消订单，退款给用户
	 * @param  [type] user,用户名(手机号），
	 * @param  [type] pending_id=, 流水号(由 /money/pending 产生)
	 * @param  [type]  shop=,店铺
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	protected  function pay($money,$order_id,$lotteryID,$issues){
		$lottery=$this->getLotteryInfo($lotteryID);
		$params['cost']=$money*100;
		$params['app']='cp-'.$lotteryID;
		$params['order_id']=$order_id;
		$params['comment']=$lottery['name'].'完成交易-'.$issues.'期';
		$response=$this->request('money', 'order/pay', $params);
		if(empty($response)){
			Extension::export('网络延时,稍后再试');
		}
		//异常
		if($response['fail']!=0){
			echo json_encode($response);
			exit;
		}	
		return true;
	}

	/**
	 * 取消订单，退款给用户
	 * @param  [type] user,用户名(手机号），
	 * @param  [type] pending_id=, 流水号(由 /money/pending 产生)
	 * @param  [type]  shop=,店铺
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	//http://api.xiangw.net/money/order/decreasecost?app=cai-piao&order_id=1441951485&amount=20000
	protected  function unpending($money,$order_id,$lotteryID,$issues){
		//$lotteryConfig=@config_item('lottery');
		$lottery=$this->getLotteryInfo($lotteryID);
		$params['amount']=$money*100;
		$params['app']='cp-'.$lotteryID;
		$params['order_id']=$order_id;
		$params['comment']=$lottery['name'].'退款-'.$issues.'期追号';
		$response=$this->request('money', 'order/decreasecost', $params);
		if(empty($response)){
			Extension::export('网络延时,稍后再试');
		}
		//异常
		if($response['fail']!=0){
			echo Extension::decodeUnicode(json_encode($response));
		}	
		return $response;
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
	
	protected function pending($chaseID,&$pending_id,$comment,$lotteryID){	
		//$lotteryConfig=@config_item('lottery');
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
			Extension::export('网络延时,稍后再试');
		}
		//验证码支付
		if($response['fail']==1){
			$response['fail']=4000;
			echo json_encode($response);
			exit;
		}
		//异常
		if($response['fail']!=0){
			echo Extension::decodeUnicode(json_encode($response));
			exit;
		}	
		return true;
	}
	/**
	* 生成未支付订单
	* app=，  app子功能号
	* amount=,金额
	* comment=, 备注
	* orderID=, 相关的订单ID
	* callback=,成功后的回调(可选)
	* shop=,  店铺ID（可选）
	 */
	//http://api.xiangw.net/money/order/decreasecost?app=cai-piao&order_id=1441951485&amount=20000
	protected function prepare($order_id,$issue='',$detail=''){
		//$lotteryConfig=@config_item('lottery');
		$issue=empty($issue)?$_REQUEST['issue']:$issue;
		$lotteryID=$_REQUEST['lotteryID'];
		$lottery=$this->getLotteryInfo($lotteryID);
		$params['amount']=abs($_REQUEST['money'])*100;
		//$lotteryID=$lotteryID==null?$_REQUEST['lotteryID']:$lotteryID;
		//$lottery=$this->getLotteryInfo($lotteryID);
		$params['app']='cp-'.$lotteryID;
		$params['user']=$this->userID;	
		$params['shop']=@$_REQUEST['shop']; 	
		$params['orderID']=$order_id;
		$params['comment']=$lottery['name'].'-'.$issue.'期-'.$detail;
		$response=$this->request('money', 'pending/prepare', $params);
		//$pending_id=@$response['pending_id'];
		//var_dump($params);
		if(empty($response)){
			//Extension::export('网络延时,稍后再试');
			$this->retFailed('网络延时,稍后再试');
		}
		//异常
		if($response['fail']!=0){
			$this->retFailed($response['mess']);
		}	
		//var_dump($response);
		return $response;
	}
	/**
	* 订单确认
	*app=，  app子功能号
	*amount=,金额
	*orderID=, 相关的订单ID
	*pending_id=,
	*money=, 平台额（可选）
	*credit=, 积分额（可选）
	*/
	//http://api.xiangw.net/money/order/decreasecost?app=cai-piao&order_id=1441951485&amount=20000
	protected function prepay($order_id,$cash,$credit,$money,$lotteryID){
		//$lotteryConfig=@config_item('lottery');
		//金额
		//$cash=abs(floatval(@$_REQUEST['cash']));
		//$credit=abs(floatval(@$_REQUEST['credit']/100));
		if($cash+$credit!=$money){
			$this->retFailed('支付金额有误,订单金额应为'.($money/100).'元');
		}
		$params['app']='cp-'.$lotteryID;	
		$params['amount']=$money;
		$params['money']=$cash;	
		$params['orderID']=$order_id;	
		$params['credit']=$credit;
		$params['shop']='';
		//$params['comment']=$lottery['name']."-".$comment;
		$response=$this->request('money', 'pending/prepay', $params);
		$pending_id=@$response['pendingID'];
		//var_dump($params);
		if(empty($response)){
			//Extension::export('网络延时,稍后再试');
			$this->retFailed('网络延时,稍后再试');
		}
		//异常
		if($response['fail']!=0){
			$this->retFailed($response['mess']);
		}	
		//var_dump($response);
		return $response;
	}
	protected function getLotteryInfo($lotteryID){
		if(empty($this->lotteryInfo)){
			$lotterys=@config_item('lottery');
			$this->lotteryInfo=@$lotterys[$lotteryID];
		}
		if(empty($this->lotteryInfo)){
			Extension::export('彩种字典错误');
		}
		//$this->lotteryInfo=$lotterys[$_REQUEST["lotteryID"]];
		return $this->lotteryInfo;
	}
	protected function generalSeek($buyType){
		return date("Ymdhis").str_pad((int)$this->userID,6,0,STR_PAD_LEFT).$_REQUEST['lotteryID'].$buyType;//.$this->userID;
	}
	/**
	 * 店铺信息
	 */
	protected function shopInfo(){
		$this->load->Model('Lotteryissue_model');
		$lottery=$this->getLotteryInfo($_REQUEST["lotteryID"]);
		$condition["1"]="1";
		if($lottery['isProvince']){
			$condition["lotteryID"]=$_REQUEST["lotteryID"];
		}
		return $this->Lotteryissue_model->getShop($condition);
	}
}
