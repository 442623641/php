<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//http://api.cxiang.net/money/running/user?user=13993001010&type=1&start=2015-06-16&end=2015-06-19
#--------------------------------------------------------------
# 用户操作接口
# Api
#--------------------------------------------------------------
# yulei <464001224@qq.com>
# 2015-6-30
#--------------------------------------------------------------
class API extends API_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
	}
	/**
	 * 验证是否已经是注册用�&#65533;;已注册提示错�&#65533;; 未注�&#65533;,发送验证码
	 * Api/validUser
	 * @params user
	 * @return [json] [fail:0]
	 */
	public function validUser(){
		$params['user'] = $this->input->get_post('user');
		$response = $this->request('user', 'phone/valid', $params);
		echo $response;
	}
	/**
	 * 用户注册 Api/register
	 * @params user=18258117622, referer=推荐�&#65533;, code=验证�&#65533;, password
	 * @return json 0-成功;1-失败 
	 */
	public function register(){
		$params['user'] = $this->input->get_post('user');
		$params['pass'] = $this->input->get_post('password');
		$params['referer'] = $this->input->get_post('refer');
		$params['referer'] = $params['referer'] ? $params['referer'] : '9999';
		$params['code'] = $this->input->get_post('code');
		$response=$this->request('user', 'phone/register', $params);
		$response = json_decode($response, true);
		if ($response['fail'] == 0) {
			$param['user'] = $response['userID'];
			$param['shop'] = $this->input->get_post('shop');
			$param['app'] = $this->input->get_post('app');
			$param['referee'] = $params['referer'];
			$param['shop'] = $params['shop'] ? $param['shop'] : 'shop001';
		$param['app'] = $param['app'] ? $param['app'] : 'default';
			$result = $this->request('money', 'appuser/add', $param);
			echo $result;
		} else {
			echo json_encode($response);
		}
	}
	//店铺推广
	public function appUser(){
		///money/appuser/add?user=xxx&referee=xxx&shop=xxx
		$params['user'] = $this->input->get_post('user');
		$params['shop'] = $this->input->get_post('shop');
		$params['app'] = $this->input->get_post('app');
		$params['referee'] = $this->input->get_post('refer');
		$params['referee'] = $params['referee'] ? $params['referee'] : '9999';
		$params['shop'] = $params['shop']?$params['shop']:'shop001';
		$response=$this->request('money', 'appuser/add', $params);
		echo $response;
	}
	//推广连接
	public function refer(){
		$arr = (Object)array(
			'fail' => 0,
			'data' => "http://xk.xiangw.com.cn/index.php/user/register"
		);
		echo json_encode($arr);
	}
	/**
	 * 发送手机验证码(正式�&#65533;)
	 * GET, POST
	 * @params phone, purpose(验证码用�&#65533;,resetpassword)
	 * @return json [fail:0; send_time:发送时间]
	 */
	public function codeSend(){
		$params['phone'] = $this->input->get_post('phone');
		$params['purpose'] = $this->input->get_post('purpose');
		$this->request('phone', 'code/send', $params);
		$response = $this->request('phone', 'code/get', $params);
		echo $response;
	} 
	/**
	 * 验证手机验证�&#65533;(正式�&#65533;)
	 * @params phone, purpose, sms_code
	 * @return json [fail:0-成功; error_code-失败]
	 */
	public function codeValid(){
		$params['phone'] = $this->input->get_post('phone');
		$params['purpose'] = $this->input->get_post('purpose');
		$params['sms_code'] = $this->input->get_post('sms_code');
		$response = $this->request('phone', 'code/valid', $params);
		echo $response;
	}
	/**
	 * 登录 Api/login
	 * @params user, password, type=web, android, ios
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	public function login(){
		$params['user'] = $this->input->get_post('user');
		$params['pass'] = $this->input->get_post('password');
		$params['type'] = $this->input->get_post('type');
		$response=$this->request('user', 'auth/login', $params);
		$response = json_decode($response);
		if ($response->fail == 0) {
			$param['user'] = $response->user;
			$result = $this->request('user', 'info/get', $param);
			$result = json_decode($result);
			$result->code = $response->code;
			//$result->last_login_time = $response->last_login_time;
			//$result->login_state = $response->login_state;
			$result->user = $response->user;
			$result->sessionID = session_id();
			echo json_encode($result);
			return ;
		} else {
			echo json_encode($response);
		}
	}
	/**
	 * 退出登�&#65533; Api/logout, user,signature
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	public function logout(){
		$params['user'] = $this->input->get_post('user');
		$params['type'] = $this->input->get_post('type');
		$response = $this->request('user', 'auth/logout', $params);
		echo $response;
	}
	/**
	 * 修改密码 Api/changePassword
	 * @params user, pass, newpass,signature
	 * @return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	public function changePassword() {
		$params['user'] = $this->input->get_post('user');
		$params['pass'] = $this->input->get_post('pass');
		$params['newpass'] = $this->input->get_post('newpass');
		$response=$this->request('user', 'pass/change', $params);
		echo $response;
	}
	/**
	 * 忘记密码 Api/forgotPassword
	 * @params user, sms_code, newpass
	 * @return json
	 */
	public function forgotPassword(){
		$params['user'] = $this->input->get_post('user');
		$params['sms_code'] = $this->input->get_post('sms_code');
		$params['newpass'] = $this->input->get_post('newpass');
		$response = $this->request('user', 'pass/reset', $params);
		echo $response;
	}
	/**
	 * 获取用户信息 Api/userInfo
	 * GET, POST
	 * @params userid=userID,signature
	 * @return [json] 
	 * {"fail":0,"Data":[{"userID":"4","phoneNumber":"13993001010","referer":"13954301610","name":"信息","sex":"0","birthday":"1990-09-10","ID_number":"340104199009102034","qq":"145896325","Email":"rrui@qq.com","avatar":"men.jpg"}]}
	 */
	public function userInfo(){
		$params['user'] = @$this->input->get_post('userid');
		$params['phone'] = @$this->input->get_post('phone');

		if (empty($params['user'])&&empty($params['phone'])) {
			$response = array(
				'fail' => 2,
				'mess' => '用户ID(userid)和手机号(phone)两个必须要有一�&#65533;',
				);
			echo json_encode($response);
			return ;
		}

		$response = $this->request('user', 'info/get', $params);
		echo $response;
	}


	/**
	 * 银行卡操�&#65533; Api/userBankInfo/lists(add,update,delete)
	 * @params userid=userID, bankid=银行卡号, bankname=民生银行, branch=分行名称, cardID=卡ID,signature
	 * @return [json] 0-成功; error_code
	 */
	public function userBankInfo($type){
		$types = array('lists', 'add', 'update', 'delete');
		if (!in_array($type, $types)) {
			$response = array(
				'fail' => 2,
				'mess' => '请求错误,操作只能是list, add, update, delete',
				);
			echo json_encode($response);
			return ;
		}
		
		if ($_GET) {
			$params = $this->input->get();
			$params['user'] = $this->input->get_post('userid');
			unset($params['userid']);
		} else {
			$params = $this->input->post();
			$params['user'] = $this->input->get_post('userid');
			unset($params['userid']);
		}
		if ($type == 'add' || $type== 'update'){
			$pattern = '/^(\d{16}|\d{19})$/';
			if (!preg_match($pattern, $params['bankid'])) {
				$response = array(
						'fail' => 1002,
						'mess' => '无效的银行卡�&#65533;'
						);
				$response = (Object)$response;
				echo json_encode($response);
				return ;
			}
		}
		
		
		$response = $this->request('user', 'bankcard/'.$type, $params);
		echo $response;
	}

	/**
	 * 验证银行卡号是否绑定
	 * @params userid, bankid,signature
	 * @return [json] [fail:0;mess:可以绑定;fail:1004;mess:卡已绑定]
	 */
	public function checkBank(){
		$params['bankid'] = $this->input->get_post('bankid');
		$params['bankid'] = str_replace(" ", "", $params['bankid']);

		$params['user'] = $this->input->get_post('userid');
		$params['signature'] = $this->input->get_post('signature');
		$response = $this->request('user', 'bankcard/lists', $params);
		$banks = json_decode($response, true);
		if (@$banks['Data']) {
			$banks = $banks['Data'];
			foreach($banks as $val){
				if ($val['number'] == $params['bankid']){
					$result['fail'] = 1004;
					$result['mess'] = '该卡已绑�&#65533;';
				} else {
					$result['fail'] = 0;
					$result['mess'] = '可以绑定';
				}
			}
		} else {
			$result['fail'] = 0;
			$result['mess'] = '该用户无绑卡信息';
		}

		echo json_encode($result);
	}

	/**
	 * 设置用户信息 /Api/setUserInfo
	 * POST
	 * @params userid, name, ID_number, birthday, sex(男：0，女:1), qq, email, avatar(头像), signature
	 * return [json] fail:0-成功; other-error_code; mess:提示信息
	 */
	public function setUserInfo(){
		$params['user'] = @$this->input->get_post('userid');

		if ($_POST) {
			$params = $this->input->post();
		} else {
			$params = $this->input->get();
		}

		if (@$params['userid']) {
			$params['user'] = @$params['userid'];
			unset($params['userid']);
		}
		
		$response = $this->request('user', 'info/set', $params);
		echo $response;
	}

	/**
	 * 修改手机�&#65533; /Api/changePhone
	 * @params userid, newphone, ,signature
	 * @return [json] [fail:0;mess:修改成功]
	 */
	public function changePhone(){
		$params['user'] = $this->input->get_post('userid');

		$params['newphone'] = $this->input->get_post('newphone');
		$params['signature'] = $this->input->get_post('signature');
		$response = $this->request('user', 'info/changephone', $params);
		return $response;
	}


	/**
	 * 增删改查用户地址 /Api/address/lists(add,update,delete)
	 * GET,POST
	 * @param userid, type(home or work)[user], province, city, address, GPS_lng(经度), GPS_lat(纬度),signature
	 * @return [json]       fail: 0-成功, other-error_code; mess:错误信息
	 */
	public function address($type){
		$types = array('lists', 'add', 'update', 'delete');
		if (!in_array($type, $types)) {
			$response = array(
				'fail' => 2,
				'mess' => '请求错误,操作只能是list, add, update, delete',
				);
			echo json_encode($response);
			return ;
		}

		if ($_POST) {
			$params = $this->input->post();
			if ($params['userid']) {
				unset($params['userid']);
			}
			$params['user'] =  $this->input->get_post('userid');
			$response = $this->request('user', 'address/'.$type, $params);
			echo $response;
			return ;
		} else {
			$params = $this->input->get();
			if ($params['userid']) {
				unset($params['userid']);
			}
			$params['user'] =  $this->input->get_post('userid');
			$response = $this->request('user', 'address/'.$type, $params);
			echo $response;
			return ;
		}
	}

	/**
	 * 批更�&#65533;
	 * @return [type] [description]
	 */
	public function batUpdate(){
		if ($_GET) {
			$params = $this->input->get();
		} else {
			$params = $this->input->post();
		}
		

		//同时遍历三个数组
		foreach ($params as $key => $val) {
			$param[$key] = explode(',', $val);
		}

		for ($i=0; $i < count($param['userid']); $i++) { 
			foreach ($param as $key => $val) {
				@$paramx[$key] = $val[$i];
			}

			$paramx['user'] = $paramx['userid'];
			unset($paramx['userid']);
			$response = $this->request('user', 'address/update', $paramx);
			$response = json_decode($response);
			if ($response->fail != 0) {
				echo json_encode($response);
				return ;
			}
		}

		echo json_encode($response);
	}


	/**
	 * 获取支持银行列表
	 * @return [type] [description]
	 */
	public function bankTypes(){
		$this->load->config('bankType');
		$bankTypes = $this->config->config['bankType'];

		$response['fail'] = 0;
		$response['Data'] = $bankTypes;

		echo json_encode($response);
	}


	/**
	 * 站内信列�&#65533;
	 * @param userid, limit="offset, pageSize", flag=0 �&#65533; 1(未读或已�&#65533;)
	 * @return [type] [description]
	 */
	public function messages(){
		$params['user'] = $this->input->get_post('userid');
		$params['limit'] = $this->input->get_post('limit');
		$params['flag'] = $this->input->get_post('flag');

		$params['limit'] = $params['limit'] ? $params['limit'] : '';
		$params['flag'] = $params['flag'] ? (int)$params['flag'] : '';
		if(!$params['limit']) unset($params['limit']);

		$response = $this->request('message', 'user/lists', $params);
		echo $response;
	}

	/**
	 * 站内信详细内�&#65533;
	 * @param userid, message_id
	 * @return [type] [description]
	 */
	public function mesRead(){
		$params['user'] = $this->input->get_post('userid');
		$params['message_id'] = $this->input->get_post('message_id');

		$response = $this->request('message', 'user/read', $params);
		echo $response;
	}

	/**
	 * 删除站内�&#65533;
	 * @param userid, message_id
	 * @return [type] [description]
	 */
	public function delMess(){
		$params['user'] = $this->input->get_post('userid');
		$params['message_id'] = $this->input->get_post('message_id');

		$response = $this->request('message', 'user/read', $params);
		echo $response;
	}

	/**
	 * 设置已读或未�&#65533;
	 * @param userid, message_id, flag(0未读, 1已读)
	 * @return [type] [description]
	 */
	public function setMess(){
		$params['user'] = $this->input->get_post('userid');
		$params['message_id'] = $this->input->get_post('message_id');
		$params['read'] = $this->input->get_post('flag');
		$response = $this->request('message', 'user/setread', $params);
		echo $response;
	}

	/**
	 * 写信
	 * @param userid, toid, flag(0未读, 1已读), subject(标题), content(内容)
	 * @return [type] [description]
	 */
	public function mesWrite(){
		$params['user'] = $this->input->get_post('userid');
		$params['to'] = $this->input->get_post('toid');
		$params['read'] = $this->input->get_post('flag');
		$params['subject'] = $this->input->get_post('subject');
		$params['content'] = $this->input->get_post('content');

		$response = $this->request('message', 'user/write', $params);
		echo $response;
	}		/**	 * 获取店铺id	 */	public function getShopid(){		$params['type'] = $this->input->get_post('type');		$params['machine'] = $this->input->get_post('machine');				$response = $this->request('dealer', 'shop/getshopid', $params);		echo $response;		}
}