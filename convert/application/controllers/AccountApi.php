<?php defined('BASEPATH') or die('Access Denied');

#--------------------------------------------------
# 财务接口
# AccountApi
# -------------------------------------------------
# yulei <464001224@qq.com>
# 2015-6-30
# -------------------------------------------------

class AccountApi extends API_Controller
{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 交易记录  /AccountApi/charges
	 * @params: user,lastcode
	 * @return [json] [fail:0; receive:[]; send:[]]
	 */
	public function charges(){
		$params['user'] = $this->input->get_post('userid');
		$params['lastcode'] = $this->input->get_post('lastcode');
		$params['type'] = $this->input->get_post('type');
		$params['start'] = $this->input->get_post('start');
		$params['end'] = $this->input->get_post('end');
		$params['limit'] = $this->input->get_post('limit');

		$response = $this->request('money', 'running/user', $params);

		echo $response;
	} 	
    protected function credit(){
        $params['user'] = $this->input->get_post('user');

        $response = $this->request('credit', 'sign/signRecord', $params);
        echo $response;
    }
    protected function creditList(){
        $params['user'] = $this->input->get_post('user');
        $params['type'] = $this->input->get_post('type');
        $params['start'] = $this->input->get_post('start');
        $params['end'] = $this->input->get_post('end');
        $params['limit'] = $this->input->get_post('limit');

        $response = $this->request('money', 'credit/lists', $params);
        echo $response;
    }    	//积分购买
    protected function creditBuy(){
        $params['user'] = $this->input->get_post('user');
        $params['money'] = $this->input->get_post('money');
        $params['credit'] = $this->input->get_post('credit');
        $params['comment'] = $this->input->get_post('comment');
        $response = $this->request('credit', 'buy/buyRecord', $params);
        echo $response;
    }
    protected function creditPro(){
        //$params['param'] = "default-credit-rate";

        //$response = $this->request('money', 'hr/config', $params);
        $response = $this->request('credit', 'buy/getRule', '');
        echo $response;
    }

	/**
	 * 用户账户信息接口	/AccountApi/userMoney/balance(deposit, withdraw, transfer)
	 * @param  [type] $type [充�&#65533;-deposit,提现withdraw,转账-transfer,余额-balance]
	 * @return callback_func
	 */
	public function userMoney($type){
		$types = array('balance', 'deposit', 'withdraw', 'transfer','credit','creditList','creditBuy','creditPro');
		if (!in_array($type, $types)) {
			$response = array(
				'fail' => 3,
				'mess' => '无效的账户操�&#65533;',
				);
			echo json_encode($response);
			return ;
		}
		$this->$type();
	}

	/**
	 * 余额 /AccountApi/userMoney/balance
	 * @params userid, lastcode
	 * @return json money-余额, credit-积分
	 */
	protected function balance(){
		$params['user'] = $this->input->get_post('userid');
		$params['lastcode'] = $this->input->get_post('lastcode');

		$response = $this->request('money', 'user/balance', $params);
		echo $response;
	}

	/**
	 * 用户充�&#65533;  /AccountApi/userMoney/deposit
	 * @params:userid, amount, comment(备注),order_id=流水�&#65533;&#65533;
	 * @return [json] [fail:0;money:余额; pending:冻结中的�&#65533;&#65533;;credit:积分]
	 */
	protected function deposit(){
		$params['user'] = $this->input->get_post('userid');
		$params['amount'] = $this->input->get_post('amount');
		$params['comment'] = $this->input->get_post('comment');
		$params['order_id'] = $this->input->get_post('order_id');
		$response = $this->request('money', 'user/deposit', $params);
		echo $response;
	}

	/**
	 * 提现申请	/AccountApi/userMoney/withdraw
	 * GET, POST
	 * @params userid, amount(金额), comment(转账方式和账�&#65533;&#65533;:taobao:13980278934)
	 * @return [json] [fail:0; pending_id:流水�&#65533;&#65533;; amount:金额]
	 */
	protected function withdraw(){
		$params['user'] = $this->input->get_post('userid');
		$params['amount'] = $this->input->get_post('amount');
		$params['comment'] = $this->input->get_post('comment');

		$response = $this->request('money', 'user/withdraw', $params);
		echo $response;
	}

	/**
	 * 用户转账	/AccountApi/userMoney/transfer
	 * GET,POST
	 * @params userid(转账�&#65533;&#65533;), toid(收款�&#65533;&#65533;), amount, comment(备注), lastcode(使用code和前面的输入参数做MD5,暂不考虑)
	 * @return [json] [fail:0;amount;UserFrom:付款�&#65533;&#65533;;UserTo收款�&#65533;&#65533;;running_id:流水号]
	 */
	protected function transfer(){
		$params['user'] = $this->input->get_post('userid');
		$params['to'] = $this->input->get_post('toid');
		$params['amount'] = $this->input->get_post('amount');
		$params['comment'] = $this->input->get_post('comment');
		$params['lastcode'] = $this->input->get_post('lastcode');

		$response = $this->request('money', 'user/transfer', $params);
		echo $response;
	}
	//money/record/withdraw?user=10022&limit=3 
	public function withDrawing(){
		$params['user'] = $this->input->get_post('userid');
		$params['limit'] = $this->input->get_post('limit');

		$response = $this->request('money', 'record/withdraw', $params);
		echo $response;
	}


	/**
	 * 正在交易列表 /AccountApi/pending/user(shop, hr)
	 * @param  [string] $type [user,shop,hr]
	 * @return [json]       [fail:0;data:列表]
	 */
	public function pending($type){
		$types = array('user', 'shop', 'hr');
		if (!in_array($type, $types)) {
			$response = array(
				'fail' => 3,
				'mess' => '不存在该用户',
				);
			echo json_encode($response);
			return ;
		}
		$type = $type."Pending";
		$this->$type();
	}

	/**
	 * 用户正在交易 user
	 * @params: userid, lastcode
	 * @return[json]       [fail:0;data:列表]
	 */
	protected function userPending(){
		$params['user'] = $this->input->get_post('userid');
		$params['limit'] = $this->input->get_post('limit');
		$params['lastcode'] = $this->input->get_post('lastcode');				$params['prepared'] = @$this->input->get_post('prepared');
		$response = $this->request('money', 'user/pending', $params);

		echo $response;
	}

	/**
	 * 商店正在交易 shop
	 * @params: shopid, lastcode
	 * @return[json]       [fail:0;data:列表]
	 */
	protected function shopPending(){
		$params['shop'] = $this->input->get_post('shopid');
		$params['lastcode'] = $this->input->get_post('lastcode');				$params['prepared'] = @$this->input->get_post('prepared');		$params['prepared'] = @$this->input->get_post('prepared');		
		$response = $this->request('money', 'shop/pending', $params);

		echo $response;
	}

	/**
	 * 总公司冻结记�&#65533;&#65533; hr
	 * @params: hrid, lastcode
	 * @return[json]       [fail:0;data:列表]
	 */
	protected function hrPending(){
		$params['shop'] = $this->input->get_post('hrid');
		$params['lastcode'] = $this->input->get_post('lastcode');
		$response = $this->request('money', 'hr/pending', $params);

		echo $response;
	}

	/**
	 * 验证支付密码  /AccountApi/checkPay
	 * @params userid, paypass
	 * @return [json] [fail,mess, data]
	 */
	public function checkPay(){
		$params['user'] = $this->input->get_post('userid');
		$params['paypass'] = $this->input->get_post('paypass');

		$response = $this->request('money', 'config/checkpass', $params);
		echo $response;
	}

	/**
	 * 设置支付密码  /AccountApi/setPayPass
	 * @params userid, paypass
	 * @return [json] [fail, mess, data]
	 */
	public function setPayPass(){
		$params['user'] = $this->input->get_post('userid');
		$params['paypass'] = $this->input->get_post('paypass');
		$response = $this->request('money', 'config/set', $params);
		echo $response;
	}

	/**
	 * 修改支付密码 /AccountApi/changePayPass
	 * @params userid, pass, newpass
	 * @return [json] [fail:0;mess:修改成功]
	 */
	public function changePayPass(){
		$params['user'] = $this->input->get_post('userid');
		$response = $this->request('money', 'config/get', $params);
		$response = json_decode($response, true);
		if ($response['fail'] == 0) {
			$pass = $this->input->get_post('pass');
			if ($pass == $response['data']['pay_pass']) {
				$params['paypass'] = $this->input->get_post('newpass');
				$response = $this->request('money', 'config/set', $params);
				echo $response;
			} else {
				$response = array(
					'fail' => '1004',
					'mess' => '原始密码错误',
					);
				echo json_encode($response);
				return ;
			}
		} else {
			$response = array(
				'fail' => 1002,
				'mess' => '修改错误，稍后重�&#65533;',
				);
			echo json_encode($response);
			return ;
		}
	}

	/**
	 * 获取用户财务相关设置 /AccountApi/getPayConf
	 * @params userid
	 * @return [json] [fail:0;data:{
	 * user:用户ID; pay_pass; min_pay:最小直接支付金�&#65533;&#65533;; 
	 * method_huge:大额支付方式(0-短信;1-支付密码); 
	 * daily_limit:每日限额;daily_amount:当日已经限额支付金额;
	 * today: 今天日期
	 * }]
	 */
	public function getPayConf(){
		$params['user'] = $this->input->get_post('userid');
		$response = $this->request('money', 'config/get', $params);
		echo $response;
	}
}