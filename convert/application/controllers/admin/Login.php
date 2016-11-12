<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class Login extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/User_model');
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->helper('captcha');
	}	
	
	#显示登录页面
	public function index(){
		$data = array('flag' => 0);
		$this->load->view('admin/login.html',$data);
	}
	
	#生成验证码
	public function code(){
		#调用函数生成验证码
		$code = create_captcha();
		#将验证码字符串保存到session中
		$this->session->set_userdata('admin_valid_code_session',$code);
	}
	
	#处理登录
	public function signin(){
		//echo "进入登陆处理";
		//exit();
		#设置验证规则
		$this->form_validation->set_rules('username','用户名','required');
		$this->form_validation->set_rules('password','密码','required|callback_check_login');
		$this->form_validation->set_rules('admin_valid_code', '验证码', 'required|callback_codeCheck');

			#验证码正确，则需要验证用户名和密码
			if ($this->form_validation->run() == false){
				$data = array('flag' => 1);
				$this->load->view('admin/login.html',$data);
			} else{
				$username = $this->input->post('username',true);
				$password = $this->input->post('password',true);
				$user_info=$this->User_model->check_user_login($username,md5($password));
				
                    //第二种设置cookie的方式：通过CI框架的input类库  
                    $this->input->set_cookie("username",$user_info['account'],3600);  
                    $this->input->set_cookie("password",$user_info['password'],3600);  
                    $this->input->set_cookie("user_id",$user_info['user_id'],3600);  
                    
                    $data1 = array(
	                   'admin_username'  => $user_info['account'],
	                   'admin_user_id'     => $user_info['user_id'],
                       'true_name'     => $user_info['name']
               		);
               		$data2['nodes'] =$this->User_model->getUserNodes($user_info['user_id']);
               		$data = array_merge($data1,$data2);
                    $this->session->set_userdata($data);  
                    //$nodes = $this->User_model->getUserNodes($user_info['user_id']);
                    $this->load->view('admin/index.html',$data);
		} 
	}
	
	public function codeCheck(){
		#获取表单数据
		$admin_valid_code = strtolower($this->input->post('admin_valid_code'));		
		#获取session中保存的验证码
		$code = strtolower($this->session->userdata('admin_valid_code_session'));
		
		if ($admin_valid_code!=$code) {
			$this->form_validation->set_message('codeCheck','%s错误');
			return false;
			
		}else{
			return true;
		}
	}
	
	public function check_login(){
		$username = $this->input->post('username',true);
		$password = $this->input->post('password',true);
		$user_info=$this->User_model->check_user_login($username,md5($password));  
        if($user_info['user_id'] > 0){
        	return true;
        }else{
        	$this->form_validation->set_message('check_login','用户名或密码错误');
        	return false;
        }
	}
	
	#注销动作
	public function logout(){
		$array_items = array('admin_username' => '', 'admin_user_id' => '');
		$this->session->unset_userdata($array_items);
		$this->session->sess_destroy();
		$data = array('flag' => 0);
		$this->load->view('admin/login.html',$data);
	}
	
	public function modifyPaaword(){
		$data = array('flag' => 0);
		$this->load->view('admin/modify_pass.html',$data);
	}
	
	public function saveModifyPass(){
		$this->load->Model('admin/User_model');
		$this->form_validation->set_rules('password_old','原密码','required|callback_check_pass');
		$this->form_validation->set_rules('password_new','新密码','required');
		$this->form_validation->set_rules('password_confirm', '确认密码', 'required|matches[password_new]');

		#验证码正确，则需要验证用户名和密码
		if ($this->form_validation->run() == false){
			$data = array('flag' => 0);
			$this->load->view('admin/modify_pass.html',$data);
		} else{
			$data = $this->session->all_userdata();
			$user_id = $data['admin_user_id'];
			$password_new = $this->input->post('password_new',true);
			$arr = array('password'=>md5($password_new));
        	$this->User_model->edit($user_id, $arr); 
        	$data = array('flag' => 1);
        	$this->load->view('admin/modify_pass.html',$data);
		} 
		
	}
	
	public function check_pass(){
		$data = $this->session->all_userdata();
		$user_id = $data['admin_user_id'];
		
		$password_old = $this->input->post('password_old',true);
		$res=$this->User_model->check_pass($user_id,md5($password_old));  
        if($res){
        	return true;
        }else{
        	$this->form_validation->set_message('check_pass','原密码错误！');
        	return false;
        }
	}
}  