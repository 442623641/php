<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserCenter extends User_Controller 
{
	public function __construct(){
		parent::__construct();
	}

	//充值
	public function chongzhi(){
		$this->load->view("chongzhi.html");
	}

	//提现
	public function tixian(){
		$data['username']= empty($this->username) ? $this->user : $this->username;

		$param['user'] = $this->user;
		$result = $this->request('AccountApi', 'userMoney/balance', $param);

		if ($result['fail'] !== 0) {
			echo json_encode($result);
			return ;
		}
		$data['result'] = $result;

		$this->load->view('tixian.html', $data);
	}

	//交易记录
	public function jiaoyi($start,$length){
		$params['user'] = $this->get_session("userid");
		$params['limit'] = $start.','.$length;
		$response=$this->request('AccountApi', 'charges', $params);
		$data['result'] = $response['data'];
		$data['start'] = $start+$length;
		$this->load->view("jiaoyi.html",$data);
	}
	
	//加载更多交易记录
	public function jiaoyi2($start,$length){
		$params['user'] = $this->get_session("userid");
		//$params['user'] = '13993001010';
		$params['limit'] = $start.','.$length;
		$response=$this->request('AccountApi', 'charges', $params);
		$data['list'] = $response['data'];
		$data['start'] = $start+$length;
		$data['flag'] = $response['fail'];
		$res = json_encode($data);
		echo $res;
	}

	//转账
	
	//消息
	public function xiaoxi(){
		$this->load->view("xiaoxi.html");
	}

	//个人设置
	public function shezhi(){
		$this->load->view("shezhi.html");
	}

	//身份认证
	public function renzhen(){
		$params['userid'] = $this->get_session("userid");
		//$params['lastcode'] = $this->get_session("code");
		$response=$this->request('Api', 'userInfo', $params);
		$data['result'] = $response['Data'];
		$this->load->view("renzhen.html",$data);
	}

	
	//身份认证保存
	public function renzhenSave(){
		$params['userid'] = $this->get_session("userid");
		//$params['lastcode'] = $this->get_session("code");
		$params['name'] =  $this->input->post('trueName');
		$params['ID_number'] =  $this->input->post('ID_number');
		$response=$this->request('Api', 'setUserInfo', $params);
		$data = json_encode($response);
		echo $data;
//		if($response['fail']==0){
//			$this->renzhen();
//		}else{
//			$data['result'] = $response;
//			$this->load->view("renzhen.html",$data);
//		}

	}	
		
	//银行卡
	public function bank(){
		$params['userid'] = $this->get_session("userid");
		$params['lastcode'] = $this->get_session("code");
		$response=$this->request('Api', 'userBankInfo/lists', $params);
		if($response['fail']==0){
			$data['result'] = $response['Data'];
		}
		$data['flag'] = $response['fail'];
		$this->load->view("bank.html",$data);
	}
	
	//添加银行卡
	public function addBank(){
		$this->load->view("bank2.html");
	}
	
	//保存添加银行卡
	public function saveAddBank(){
				//bankid, bankType, branch, bankAddr
		$params['userid'] = $this->get_session('userid');
		$params['bankid'] =  $this->input->post('bankid');
		$this->load->helper('bankLocation');
		$this->config->load('banklists');
		
		$bankLists = $this->config->config['banklists'];
		$params['bankname'] = bankInfo($params['bankid'],$bankLists);
		//$temp['branch'] = $this->input->post('branch');
		//$temp['bankAddr'] = $this->input->post('bankAddr');
		//$params['branch'] =  json_encode($temp);
		$params['branch'] =  $this->input->post('bankAddr');
		$params['lastcode'] = "";
		$response=$this->request('Api', 'userBankInfo/add', $params);
		
		$data = json_encode($response);
		echo $data;
//		$data['result'] = $response;
//		if($response['fail']==0){
//			$this->bank();
//		}else{
//			$this->load->view("bank2.html");
//		}
		
	}
	
	//解绑银行卡
	public function deleteBank(){
		$params['userid'] = $this->get_session("userid");
		$params['bankid'] = $this->input->post('bankid');
		$response=$this->request('Api', 'userBankInfo/delete', $params);
		$data = json_encode($response);
		echo $data;
		//$data['result'] = $response;
		//$this->load->view("bank.html",$data);
	}
	
	//登录密码
	public function xiugaimima(){
		$this->load->view("xiugaimima.html");
	}
	
	//保存修改登录密码
	public function saveXiugaimima(){
		$params['user'] = $this->get_session("user");
		$params['pass'] =  $this->input->post('oldPassword');
		$params['newpass'] =  $this->input->post('newPassword');
		$response=$this->request('Api', 'changePassword', $params);
		$data['fail'] = $response['fail'];
		$data['mess'] = $response['mess'];
		if($response['fail']==0){
			$params2['signature'] =  '';
			$params2['user'] = $this->session->userdata('user');
			$response2=$this->request('Api', 'logout', $params2);
			$this->session->unset_userdata('user');
			$this->session->sess_destroy();
			$data['redirect'] = site_url('user/login');
		}
		$data = json_encode($data);
		echo $data;

//		$data['result'] = $response;
//		$this->load->view("xiugaimima.html",$data);
	}
	
	//支付密码
	public function zhifumima(){
		$params['userid'] = $this->get_session("userid");
		$params['signature'] = "";
		$response=$this->request('AccountApi', 'getPayConf', $params);
		if(isset($response['data']['pay_pass'])){
			$this->load->view("yishezfmm.html");
		}else{
			$this->load->view("shezhizfmm.html");
		}
		
	}

	public function codeSend(){
		$params['phone'] = $this->get_session("user");
		$params['purpose'] =  'paypass';
		$response=$this->request('Api', 'codeSend', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	public function codeValid(){
		$params['phone'] = $this->get_session("user");
		$params['purpose'] =  'paypass';
		$params['sms_code'] =  $this->input->post('sms_code');;
		$response=$this->request('Api', 'codeValid', $params);
//		if($response['fail']==0){
//			return true;
//		}else{
//			return false;
//		}
		return $response;
	}
	
	public function setPayPass(){
		$valid_code = $this->codeValid();
		if($valid_code['fail']==0){
			$params['userid'] = $this->get_session("userid");
			$params['paypass'] =  $this->input->post('paypass');
			$params['signature'] =  '';
			$response=$this->request('AccountApi', 'setPayPass', $params);
			$data = json_encode($response);
			echo $data;
		}else{
			$data['mess'] = $valid_code['mess'];
			$data['fail'] = $valid_code['fail'];
			$data = json_encode($data);
			echo $data;
		}

	}
	
	public function chongZhiPayPass(){
		$this->load->view("chongzhizfmm.html");
	}

	//保存支付密码
	public function saveZhifumima(){
		$params['userid'] = $this->get_session("userid");
		$params['pass'] =  $this->input->post('pass');
		$params['newpass'] =  $this->input->post('newpass');
		$response=$this->request('AccountApi', 'changePayPass', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	
	//资料
	public function ziliao(){
		$params['userid'] = $this->get_session("userid");
		//$params['lastcode'] = $this->get_session("code");
		$response=$this->request('Api', 'userInfo', $params);
		$data['result'] = $response['Data'];
		$this->load->view("ziliao.html",$data);
	}
	
	//资料
	public function ziliaoSave(){
	
		$params['userid'] = $this->get_session("userid");
		//$params['name'] =  $this->input->post('name');
		//$params['newphone'] =  $this->input->post('newphone');
		$params['sex'] =  $this->input->post('sex');
		$params['qq'] =  $this->input->post('qq');
		$params['email'] =  $this->input->post('email');
		$params['birthday'] =  $this->input->post('birthday');
		$response=$this->request('Api', 'setUserInfo', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	//地址管理首页
	public function dizhi(){
		$params['userid'] = $this->get_session("userid");
		$response=$this->request('Api', 'address/lists', $params);
		$data['fail'] = $response['fail'];
		if($response['fail']==0){
			$data['result'] = $response['Data']	;
		}
		//var_dump($data['result']);
		$this->load->view("dizhi.html",$data);
	}
	
	//删除地址
	public function deleteDizhi(){
		$params['userid'] = $this->get_session("userid");
		$params['address_no'] =  $this->input->post('addressNo');
		$response=$this->request('Api', 'address/delete', $params);
		$data = json_encode($response);
		echo $data;
	}
	
	
	//添加地址页面
	public function addDizhi($addressNo){
		$data['cur_num'] = $addressNo;
		$this->load->view("dizhi_add.html",$data);
	}
	
	//保存添加的地址
	public function addDizhiSave(){
		$params['userid'] = $this->get_session("userid");
		$params['type'] =  $this->input->post('type');
		$params['name'] =  $this->input->post('name');
		$params['phonenumber'] =  $this->input->post('phone');
		$params['province'] =  $this->input->post('province');
		$params['city'] =  $this->input->post('city');
		$params['district'] =  $this->input->post('district');
		$params['address'] =  $this->input->post('address');
		$params['GPS_lng'] =  '1';
		$params['GPS_lat'] =  '2';
		$params['youbian'] =  $this->input->post('youbian');
		$params['signature'] =  "";
		$response=$this->request('Api', 'address/add', $params);
		$data['fail'] = $response['fail'];
		$data['mess'] = $response['mess'];
		$data['redirect'] = site_url('UserCenter/dizhi');
		$data = json_encode($data);
		if($response['fail']==0){
			$cur_num = $this->input->post('cur_num');
			if(isset($cur_num)){
				$params2['userid'] = $this->get_session("userid");
				$params2['type'] =  '2';
				$params2['address_no'] =  $cur_num;
				$response=$this->request('Api', 'address/update', $params2);
			}
		}

		echo $data;
	}
	
	
	//修改地址页面
	public function updateDizhi(){
		$data['addressNo'] = $_GET['addressNo'];
		$data['name'] = $_GET['name'];
		$data['phone'] = $_GET['phone'];
		$data['province'] = $_GET['province'];
		$data['city'] = $_GET['city'];
		$data['district'] = $_GET['district'];
		$data['address'] = $_GET['address'];
		$data['youbian'] = $_GET['youbian'];
		$data['type'] = $_GET['type'];
		$data['cur_num'] = $_GET['cur_num'];
		$this->load->view("dizhi_modify.html",$data);
//		$data['nexturl'] = site_url('dizhi_modify.html');
//		$data = json_encode($data);
//		echo $data;
	}
	
	public function updateDizhiSave(){
		$params['userid'] = $this->get_session("userid");
		$params['address_no'] = $this->input->post('addressNo');
		$params['type'] =  $this->input->post('type');
		$params['name'] =  $this->input->post('name');
		$params['phonenumber'] =  $this->input->post('phone');
		$params['province'] =  $this->input->post('province');
		$params['city'] =  $this->input->post('city');
		$params['district'] =  $this->input->post('district');
		$params['address'] =  $this->input->post('address');
		$params['GPS_lng'] =  '1';
		$params['GPS_lat'] =  '2';
		$params['youbian'] =  $this->input->post('youbian');
		$params['signature'] =  "";
		$response=$this->request('Api', 'address/update', $params);
		$data['fail'] = $response['fail'];
		$data['mess'] = $response['mess'];
		$data['redirect'] = site_url('UserCenter/dizhi');
		$data = json_encode($data);
		if($response['fail']==0){
			$cur_num = $this->input->post('cur_num');
			if(isset($cur_num)){
				$params2['userid'] = $this->get_session("userid");
				$params2['type'] =  '2';
				$params2['address_no'] =  $cur_num;
				$response=$this->request('Api', 'address/update', $params2);
			}
		}

		echo $data;

	}
	
	//修改默认地址
	public function updateDefault(){
		$params['userid'] = $this->get_session("userid");
		$params['type'] =  $this->input->post('type');
		$params['address_no'] =  $this->input->post('addressNo');
		$response=$this->request('Api', 'address/update', $params);
		$data = json_encode($response);
		echo $data;
	}
}
