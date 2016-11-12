<?php defined('BASEPATH') or die('Access Denied');

/**
 *用户中心 
 */
class UserCenter extends User_Controller
{	
	public function __construct(){
		parent::__construct();
		$data['user'] = $this->user;
		$data['avatar'] = $this->get_session('avatar');
		$data['name'] = $this->get_session('name');
		$data['last_login'] = $this->get_session('last_login');
		$this->data['header'] = $this->load->view('UserCenter/header.html', $data, true);
		$this->data['footer'] = $this->load->view('UserCenter/footer.html','', true);
	}

	/**
	 * 个人首页
	 * @return [type] [description]
	 */
	public function index(){
		$this->load->view('UserCenter/index.html', $this->data);
	}

	public function indexSy(){
		$this->load->view('UserCenter/index_sy.html', $this->data);
	}

	/**
	 * 交易记录
	 */
	public function charge(){
		$this->load->view('UserCenter/charge.html', $this->data);
	}

	/**
	 * 我的服务
	 */
	public function server(){
		$this->load->view('UserCenter/server.html', $this->data);
	}

	/**
	 * 账户中心
	 */
	public function account(){
		$data['userid'] = $this->userid;
		$data['header'] = $this->data['header'];
		$data['footer'] = $this->data['footer'];
		$this->load->view('UserCenter/account.html', $data);
	}

	//充值
	public function chongzhi(){
		$this->load->view("UserCenter/chongzhi.html");
	}

	//提现
	public function tixian(){
		$this->load->view("UserCenter/tixian.html");
	}

	//实名认证
	public function renzheng(){
		$params['userid'] = $this->userid;
		$params['signature'] = $this->input->get_post('signature');
		$userInfo = $this->request('Api', 'userInfo', $params);
		$data['userInfo'] = $userInfo['Data'][0];
		$this->load->view("UserCenter/renzheng.html", $data);
	}

	//个人资料
	public function personInfo(){
		$params['userid'] = $this->userid;
		$params['lastcode'] = $this->code;
		$data['userInfo'] = $this->request('Api', 'userInfo', $params);
		$data['userInfo'] = $data['userInfo']['Data'][0];
		$this->load->view("UserCenter/personinfo.html", $data);
	}

	//支付设置
	public function paySet(){
		$params['userid'] = $this->userid;
		$params['signature'] = $this->input->get_post('signature');

		$payInfo = $this->request('AccountApi', 'getPayConf', $params);
		if ($payInfo['fail'] == 0) {
			$data['pay'] = $payInfo['data'];
		}
		$this->load->view("UserCenter/payset.html", $data);
	}

	//修改密码
	public function passMod(){
		$this->load->view("UserCenter/passmod.html", $this->data);
	}

	//银行卡页面
	public function bankBind(){
		$params['userid'] = $this->userid;
		$result = $this->request('Api', 'userBankInfo/lists', $params);

		if (@$result['Data']) {
			$data['banks'] = $result['Data'];
		} else {
			$data['banks'] = $result['mess'];
		}
		$this->load->view("UserCenter/bindbank.html", $data);
	}

	//地址管理
	public function address(){
		$data['user'] = $this->user;
		$this->load->view('UserCenter/address.html', $data);
	}

	//银行卡解绑
	public function delBank(){
		if ($_POST) {
			$params['userid'] = $this->userid;
			$params['paypass'] = $this->input->get_post('paypass');
			$result = $this->request('AccountApi', 'checkPay', $params);
			if ($result['fail'] == 0){
				$params['bankid'] = $this->input->get_post('bankid');
				$result = $this->request('Api', 'userBankInfo/delete', $params);
				echo json_encode($result);
				return ;
			} else {
				echo json_encode($result);
				return ;
			}
		}
		$data['bankid'] = $this->input->get('bankid');
		$this->load->view('UserCenter/delbank.html', $data);
	}

	//绑定银行卡
	public function addBank(){
		$this->config->load('banklists');
		$this->load->helper('banklocation.php');
		$bankLists = $this->config->config['banklists'];
		$params['userid'] = $this->userid;
		$params['bankid'] = $this->input->get_post('bankid');
		$params['bankid'] = str_replace ( " ", "", $params['bankid']);
		$params['bankname'] = bankInfo($params['bankid'], $bankLists);
		$params['branch'] = $this->input->get_post('branch');
		$params['lastcode'] = $this->code;

		$result = $this->request('Api', 'userBankInfo/add', $params);
		echo json_encode($result);
	}

	//银行卡类型
	public function bankType(){
		$this->config->load('banklists');
		$this->load->helper('Banklocation.php');
		$bankLists = $this->config->config['banklists'];
		$params['bankid'] = $this->input->get_post('bankid');
		$params['bankid'] = str_replace ( " ", "", $params['bankid']);
		$bankname = bankInfo($params['bankid'], $bankLists);
		echo $bankname;
	}

	/**
	 * 用户信息
	 */
	public function userInfo(){
		$params['userid'] = $this->userid;
		$params['lastcode'] = $this->code;
		$data['userInfo'] = $this->request('Api', 'userInfo', $params);
		$data['userInfo'] = $data['userInfo']['Data'][0];

		$params['lastcode'] = MD5($this->code.$params['userid']);
		$data['banklist'] = $this->request('Api', 'userBankInfo', $params);
		$data['banklist'] = $data['banklist']['Data'];
		$data['banknum'] = count($data['banklist']);
		$data['account'] = $this->request('AccountApi', 'userMoney/balance', $params);
		$data['avatar'] = $this->get_session('avatar');
		$this->load->view('UserCenter/acc_info.html', $data);
	}

	//添加地址页面
	public function addAddr(){
		$data['user'] = $this->user;
		$this->load->view('UserCenter/addAddr.html', $data);
	}

	//测试接口
	public function api(){
		$param['user'] = '18258117622';
		$url = $this->domain . 'user/getInfo';
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
	    $response = curl_exec($ch);

	    if($error=curl_error($ch)){
	        die($error);
	    }

	    curl_close($ch);

		var_dump($response);
		//echo $url;
	}

}
