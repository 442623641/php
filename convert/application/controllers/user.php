<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户控制器
class User extends Common_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('form');
	}

	//首页
	public function index(){
		$this->load->view('index.html');
	}

	public function login()
	{	
		if($_POST)
		{
			signin();
		}
		$this->load->view('login.html');
	}

	public function changePassword()
	{	
		if($_POST)
		{
			doChangePassword();
		}
		$this->load->view('modify_pw.html');
	}

	public function person()
	{			
		$user=$this->session->userdata('user');
		if (empty($user))
		{
			redirect('user/login');
		}
		if($_POST)
		{
			$this->setUserInfo($user);
		}else{
			$this->userInfo($user);
		}
	}

	/**
	 * 招商加盟
	 * @return [type] [description]
	 */
	public function join(){	
		$this->load->view('join.html');
	}

	/**
	 * 关于我们
	 * @return [type] [description]
	 */
	public function us(){	
		$this->load->Model('admin/news_model');
		$data['news'] = $this->news_model->searchByType(1);
		$data['dyn'] = $this->news_model->searchByType(2);
		$this->load->view('about_us.html',$data);
	}


	
	/**
	 * 登录处理
	 * @param  [type] $href [description]
	 * @return [type]       [description]
	 */
	public function signin($href)
	{
		#设置验证规则
		$this->form_validation->set_rules('user','手机号','trim|required');
		$this->form_validation->set_rules('passwd','密码','trim|required|callback_check_login');
		if ($this->form_validation->run() === false) {
			if(empty($href)){
				$href='login';
			}
			$this->load->view($href.'.html');
		 } else {	
		 
		 redirect('user/index');
		}
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
		if (empty($response)) {
			$this->form_validation->set_message('check_login', '网络超时，请稍后再试！');
			return false;
		}
		if ($response['fail'] == 3015) {
			$this->form_validation->set_message('check_login', '网络超时，请稍后再试！');
			return false;
		}	

		if ($response['fail'] === 0) {
			$avatar = $response['Data'][0]['avatar'];
			if (!file_exists(base_url("jcrop/avatars/".$avatar))) {
				$avatar = 'default.png';
			}

			//登录成功
			$userInfo = array(
				'userID' => $response['user'],
				'user' => $params['user'],
				'name' => $response['Data'][0]['name'],
				'code' => $response['code'],
				'avatar' => $avatar,
				'validTime' => $response['valid_time'],	
				'last_login' => $response['last_login_time']['web'],		
				'islogin' => true
			);

			$this->set_session($userInfo);
			return true;	
		}

		$this->form_validation->set_message('check_login', $response['mess']);
		return false;
	}

	#显示注册页面
	public function register(){
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
	
	protected function userInfo($user){
		$data=array(
		'phone'=>$user['user'],
		'name'=>'',
		'address'=>'',
		'idNumber'=>''
		);
		$parameter=array();
		array_push($parameter,'user='.$user['user']);
		//用户手机号，省份证，姓名
		$getInfo=$this->request('user', 'getInfo', $parameter);
		//用户地址
		$getAddressInfo=$this->request('user', 'getAddressInfo', $parameter);
		if ($getInfo === false) {
			error('网络超时，请稍后再试！');
		}
		if($getInfo['fail'] ==0){
			//{"fail":0,"Data":[{"userID":"10","0":"10","phoneNumber":"18225605421","1":"18225605421","referer":"","2":"","name":"","3":"","birthday":"0000-00-00","4":"0000-00-00","ID_number":"","5":""}]}
			$data['name']=$getInfo['Data'][0]['name'];
			$data['idNumber']=$getInfo['Data'][0]['ID_number'];		
		}
		if($getAddressInfo['fail'] ==0){
			$data['address']=$getAddressInfo['Data'][0]['address'];
		}
		$this->load->view('personal.html',$data);	
	}

	protected function setUserInfo($user){		
		$data = $this->input->post();
		if (empty($data['name'])){
			error('姓名不能为空');
		}
		if (empty($data['idNumber'])){
			error('身份证号码不能空');
		}
		//用户手机号，省份证，姓名
		//user=15955142348&birthday=1977-09-10&name=张友&ID_number=340989197709102034&parentID=18902938231
		$parameter=array();
		array_push($parameter,'user='.$user['user']);
		array_push($parameter,'birthday='.date("y-m-d",time()));
		array_push($parameter,'name='.$data['name']);
		array_push($parameter,'ID_number='.$data['idNumber']);
		array_push($parameter,'parentID=15920465334');
		$userInfo=$this->request('user', 'info', $parameter);
		//用户地址
		$addAarray=array();
		array_push($addAarray,'user='.$user['user']);
		array_push($addAarray,'address='.$data['address']);
		$address=$this->request('user', 'address',$addAarray);
		if ($userInfo === false||$address===false) {
			error('网络超时，请稍后再试！');
		}
		if($userInfo['fail'] != 0) {
			error($userInfo['mess']);
		}
		if($address['fail'] != 0) {
			error($address['mess']);
		}
		success('user/person', '信息更新成功');
	}
}
?>
