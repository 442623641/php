<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Home_Controller 
{
	public $title = array(
		'home' => "首页",
		"server" => "服务中心",
		"person" => "个人中心",
		'personinfo' => "个人信息",
		'modifypwd' => "修改密码",
		);

	public function __construct(){
		parent::__construct();
		$this->load->helper('url');
		$url = explode('/', strtolower(uri_string()));
		$data['title'] = $this->title[array_pop($url)];
		$this->data['header'] = $this->load->view('header.html', $data, true);
		$this->data['footer'] = $this->load->view('footer.html', '', true);
	}

	//服务中心
	public function server(){
		$data['header'] = $this->data['header'];
		$data['footer'] = $this->data['footer'];

		$this->load->view('server.html', $data);
	}

	//个人中心
	public function person(){
		$data['header'] = $this->data['header'];
		$data['footer'] = $this->data['footer'];
		$data['username']= empty($this->username) ? $this->user : $this->username;

		$param['userid'] = $this->userid;
		$result = $this->request('AccountApi', 'userMoney/balance', $param);

		if ($result['fail'] !== 0) {
			echo json_encode($result);
			return ;
		}
		$data['result'] = $result;

		$this->load->view('personal.html', $data);
	}


	//个人资料
	public function personInfo(){

		if ($_POST) {
			$response = $this->doModifyPerson();
			echo json_encode($response);
			return ;
		}

		//用户信息
		$data['userinfo'] = json_decode($this->get_session('userinfo'), true);

		//请求用户地址
		$param['user'] = $this->user;
		$result = $this->request('user', 'address/list', $param);

		if ($result['fail'] !== 0) {
			echo json_encode($result);
			return ;
		}
		$data['address'] = $result;

		$this->load->view('personal_info.html', $data);
	}


	//密码修改
	public function modifyPwd(){
		if ($_POST) {
			$response = $this->doModifyPwd();
			echo json_encode($response);
			exit;
		}
		$data['user'] = $this->username;
		$this->load->view('modify_pw.html', $this->data);
	}

	protected function doModifyPwd(){
		//判断旧密码是否正确的表单验证扩展
		$data = $this->input->post();
		$user = $this->session->userdata('user');
		$result = array();

		if ($data['old_passwd'] != $this->session->userdata('pass')) {
			$result['fail'] = 3;
			$result['msg'] = "旧密码错误";
		} else if ($data['passwd'] != $data['passwdCf']) {
			$result['fail'] = 5;
			$result['msg'] = '两次密码输入不一致';
		} else {
			$url = self::BASE_API . 'changePassword?user='. $user . '&newPassword=' . $data['passwd'] . '&oldPassword=' . $data['old_passwd'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($ch);
			$response = json_decode($response, true);

			curl_close($ch);
			
			if ($response['fail'] === 0) {
				//删除session
				$this->session->unset_userdata('user');
				$this->session->sess_destroy();

				$result = array(
					'fail' => 0,
					'msg' => '修改成功',
					'redirect' => site_url('user/login'),
					);
			} else if ($response === NULL) {
				$result = array(
					'fail' => 4,
					'msg' => '暂不支持修改密码',
					'redirect' => site_url('home/person'),
					);
			} else {
				$result = array(
					'fail' => $response['fail'],
					'msg' => '修改失败',
					);
			}


		}

		return $result;
	}

	protected function doModifyPerson(){
		$data = $this->input->post();
		$url = self::BASE_API . 'setUserInfo?user='.$data["user"].'&idNumber='.$data["idNumber"].'&address='.$data["address"].'&name='.$data["name"];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		$response = json_decode($response, true);
		curl_close($ch);

		if ($response === false) {
			$data = array(
				'fail' => 5,
				'mess' => '未知错误，稍后重试',
				);

			return $data;
		} else if($response['fail']==0){
			$this->session->set_userdata($data);
		}
		return $response;
	}
}
