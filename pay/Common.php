<?php
#前台父控制器
class Common {
	protected $timeout;
	protected $domain;
	public function __construct() {
		$this->timeout = 1;
		$this->domain = 'http://api18.xiangw.net/';//http://api.cxiang.net/';
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
	private  function request($class, $function, $parameter) {
		$retArr = array('fail' => 0);
		//参数处理
		$url = $this->domain;
		if (is_array($parameter)) {
			$parameter = implode('&', $parameter);
		}
		$url = $url . $class . '/' . $function . '?' . $parameter;
		$timeout = empty($timeout) ? $this->timeout : $timeout;
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			$file_contents = curl_exec($ch);	
			curl_close($ch);
			$retArr=explode("\n\n\n",$file_contents);
			if(count($retArr)>1)
			$retArr= json_decode($retArr[1],true);
			else {
			$retArr['fail'] = 1;
			$retArr['mess'] =$retArr;
		};
			//$retArr = json_decode($file_contents, true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
		}
		$retArr['url']=$url;
		return $retArr;
	}
	public function recharge($user,$amount,$order_id,$comment)
	{
		$parameter=array();
		array_push($parameter, 'user='.$user);
		array_push($parameter, 'amount='.$amount);
		array_push($parameter, 'order_id='.$order_id);
		array_push($parameter, 'comment='.$comment);
		$response=$this->request('money', 'user/deposit', $parameter);		return json_encode($response);
//		if ($response === false) {
//			return false;
//		}
//		if ($response['fail'] != 0)
//		{
//			return false;
//		}
//		return true;
	}
	//生产流水订单号
	public function make_seek($userID)
	{
		if(empty($userID))
		{
			exit();
		}
		return date("Ymdhism").$userID;
	}
	//获取渠道号
	public function get_channelType($channelType=null)
	{
		$res=channelType=='web'? '08':07;
		return $res;
	}
}
