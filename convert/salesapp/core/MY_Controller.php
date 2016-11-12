<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

#--------------------------------------------------------------
# 公共类
# -------------------------------------------------------------
# yulei <464001224@qq.com> 
# -------------------------------------------------------------
# 2015-7-14
#--------------------------------------------------------------
 
/**
 * descrip: 公共控制器
 * @author: yulei<13685590366@126.com>
 * date: 2015-7-14
 */

class Common_Controller extends CI_Controller {

	//请求超时时间
	protected $timeout;

	//接口地址
	protected $domain;

	public function __construct() {
		parent::__construct();
		$this->domain = "http://api.xiangw.net";
		$this->load->switch_themes_on();

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
			return $responses;
		} catch (Exception $e) {
			$retArr['fail'] = 3015;
			$retArr['mess'] = $e->getMessage();
			$retArr = json_encode($retArr);
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
 * 权限中心控制器
 * yulei 2015-7-14
 */
class Home_Controller extends Common_Controller{

	/**
	 * 访问权限控制
	 */
	public function __construct() {
		parent::__construct();

		$this->isLogin = $this->get_session('isLogin');

		//检测用户是否登录
		if (!$this->isLogin) {

			//跳转
			redirect('User/login');
			return ;
		} else {

			//user-手机号码
			$this->user = $this->get_session('user');
			$this->dealerid = $this->get_session('dealerid');
			$this->code = $this->get_session('code');
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
