<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户控制器
class User extends Common_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('form');
	}
	public function test(){
		echo 111;
	}
	//首页
	public function index(){	
		$this->load->helper('getClientIp');
		$ip = get_client_ip(0, true);
		$ip2long = ip2long($ip);
		if ($ip2long>=ip2long('192.168.0.0') &&$ip2long<=ip2long('192.168.255.255')) {
			$data['city'] = '合肥';
		} else {
			$baseUrl = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $baseUrl);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($ch);
			$response = json_decode($response, true);
			curl_close($ch);
			$data['city'] = $response['data']['city']; 
		}

		$this->load->view('index.html', $data);
	}

	public function login()
	{
		if ($_POST) {
			$params['user'] =  $this->input->post('user');
			$params['password'] =  $this->input->post('passwd');
			$params['type'] = 'web';
			$response=$this->request('Api', 'login', $params);
			$temp = $response['Data'][0];
			if($response['fail']==0){
							//登录成功
				$userInfo = array(
					'userid' => $response['user'],
					'user' => $params['user'],
					'code' => $response['code'],
					'avatar' => $temp['avatar'],
					'islogin' => true,
				);

				@$this->set_session($userInfo);
				$data['redirect']= site_url('user/index');
				$data['fail'] = 0;
				echo json_encode($data);
			}else{
				$data['fail'] = $response['fail'];
				$data['mess'] = $response['mess'];
				echo json_encode($data);
			}	
			exit;
		}
//		if ($_POST) {
//			$result = $this->doLogin();
//			echo json_encode($result);
//			exit;
//		} 	
		$this->load->view('login.html');
	}

	protected function doLogin()
	{
		#设置验证规则
		$this->form_validation->set_rules('user','手机号或邮箱','trim|required');
		$this->form_validation->set_rules('passwd','密码','trim|required|callback_check_login');

		if ($this->form_validation->run() === false) {
			$result = array(
				'fail' => 1001,
				'msg' => validation_errors(),
				'redirect' => site_url('user/login'),
				);
		 } else {
		 	$result = array(
		 		'fail' => 0,
		 		'msg' => '恭喜你！登录成功',
		 		'redirect' => site_url('user/index'),
		 		);
		}

		return $result;
	}

	public function check_login(){
		$params['user'] = $this->input->post('user');
		$params['password'] = $this->input->post('passwd');
		$params['type'] = "mobile";

		$response = $this->request('Api', 'login', $params);

		if ($response['fail'] == 3015) {
			$this->form_validation->set_message('check_login', '网络超时，请稍后再试！');
			return false;
		}	

		if ($response['fail'] != 0) {
			$this->form_validation->set_message('check_login', $response['mess']);
			return false;
		}else{
			//登录成功
			$userInfo = array(
				'userid' => $response['user'],
				'user' => $params['user'],
				'code' => $response['code'],
				'validTime' => $response['valid_time'],
				'islogin' => true,
			);

			@$this->set_session($userInfo);
		}

		return true;	
	}

	#显示注册页面
	public function register(){
		if (@$_GET['shop'] || @$_GET['referee']) {
			$data['shop'] = $this->input->get('shop');
			$data['referee'] = $this->input->get('referee');
		}

		if (!$_POST) {
			$result = $this->doRegister();
			echo json_encode($result);
			exit();
		}

		@$this->load->view('register.html', $data);
	}

	protected function doRegister(){
		#设置验证规则
		$this->form_validation->set_rules('user','手机号或邮箱','required');
		$this->form_validation->set_rules('passwd','密码','required|matches[passwdCf]');
		$this->form_validation->set_rules('passwdCf','确认密码','required');

		$result = array();
		if ($this->form_validation->run() == false) {
			$result = array(
				'fail' => 1001,
				'msg' => validation_errors(),
				);
		} else {
			$data = $this->input->post();
			$params['user'] = $data['user'];
			$params['password'] = $data['passwd'];
			$params['code'] = $data['telCode'];
			$response = $this->request('Api','register', $params);
			if ($response['fail'] == 0) {
				$param['user'] = $data['user'];
				@$param['shop'] = $data['shop'];
				$param['app'] = 'deafult';
				@$param['referee'] = $data['referee'];
				$result = $this->request('Api', 'appUser', $param); 
			}
			if ($result['fail'] == 0) {
				$res = array(
						'fail' => 0,
						'msg' => '恭喜你！注册成功',
						'redirect' =>base_url().'/download.html'//'http://www.xiangw.com.cn/download.html',
					);
			} else {
				$res = array(
						'fail' => 1004,
						'msg' => '未知错误',
						'redirect' => site_url('User/register'),
					);
			}
			return $res;
		}
	}


	public function code_check($code)
	{
		if ($code != $this->session->userdata('code')) {
			$this->form_validation->set_message('code_check','%s错误!');
			return false;
		} else {
			return true;
		}
	}


	public function forget(){
		if ($_POST) {
			$response = $this->doForget();
			echo $response;
			exit;
		}
		$this->load->view('forget.html');
	}

	public function doForget(){
		$data = $this->input->post();
		$url = self::BASE_API . 'forgotPassword?user='.$data["user"].'&password='.$data["passwd"].'&repassword='.$data["passwdCf"].'&code='.$data["telCode"];

		$request = curl_init();
		curl_setopt($request, CURLOPT_URL, $url);
		curl_setopt($request, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($request);
		curl_close($request);
		if ($response === false) {
			 //echo 'Curl error: ' . curl_error($request);
			exit('未知错误，请稍后重试！');

		}

		$response = mb_convert_encoding ( $response , 'ASCII');
		$response = json_decode($response, true);

		if (!isset($response['fail'])) {
			$response = array(
				'fail' => 1004,
				'msg' => '请求错误'
				);
		} else if ($response['fail'] == 0){
			$response = array(
				'fail' => 0,
				'msg' => '修改成功',
				'redirect' => site_url('user/login'),
				);
		}
		

		return json_encode($response);

	}

	public function forgetCode(){
		$user = $this->input->post('user');

		//忘记密码的验证码
		$url = self::BASE_API . 'code/reset?user='.$user;

		$request = curl_init();
		curl_setopt($request, CURLOPT_URL, $url);
		curl_setopt($request, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($request);
		if ($response === false) {
			 //echo 'Curl error: ' . curl_error($request);
			exit('未知错误，请稍后重试！');

		}
		curl_close($request);
		$response = mb_convert_encoding ( $response , 'ASCII');
		$response = json_decode($response, true);
		if (!isset($response['fail'])) {
			$response = array(
				'fail' => 1004,
				'msg' => '请求错误'
				);
		} else if ($response['fail'] == 0){
			$response = array(
				'fail' => 0,
				'msg' => '获取成功',
				);
		}


		echo json_encode($response);


	}

	#处理登录
	public function signin(){
		#设置验证规则
		$this->form_validation->set_rules('username','用户名','required');
		$this->form_validation->set_rules('password','密码','required');

		#获取表单数据
		$captcha = strtolower($this->input->post('captcha'));

		#获取session中保存的验证码
		$code = strtolower($this->session->userdata('cx_code'));

		if ($captcha === $code){
			#验证码正确，则需要验证用户名和密码
			if ($this->form_validation->run() == false){
				return array('fail'=>1,'mess'=>validation_errors()); 
			} else{
				$userName = $this->input->post('username',true);
				$password = $this->input->post('password',true);
				$parameter=array();
				array_push($parameter, 'user='.$username);
				array_push($parameter, 'pass='.$password);
				$response=$this->request('user', 'login', $parameter);
				// if ($username == 'admin' && $password == '123'){
				#用户验证通过，跳转到首页
				if ((int)$response['fail']==0)
				{
					# OK，保存session信息,然后跳转到首页
					$this->session->set_userdata('cx_username',$username);
					redirect('home/index');
				}else{
					return $response;
				}
			}

		} else {
			#验证码不正确，给出提示页面，然后返回
			return array('fail'=>1,'mess'=>'验证码错误，请重新填写');
		}
	}

	#注销动作
	public function logout(){
		$params['signature'] =  '';
		$params['user'] = $this->session->userdata('user');
		$response=$this->request('Api', 'logout', $params);
//		if($response['fail']==0){
//			redirect('user/login');
//		}
		$this->session->unset_userdata('user');
		$this->session->sess_destroy();
		redirect('user/login');	
	}
	

	#注册获取验证码
	public function code(){
		$params['user'] = $_REQUEST['user'];
		//var_dump($params);
		$response=$this->request('Api', 'validUser', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	#忘记密码获取验证码
	public function codeSend(){
		$params['phone'] =  $this->input->post('phone');
		$params['purpose'] =  'resetpassword';
		$response=$this->request('Api', 'codeSend', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	public function forgetPassModify(){  
		$params['user'] =  $this->input ->post('user');
		$params['sms_code'] =  $this->input->post('telCode');
		$params['newpass'] = $this->input->post('passwd');
		$response=$this->request('Api', 'forgotPassword', $params);
		$data['fail'] = $response['fail'];
		$data['mess'] = $response['mess'];
		$data['redirect'] = site_url('user/login');
		$data = json_encode($data);
		echo $data;
		
		//user, sms_code, newpass, lastcode
	}
}