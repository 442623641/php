<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户控制器
class Privilege extends Common_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('form');
	}

	//首页
	public function index(){
		$this->load->view('login.html');
	}

	public function login()
	{	
		if($_POST)
		{
			signin();
		}
		$this->load->view('login.html');
	}

	/**
	 * 登录处理
	 * @param  [type] $href [description]
	 * @return [type]       [description]
	 */
	public function signin()
	{
		#设置验证规则
		$params['user'] = $this->input->post('user');
		$params['pass'] = $this->input->post('passwd');
		$response = $this->request('dealer', 'auth/login', $params);
		if ($response['fail'] ===false) {	
			error( '网络超时，请稍后再试！');
			return false;
		}
		if ($response['fail'] != 0){
			error($response['mess']);
		}
		else{
			$userInfo = array(
				'dealerID' => $response['dealerID'],
				'dealer' => $params['user'],
				'dealer_code' => $response['code'],
				'dealer_validTime' => $response['valid_time'],
				'dealer_loginTime' =>date('y-m-d h:i:s',time()),						
				'dealer_islogin' => true
			);	
			$this->session->set_userdata($userInfo);
//			var_dump($this->session->userdata('dealer_loginTime'));
//			var_dump($response);
//			exit();
		}
		redirect('user/index');
	}

	/**
	 * 验证用户登录 call_back_func
	 * @return 
	 */
	public function check_login(){
	
		
		$params['user'] = $this->input->post('user');
		$params['password'] = $this->input->post('passwd');
		$params['type'] = "web";

		$response = $this->request('Api', 'login', $params);

		if ($response['fail'] == 3015) {
			$this->form_validation->set_message('check_login', '网络超时，请稍后再试！');
			return false;
		}	

		if ($response['fail'] === 0) {
			//登录成功
			$userInfo = array(
				'userID' => $response['user'],
				'user' => $params['user'],
				'code' => $response['code'],
				'validTime' => $response['valid_time'],				
				'islogin' => true,
			);
			setcookie('avatar', $response['user'],time()+86400*30);
			$this->set_session($userInfo);
			return true;	
		}

		$this->form_validation->set_message('check_login', $response['mess']);
		return false;
	}

	#显示注册页面
	public function register(){
		if($_POST)
		{
			doregister();
		}
		$this->load->view('register.html');
	}
	#显示忘记密码页面
	public function forgotPassword(){
		$this->load->view('forgotPassword.html');
	}
	public function doChangePassword()
	{
		$response=array('fail'=>0);
		$user=$this->session->userdata('user');	
		if (empty($user))
		{
			$response['fail']=1;
			$response['mess']='你还没登录';
			$response['href']='user/login';
		}
		$userName=$user['user'];
		$oldpassword = $_REQUEST['oldPassword'];
		$newpassword = $_REQUEST['newPassword'];
		$reNewpassword = $_REQUEST['reNewPassword'];
		if(empty($oldpassword))
		{
			$response['fail']=1;
			$response['mess']='旧密码不能为空';
			$response['href']='user/changePassword';
		}
		if(empty($newpassword))
		{
			$response['fail']=1;
			$response['mess']='新密码不能为空';
			$response['href']='user/changePassword';
		}
		if(empty($reNewpassword))
		{
			$response['fail']=1;
			$response['mess']='确认密码不能为空';
			$response['href']='user/changePassword';
		}
		if ($newpassword!=$reNewpassword){
			$response['fail']=1;
			$response['mess']='两次密码输入不一样';
			$response['href']='user/changePassword';
		}
		if (strlen($newpassword)<6)	{
			$response['fail']=1;
			$response['mess']='密码不足六位';
			$response['href']='user/changePassword';
		}
		if ($newpassword==$oldpassword)	{
			$response['fail']=1;
			$response['mess']='新密码不能与旧密码一样';
			$response['href']='user/changePassword';
		}
		$parameter=array();
		array_push($parameter, 'user='.$userName);
		array_push($parameter, 'pass='.$oldpassword);
		array_push($parameter, 'newpass='.$newpassword);
		$response=$this->request('user', 'changepass', $parameter);
		if($response['fail'] != 0) {
			$response['href']='user/changePassword';
		}
		$this->echoAndExit($response);
	}
}
?>
