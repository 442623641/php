<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pay extends Home_Controller {

	public function __construct() {
		parent::__construct();
		//$api_url=config_item("api_url");
		//$lottery_url=config_item("lottery_url");
	}
	//支付宝支付
	public function aliPay(){
	}
	//便命支付，余额支付
	public function balancePay(){
		$ret=array("fail"=>0);
		if($_REQUEST['apiUrl']){
			$ret['fial']=1;
			$ret['mess']="无此app应用";
		}
		//$url=@config_item($app."_url");
		//		if(empty($url)){
		//			$ret['fial']=1;
		//			$ret['mess']="无此app应用";
		//		}
		$response=$this->requestWithurl($url, $parameter);
	}
	#输出
	private function printa($msg,$code=1) {
		$ret['fail']=1;
		if($code==1){
			$ret['mess']=$msg;
		}else{
			$ret['data']=$msg;
		}
		 //json_encode($ret);
		echo $this->decodeUnicode(json_encode($ret)); 
		exit;
	}
	private function decodeUnicode($str)
	{
	    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
	        create_function(
	            '$matches',
	            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
	        ),
	        $str);
	}
}