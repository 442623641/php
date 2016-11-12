<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 交易记录API
 */
class UserApi extends User_Controller
{
	public function __construct(){
		parent::__construct();
	}

	//记录分页显示
	public function records(){
		$params['lastcode'] = $this->get_session('code');
		$params['userid'] = $this->userid;
		$params['type'] = $this->input->get_post('type');
		$params['start'] = $this->input->get_post('start');
		$params['end'] = $this->input->get_post('end');
		$params['type'] = empty($params['type']) ? "" : $params['type'];
		$params['start'] = empty($params['start']) ? "" :$params['start'];
		$params['end'] = empty($params['end']) ? "" : $params['end'];

		$page = intval($this->input->get_post('pageNum'));
		$pageSize = 10;
		$offset = $page * $pageSize;
		$params['limit'] = "{$offset}, {$pageSize}";
		$result = $this->request('AccountApi', 'charges', $params);

		if ($result['fail'] != 0) {
			echo json_encode($result);
			return ;
		} else {
			$response['fail'] = 0;
			$response['total'] = $result['total'];
			$response['totalPage'] = ceil($response['total']/$pageSize);
			$response['pageSize'] = $pageSize;
			$response['list'] = $result['data'];
		}
		
		$response = json_encode($response);
		echo $response;
	}

	//搜索记录,多加了时间条件
	public function search(){
		$params['lastcode'] = $this->get_session('code');
		$params['userid'] = $this->userid;
		$params['type'] = $this->input->get_post('type');
		$params['start'] = $this->input->get_post('start');
		$params['end'] = $this->input->get_post('end');
		$params['type'] = empty($params['type']) ? "" : $params['type'];
		$params['start'] = empty($params['start']) ? "" :$params['start'];
		$params['end'] = empty($params['end']) ? "" : $params['end'];
		if ($params['type'] == 0 ) {
			unset($params['type']);
		}

		$page = intval($this->input->get_post('pageNum'));
		$pageSize = 10;
		$offset = $page * $pageSize;
		$params['limit'] = "{$offset}, {$pageSize}";
		$result = $this->request('AccountApi', 'charges', $params);

		if ($result['fail'] != 0) {
			echo json_encode($result);
			return ;
		} else {
			$response['fail'] = 0;
			$response['total'] = $result['total'];
			$response['totalPage'] = ceil($response['total']/$pageSize);
			$response['pageSize'] = $pageSize;
			$response['list'] = $result['data'];
		}
		
		$response = json_encode($response);
		echo $response;
	}

	/**
	 * 修改手机号码
	 * @return [json] [fail:0;mess:修改成功]
	 */
	public function modPhone(){
		$params['userid'] = $this->userid;
		$params['phone'] = $this->input->post('phone');
		$params['newphone'] = $this->input->post('newphone');
		$response = $this->request('Api', 'changePhone', $params);
		echo json_encode($response);
	}

	/**
	 * 实名认证
	 * @return [json] [fail:0;mess:验证成功]
	 */
	public function confirm(){
		$params['userid'] = $this->userid;
		$params['name'] = $this->input->post('name');
		$params['ID_number'] = $this->input->post('ID_number');
		$params['signature'] = $this->input->post('signature');
		$response = $this->request('Api', 'setUserInfo', $params);
		echo json_encode($response);
	}

	//修改密码
	public function modPass(){
		$params['user'] = $this->user;
		$params['pass'] = $this->input->post('oldPass');
		$params['newpass'] = $this->input->post('newPass');
		$params['lastcode'] = $this->code;

		$response = $this->request('Api', 'changePassword', $params);
		echo json_encode($response);
	}

	//支付密码设置
	public function setPayPass(){
		$params['userid'] = $this->userid;
		$params['paypass'] = $this->input->post('paypass');
		$params['repass'] = $this->input->post('repass');

		if ($params['paypass'] != $params['repass']) {
			$response = array(
				'fail' => 5,
				'mess' => '两次输入密码不一致，请重新设置',
				);

			echo json_encode($response);
			return ;
		}

		$result = $this->request('AccountApi', 'setPayPass', $params);
		echo json_encode($result);
	}

	//添加地址
	public function addAddr(){
		$params = $this->input->post();
		$params['userid'] = $this->userid;
		if (!isset($params['GPS_lng']) || !isset($params['GPS_lat'])) {
			$params['GPS_lng'] = '120';
			$params['GPS_lat'] = '60';
		}

		$result = $this->request('Api', 'address/add', $params);
		echo json_encode($result);
	}

	//更新地址
	public function updateAddr(){
		$params = $this->input->post();
		$params['userid'] = $this->userid;

		$result = $this->request('Api', 'address/update', $params);
		echo json_encode($result);
	}

	//批更新地址
	public function batUpdate(){
		$params['userid'] = $this->userid.','.$this->userid;
		$params['address_no'] = $this->input->get_post('address_no');
		$params['type'] = $this->input->get_post('add_type');

		$result = $this->request('Api', 'batUpdate', $params);
		echo json_encode($result);
	}

	//删除地址
	public function delAddr(){
		$params['userid'] = $this->userid;
		$params['address_no'] = $this->input->get_post('address_no');

		$result = $this->request('Api', 'address/delete', $params);
		echo json_encode($result);
	}

	//个人资料修改
	public function modPerson(){
		$params['userid'] = $this->userid;
		$params['sex'] = $this->input->get_post('sex');
		$params['qq'] = $this->input->get_post('qq');
		$params['email'] = $this->input->get_post('email');
		$params['birthday'] = $this->input->get_post('birthday');

		$result = $this->request('Api', 'setUserInfo', $params);
		echo json_encode($result);
	}

	//注销动作
	public function logout(){
		$params['user'] = $this->user;
		$params['type'] = 'web';
		$params['signature'] = $this->code;
		$result = $this->request('Api', 'logout', $params);
		if ($result['fail'] === 0) {
			$this->session->unset_userdata();
			$this->session->sess_destroy();
			redirect('user/login');
		} else {
			echo $result['mess'];
		}
	}

	//请求地址
	public function address(){
		$params['userid'] = $this->userid;
		$result = $this->request('Api', 'address/lists', $params);
		echo json_encode($result);
	}
}