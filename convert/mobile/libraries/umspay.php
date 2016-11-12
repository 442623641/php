<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 全民付接�&#65533;
 */

class Umspay{

	//const UMSPAY_BASE_URL = 'http://116.228.21.170/qmfweb'; // 测试地址;
	const UMSPAY_BASE_URL = 'https://mpos.quanminfu.com/QmfWeb/'; // 生产地址

	/**
	 * 渠道�&#65533;
	 *
	 * @var string
	 */
	private $channelId;

	/**
	 * 定制�&#65533;
	 *
	 * @var strings
	 */
	private $customizeId;

	public function __construct() {
		$this->channelId = 100039;
		$this->customizeId = 1139;
	}

	/**
	 * 签名数据
	 *
	 * @param string $data
	 *        	要签名的数据
	 * @param string $private
	 *        	私钥文件
	 * @return string 签名�&#65533;16进制数据
	 */
	public function sign($data, $private = 'private.pem') {
		$p = openssl_pkey_get_private ( file_get_contents ( $private ) );
		openssl_sign ( $data, $signature, $p );
		openssl_free_key ( $p );
		return bin2hex ( $signature );
	}

	/**
	 * 验签
	 *
	 * @param string $data
	 * @param string $sign
	 * @param string $public
	 *        	公钥文件//验签公钥文件应为全民捷付提供的公钥文�&#65533;
	 * @return bool 验签状�&#65533;
	 */
	public function verify($data, $sign, $public = 'public.pem') {
		//file_get_contents ( $public );
		$p = openssl_pkey_get_public ( file_get_contents ( $public ) );
		$verify = openssl_verify ( $data, hex2bin ( $sign ), $p );
		openssl_free_key ( $p );
		return $verify > 0;
	}

	/**
	 * 以post方式发送请�&#65533;
	 *
	 * @param array $data
	 *        	请求参数
	 * @param string $url
	 *        	请求地址
	 * @return array 请求结果
	 */
	function post($data, $url) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSLVERSION, 4 ); 
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		
		// 数据载体格式
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( array (
				'jsonString' => json_encode ( $data ) 
		) ) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$result = json_decode ( curl_exec ( $ch ), true );
		echo(curl_error($ch));
		curl_close ( $ch );
		return $result;
	}

	/**
	 * 全民付接�&#65533;
	 * @param $user
	 * @param $bizName
	 * @return mixed
	 */
	public function charge($user, $bizName){
		$private = __ROOT__ . '/qmRsaKey/rsa_private_key.pem';
		$public = __ROOT__ . '/qmRsaKey/rsa_public_key.pem';
		$data = array(
			'channelId' => $this->channelId,
			'customizeId' => $this->customizeId,
			'mobileNum' => $user,
			);

		$str = $query = implode('', $data); 
		$signature = $this->sign($str,  $private);

		$data['bizName'] = $bizName;
		$data['signature'] = $signature;
		
		$query = "channelId=$this->channelId&customizeId=$this->customizeId&mobileNum=$user&bizName=$bizName&signature=$signature";
		$url = self::UMSPAY_BASE_URL . '?' . $query;
		$valid = $this->verify($str, $signature, $public);
		$result = $url;
		if (!$valid) {
			$result =  'Invalid';
		}

		return $result;
	}

	/**
	 * 下单
	 *
	 * @param string $title
	 *        	订单标题
	 * @param number $amt
	 *        	订单金额,单位�&#65533;
	 * @param string $orderId
	 *        	订单id,由商户产�&#65533;
	 * @param string $type
	 *        	交易类型
	 * @param integer $exp
	 *        	过期时间
	 * @return mixed
	 */
	function order($title, $amt, $orderId, $exp, $type = 'NoticePay') {
		$time = time ();
		var_dump($title);
		$data = array (
				'TransCode' => '201201', // 订单�&#65533; const value
				'OrderTime' => date ( 'His', $time ), // 订单时间，date hhmmss
				'EffectiveTime' => strval ( $exp ? $exp - $time : 0 ), // 有效期，�&#65533;0则长期有�&#65533;
				'OrderDate' => date ( 'Ymd', $time ), // 订单日期，yyyymmdd
				'MerOrderId' => $orderId, // 商户订单�&#65533;
				'TransType' => $type, // 支付类型，固定为NoticePay
				'TransAmt' => strval ( $amt ), // 订单金额，单位为分，不可为非整数�&#65533;
				'MerId' => $this->merchantId, // 商户编号
				'MerTermId' => $this->termId, // 商户终端�&#65533;
				'NotifyUrl' => self::ORDER_CALLBACK_URL, // 支付成功后，银商后台通知回调地址，保证外网可以访问�&#65533;
				'Reserve' => '', // 银商备用字段，不用理会�&#65533;
				'OrderDesc' => $title, // 订单描述
				'MerSign' => '' 
				);

				// 签名
				$data ['MerSign'] .= $data ['OrderTime'];
				$data ['MerSign'] .= $data ['EffectiveTime'];
				$data ['MerSign'] .= $data ['OrderDate'];
				$data ['MerSign'] .= $data ['MerOrderId'];
				$data ['MerSign'] .= $data ['TransType'];
				$data ['MerSign'] .= $data ['TransAmt'];
				$data ['MerSign'] .= $data ['MerId'];
				$data ['MerSign'] .= $data ['MerTermId'];
				$data ['MerSign'] .= $data ['NotifyUrl'];
				$data ['MerSign'] .= $data ['OrderDesc'];
				$data ['MerSign'] = $this->sign ( $data ['MerSign'] );

				// 发送请�&#65533;
				$result = $this->post ( $data, self::ORDER_CREATE_URL );
				// var_dump($result);
				// var_dump($data);
				// 验签
				$verify = '';
				$verify .= $result ['MerOrderId'];
				$verify .= $result ['ChrCode'];
				$verify .= $result ['TransId'];
				$verify .= $result ['Reserve'];
				$verify .= $result ['RespCode'];
				$verify .= $result ['RespMsg'];

				if (! isset ( $result ['Signature'] ) || ! $this->verify ( $verify, $result ['Signature'] )) {
					// 验签失败
					return false; // '下单返回验签失败';
				}

				if (! isset ( $result ['RespCode'] ) || intval ( $result ['RespCode'] )) {
					// 下单失败 详细信息参�&#65533;$result['RespMsg']
					return false; // '下单失败';
				}

				// 业务逻辑处理
				// var_dump($result);

				// 组装客户端需要的参数返回给客户端 TransId|ChrCode|签名|商户�&#65533;
				// $retStr ='';
				// $retStr .= $result['TransId'];
				// $retStr .= '|';
				// $retStr .= $result['ChrCode'];
				// $retStr .= '|';
				// $retStr .= $this->sign($result['TransId']�&#65533;$result['ChrCode']);
				// $retStr .= '|';
				// $retStr .= $this->merchantId;
				// web支付参数组装

				$retStr .= 'tranId=' . $result ['TransId'];
				$retStr .= '&';
				$retStr .= 'ChrCode=' . $result ['ChrCode'];
				$retStr .= '&';
				$retStr .= 'MerSign=' . $this->sign ( $result ['TransId'] . $result ['ChrCode'] );
				$retStr .= '&';
				$retStr .= 'url=http://www.baidu.com'; // 支付成功后，用户点击返回商户页面时返回的就是该地址。请填写完整地址�&#65533;
				// 		$retStr = array(
				// 				'tranId'  => $result ['TransId'],
				// 				'ChrCode' => $result ['ChrCode'],
				// 				'MerSign' => $this->sign ( $result ['TransId'] . $result ['ChrCode'] ),
				// 				'url'     =>'http://www.baidu.com'
				// 		);
				return $retStr;
	}
	/**
	 * 订单查询
	 *
	 * @param string $title
	 *        	订单标题
	 * @param number $amt
	 *        	订单金额,单位�&#65533;
	 * @param string $orderId
	 *        	订单id,由商户产�&#65533;
	 * @param string $type
	 *        	交易类型
	 * @param integer $exp
	 *        	过期时间
	 * @return mixed 订单状�&#65533;0:新订�&#65533;, 1成功�&#65533; 2失败�&#65533;3支付�&#65533;
	 *
	 */
	function seek($transId, $merOrderId) {
		$time = time ();
		$data = array (
				'TransCode' => '201203', // 交易代码
				'ReqTime' => date ( 'YmdHis', $time ), // 请求时间
				'OrderDate' => date ( 'Ymd', $time ), // 订单日期
				'MerOrderId' => $merOrderId, // 商户订单�&#65533;
				'TransId' => $transId, // 交易流水
				'MerId' => $this->merchantId, // 商户�&#65533;
				'MerTermId' => $this->termId, // 终端�&#65533;
				'Reserve' => '',
				'MerSign' => '' 
				);

				// 签名
				$data ['MerSign'] .= $data ['ReqTime'];
				$data ['MerSign'] .= $data ['OrderDate'];
				$data ['MerSign'] .= $data ['MerOrderId'];
				$data ['MerSign'] .= $data ['TransId'];
				$data ['MerSign'] .= $data ['MerId'];
				$data ['MerSign'] .= $data ['MerTermId'];
				$data ['MerSign'] .= $data ['Reserve'];
				$data ['MerSign'] = $this->sign ( $data ['MerSign'] );

				// 发送请�&#65533;
				$result = $this->post ( $data, self::ORDER_QUERY_URL );
				var_dump ( $result );

				// 验签
				$verify = '';
				$verify .= $result ['OrderTime'];
				$verify .= $result ['OrderDate'];
				$verify .= $result ['MerOrderId'];
				$verify .= $result ['TransType'];

				$verify .= $result ['TransAmt'];
				$verify .= $result ['MerId'];
				$verify .= $result ['MerTermId'];
				$verify .= $result ['TransId'];
				$verify .= $result ['TransState'];

				$verify .= $result ['RefId'];
				$verify .= $result ['Reserve'];
				$verify .= $result ['RespCode'];
				$verify .= $result ['RespMsg'];

				if (! isset ( $result ['Signature'] ) || ! $this->verify ( $verify, $result ['Signature'] )) {
					// 验签失败
					return false; // '订单查询返回验签失败';
				}

				if (! isset ( $result ['RespCode'] ) || intval ( $result ['RespCode'] )) {
					// 订单查询失败 详细信息参�&#65533;$result['RespMsg']
					return false; // '订单查询失败';
				}

				// 商户后台业务逻辑处理

				return true;
	}

	/**
	 * 响应回调
	 */
	function notify() {
		$data = &$_POST;
		// 验签
		$verify = '';
		$verify .= isset ( $data ['OrderTime'] ) ? $data ['OrderTime'] : '';
		$verify .= isset ( $data ['OrderDate'] ) ? $data ['OrderDate'] : '';
		$verify .= isset ( $data ['MerOrderId'] ) ? $data ['MerOrderId'] : '';
		$verify .= isset ( $data ['TransType'] ) ? $data ['TransType'] : '';
		$verify .= isset ( $data ['TransAmt'] ) ? $data ['TransAmt'] : '';
		$verify .= isset ( $data ['MerId'] ) ? $data ['MerId'] : '';
		$verify .= isset ( $data ['MerTermId'] ) ? $data ['MerTermId'] : '';
		$verify .= isset ( $data ['TransId'] ) ? $data ['TransId'] : '';
		$verify .= isset ( $data ['TransState'] ) ? $data ['TransState'] : '';
		$verify .= isset ( $data ['RefId'] ) ? $data ['RefId'] : '';
		$verify .= isset ( $data ['Account'] ) ? $data ['Account'] : '';
		$verify .= isset ( $data ['TransDesc'] ) ? $data ['TransDesc'] : '';
		$verify .= isset ( $data ['Reserve'] ) ? $data ['Reserve'] : '';

		if (! $this->verify ( $verify, $data ['Signature'] )) {
			// 验签失败!
			return false; // '查询返回报文验签失败';
		}

		// 根据MerOrderId得到本地订单

		// 商户后台做一些业务处�&#65533;

		// 响应数据
		$result = array (
				'TransCode' => '201202',
				'MerOrderId' => $data ['MerOrderId'],
				'TransType' => 'NoticePay',
				'MerId' => $data ['MerId'],
				'MerTermId' => $data ['MerTermId'],
				'TransId' => $data ['TransId'],
				'MerPlatTime' => date ( 'YmdHis' ),
				'MerOrderState' => '11',
				'Reserve' => '',
				'MerSign' => '' 
				);

				$result ['MerOrderState'] = '00';

				// 签名
				$result ['MerSign'] .= isset ( $result ['MerOrderId'] ) ? $result ['MerOrderId'] : '';
				$result ['MerSign'] .= isset ( $result ['TransType'] ) ? $result ['TransType'] : '';
				$result ['MerSign'] .= isset ( $result ['MerId'] ) ? $result ['MerId'] : '';
				$result ['MerSign'] .= isset ( $result ['MerTermId'] ) ? $result ['MerTermId'] : '';
				$result ['MerSign'] .= isset ( $result ['TransId'] ) ? $result ['TransId'] : '';
				$result ['MerSign'] .= isset ( $result ['MerPlatTime'] ) ? $result ['MerPlatTime'] : '';
				$result ['MerSign'] .= isset ( $result ['MerOrderState'] ) ? $result ['MerOrderState'] : '';
				$result ['MerSign'] .= isset ( $result ['Reserve'] ) ? $result ['Reserve'] : '';

				$result ['MerSign'] = $this->sign ( $result ['MerSign'] );

				// 响应回调
				exit ( json_encode ( $result ) );
	}
}
?>