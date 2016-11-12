<?php defined('BASEPATH') or die('Access Denied');

class UserApi extends Home_Controller
{	

	/**
	 * 修改登录密码
	 * @return [type] [description]
	 */
	public function changePass(){
		$params['user'] = $this->user;
		$params['pass'] = $this->input->post('pass');
		$params['newpass'] = $this->input->post('newpass');
		$params['lastcode'] = $this->code;

		$response = $this->request('dealer', 'pass/change', $params);
		echo $response;
	}

	/**
	 * 退出登录
	 * @return [type] [description]
	 */
	public function logout(){
		$params['user'] = $this->get_session('user');
		$params['last_code'] = $this->get_session('code');

		$result = $this->request('dealer', 'auth/logout', $params);
		$result = json_decode($result, true);

		if ($result['fail'] == 0) {
			$response['fail'] = 0;
			$response['mess'] = '退出成功';
			$this->session->unset_userdata();
			$this->session->sess_destroy();
			redirect('User/login');
		} else {
			$response['fail'] = 1004;
			$response['mess'] = '退出失败';
		}

		echo json_encode($response);
	}

	public function lists($type){
		$this->$type();
	}

	public function shopLists(){
		$params['user'] = $this->user;
		$params['signature'] = $this->input->get('signature');
		$params['page'] = $this->input->get('page');
		$params['pageSize'] = $this->input->get('pageSize');
		$offset = intval($params['page'])-1;
		$pageSize = intval($params['pageSize']);
		$params['limit'] = $offset . ',' . $pageSize;
		$result = $this->request('dealer', 'shop/lists', $params);
		$response = json_decode($result, true);
		$response['pageSize'] = ($response['content'] == false) ? 0 : count($response['content']);

		echo json_encode($response);
	}

	public function order(){
		$params['user'] = '13993001010';//$this->user;
		$params['signature'] = $this->input->get('signature');
		$params['page'] = $this->input->get('page');
		$params['pageSize'] = $this->input->get('pageSize');
		$offset = intval($params['page'])-1;
		$pageSize = intval($params['pageSize']);
		$params['limit'] = $offset . ',' . $pageSize;
		$result = $this->request('money', 'running/user', $params);
		$response = json_decode($result, true);
		$response['pageSize'] = ($response['data'] == false) ? 0 : count($response['data']);
		echo json_encode($response);
	}

	//搜索, 时间, 类型
	public function search(){
		$params['user'] = '13993001010';//$this->user;
		$params['type'] = $this->input->get('type');
		if ($params['type'] == 0 ) {
			unset($params['type']);
		}
		$params['start'] = $this->input->get('start');
		$params['end'] = $this->input->get('end');
		$params['page'] = $this->input->get('page');
		$params['pageSize'] = $this->input->get('pageSize');
		$offset = intval($params['page'])-1;
		$pageSize = intval($params['pageSize']);
		$params['limit'] = $offset . ',' . $pageSize;
		$result = $this->request('money', 'running/user', $params);
		$response = json_decode($result, true);
		$response['pageSize'] = ($response['data'] == false) ? 0 : count($response['data']);
		echo json_encode($response);
	}

}