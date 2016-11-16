<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * descrip: 手机端和PC端判�&#65533;
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
		$this->domain = "http://xk.xiangw.net/xk.php";
		$this->load->switch_themes_on();
		//判断手机或PC
		// $this->load->library('user_agent');
		// $isMobile = $this->agent->is_mobile();

		// if (!$isMobile) {
		// 	$url = rtrim(base_url(), '/') . '/xk.php';
		// 	redirect($url);
		// }
	}
	/**	 * POST请求接口	 * @param  [type] $class     接口类名	 * @param  [type] $function  接口方法名	 * @param  [type] $parameter 传递post参数	 * @param  [type] $type      请求类型HTTP, HTTPS	 * @return [type]            请求结果	 */	public function request($class, $function, $parameter, $type=CURLPROTO_HTTP) {		if ($type == 'HTTPS') {			$type = CURLPROTO_HTTPS;		}		$retArr = array('fail' => 0);		$url = $this->domain . '/' .$class . '/' . $function;				try {			$ch = curl_init();			curl_setopt($ch, CURLOPT_URL, $url);			curl_setopt($ch, CURLOPT_PROTOCOLS, $type);			curl_setopt($ch, CURLOPT_POST, 1);	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);			$responses = curl_exec($ch);			curl_close($ch);			$retArr=explode("\n\n\n",$responses);			if(count($retArr)>1) 				return $retArr[1];			else 				return $retArr[0];		} catch (Exception $e) {			$retArr['fail'] = 3015;			$retArr['mess'] = $e->getMessage();			$retArr = json_encode($retArr);		}		return $retArr;	}
	/**
	 * 设置session 数组批设�&#65533;,或逐个设置
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
 * Mobile展示模块控制�&#65533;
 * zhangshuai
 */
class Home_Controller extends Common_Controller{
	/**
	 * 访问权限控制
	 */
	public function __construct() {
		parent::__construct();

		$params['islogin'] = $this->get_session('islogin');
		$params['user'] = $this->get_session('user');

		//检测用户是否登�&#65533;
		if (!$params['islogin']) {

			//跳转
			redirect('user/login');
			return ;
		} else {

			//user-手机号码;username-姓名;userid-用户id
			$this->user = $this->get_session('user');
			$this->userid = $this->get_session('userid');
			$this->code = $this->get_session('code');
		}
	}

	/**
	 * 设置session 数组批设�&#65533;,或逐个设置
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
	 * @param  [type] $function  接口方法�&#65533;
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
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
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



#后台父控制器
class Admin_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->switch_themes_off();

		#权限验证
		if (!$this->session->userdata('cx_admin')) {
			redirect('admin/privilege/login');
		}
	}
}