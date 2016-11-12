<?php defined('BASEPATH') or die('Access Denied');

/**
 * 用户管理控制器
 */
class User extends Common_Controller
{
	public function login(){
		$this->load->view('login.html');
	}

	public function doLogin(){
		$params = $this->input->post();
		$result = $this->request('dealer', 'auth/login', $params);
		$result = json_decode($result, true);
		var_dump($result);
		if ($result['fail'] == 0) {
			$session = array(
				'user' => $params['user'],
				'dealerid' => $result['dealerID'],
				'code' => $result['code'],
				'valid_time' => $result['valid_time'],
				'isLogin' => true,
				);

			$response = array(
				'fail' => 0,
				'mess' => '登录成功',
				);

			$param['user'] = $params['user'];
			$param['lastcode'] = $result['code'];
			$userInfo = $this->request('dealer', 'info/get', $param);
			$userInfo = json_decode($userInfo, true);
			if ($userInfo['fail'] == 0) {
				$session['name'] = $userInfo['content'][0]['name'];
			} else {
				$response['mess'] = '获取用户信息失败';
			}
			@$this->set_session($session);

		} else {
			$response = array(
				'fail' => 1004,
				'mess' => '登录失败',
				);
		}

		echo json_encode($response);
	}
}