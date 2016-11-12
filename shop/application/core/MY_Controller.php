<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

#--------------------------------------------------------------
# 公共类
# -------------------------------------------------------------
# zhangshuai created
# yulei <464001224@qq.com> updated
# -------------------------------------------------------------
# 2015-6-30
#--------------------------------------------------------------
 
/**
 * descrip: 手机端和PC端判断
 * @author: yulei<13685590366@126.com>
 * date: 2015-5-7
 */

class Common_Controller extends CI_Controller {

	//请求超时时间
	protected $timeout;

	//接口地址
	protected $domain;

	public function __construct() {
		parent::__construct();
		$this->domain ='http://api.cxiang.net';
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
		if ($type == 'HTTPS') {
			$type = CURLPROTO_HTTPS;
		}

		$retArr = array('fail' => 0);
		$url = $this->domain . '/' .$class . '/' . $function;
		$timeout = empty($timeout) ? $this->timeout : $timeout;
		
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
//			echo $url;
//			exit();
			curl_setopt($ch, CURLOPT_PROTOCOLS, $type);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			$responses = curl_exec($ch);
			curl_close($ch);
			$responses=explode("\n\n\n",$responses);
			$retArr= json_decode(count($responses)>1?$responses[1]:$responses,true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			//$retArr = json_encode($retArr);
		}
		return $retArr;
	}

	/**
	 * 设置session 数组批设置,或逐个设置
	 * @param [type] $sessionName [description]
	 * @param [type] $val         [description]
	 */
	public function set_session($sessionName,$val){	
		if (is_array($sessionName))
			$this->session->set_userdata($sessionName);
		else {
			if(is_array($val)){
				$val = json_encode($val);
			}

			$this->session->set_userdata($sessionName,$val);
		}
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

		$this->isLogin = @$this->islogin || $this->get_session('dealer_islogin');
		//检测用户是否登录
		if (!$this->isLogin) {
			//跳转
			redirect('Privilege/login');
			return ;
		} else {

			//user-手机号码;username-姓名;userid-用户id
			$this->dealer = $this->get_session('dealer');
			$this->dealerID = $this->get_session('dealerID');
			$this->dealer_code = $this->get_session('dealer_code');
		}
	}
	/**
	 * 获取session
	 * @param  [type] $sessionName [description]
	 * @return [type]              [description]
	 */
	public function get_session($sessionName)
	{
		return $this->session->userdata($sessionName);

	}
}


/**
 * @author:yulei <464001224@qq.com>
 * 个人中心
 * 2015-6-29
 */
class User_Controller extends Home_Controller
{
	/**
	 * POST请求接口
	 * @param  [type] $class     接口类名
	 * @param  [type] $function  接口方法名
	 * @param  [type] $parameter 传递post参数
	 * @param  [type] $type      请求类型HTTP, HTTPS
	 * @return [type]            请求结果
	 */
	public function request($class, $function, $parameter, $type=CURLPROTO_HTTP) {
		if ($type == 'HTTPS') {
			$type = CURLPROTO_HTTPS;
		}

		$url = $this->domain . '/' .$class . '/' . $function;
		$timeout = empty($timeout) ? $this->timeout : $timeout;

		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_PROTOCOLS, $type);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			$result = curl_exec($ch);
			curl_close($ch);
			$retArr = json_decode($result,true);
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
		}
		return $retArr;
	}
}

/**
 * 调用外部接口常用操作类
 */
class API_Controller extends CI_Controller{
	
	protected $timeout;
	protected $domain;

	public function __construct(){
		parent::__construct();
		$this->timeout = 1;
		$this->domain ='http://api.cxiang.net';

		//验证是否有权限调用接口
		//$this->_sign();
	}

	protected function _sign(){
		if ($_GET) {
			$params = $this->input->get();
		} else if($_POST) {
			$params = $this->input->post();
		}

		$valid = $this->_encrypt($params);
		if ($valid !== true) {
			$response = array(
				'fail' => 1025,
				'mess' => '验证不能通过，非有效用户',
				);	
			echo json_encode($response);
			return ;
		}
		
	} 

	private function _encrypt($params){
		if ($params['access_token'] == "") {
			$isLogin = $this->db->simple_query("select * from user_valid where user='".$params['user']."'");
			if ($isLogin === true) {
				$response = array(
					'fail' => 1025,
					'mess' => '验证不能通过，非有效用户',
				);	
				echo json_encode($response);
				return false;		
			}
		} else {

			//获取数据库中用户timestamp
			$query = $this->db->query('select timestamp from user_valid where userid="'.$params['userid'].'" and access_token="'.$params['access_token'] .'"');
			$timestamp = $query->db->result_array();
			if ($timestamp['timestamp'] == $params['timestamp']) {
				$response = array(
					'fail' => 1025,
					'mess' => '验证不能通过，非有效用户',
				);	
				echo json_encode($response);
				return false;	
			} else {
				$signature = $params['signature'];
				unset($params['signature']);

				if ($signature == md5($params)) {
					return true;
				}
			}
			
		}
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
		if ($type == 'HTTPS') {
			$type = CURLPROTO_HTTPS;
		}

		$retArr = array('fail' => 0);
		$url = $this->domain . '/' .$class . '/' . $function;
		$timeout = empty($timeout) ? $this->timeout : $timeout;
		
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_PROTOCOLS, $type);
			curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			$responses = curl_exec($ch);
			curl_close($ch);
			$retArr=explode("\n\n\n",$responses);

			if(count($retArr)>1) 
				return $retArr[1];
			else 
				return $retArr;
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			$retArr = json_encode($retArr);
		}
		return $retArr;
	}
}




