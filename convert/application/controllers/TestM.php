<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//http://api.cxiang.net/money/running/user?user=13993001010&type=1&start=2015-06-16&end=2015-06-19
#--------------------------------------------------------------
# 用户操作接口
# Api
#--------------------------------------------------------------
# yulei <464001224@qq.com>
# 2015-6-30
#--------------------------------------------------------------

class TestM extends API_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		//$this->load->library('MY_session');
	}
	public function test(){
		$this->mc->set($key, time());
        echo $this->mc->get( $key );
		//连接Memcache  
		$mem = new Memcache;  
		$mem->connect("192.168.10.18", 11211);  
		//保存数据  
		$mem->set('key1', 'This is first value', 0, 60);  
		$val = $mem->get('key1');  
		echo "Get key1 value: " . $val ."<br>";  
		//替换数据  
		$mem->replace('key1', 'This is replace value', 0, 60);  
		$val = $mem->get('key1');  
		echo "Get key1 value: " . $val . "<br>";  
		//保存数组数据  
		$arr = array('aaa', 'bbb', 'ccc', 'ddd');  
		$mem->set('key2', $arr, 0, 60);  
		$val2 = $mem->get('key2');  
		echo "Get key2 value: ";  
		print_r($val2);  
		echo "<br>";  
		//删除数据  
		$mem->delete('key1');  
		$val = $mem->get('key1');  
		echo "Get key1 value: " . $val . "<br>";  
		//清除所有数据  
		$mem->flush();  
		$val2 = $mem->get('key2');  
		echo "Get key2 value: ";  
		print_r($val2);  
		echo "<br>";  
		//关闭连接  
		$mem->close();  
		
	}
	public function testmc(){
		 $this->load->memcache();
		 echo var_dump(create_guid());
		//$key='test';
		//$this->mc->set($key, time());
        //echo $this->mc->get( $key );
		
	}
	function sign() {
		$prestr="body=用户充值&buyer_email=846269981@qq.com&buyer_id=2088212356218460&discount=0.00&gmt_create=2015-08-22 14:09:32&gmt_payment=2015-08-22 14:09:33&is_total_fee_adjust=N&notify_id=8aaa4eed2d5b5ed756df2a77650f71584k&notify_time=2015-08-22 14:09:34&notify_type=trade_status_sync&out_trade_no=201508220209176910022&payment_type=1&price=0.01&quantity=1&seller_email=2712397479@qq.com&seller_id=2088511891258053&subject=测试商品&total_fee=0.01&trade_no=2015082200001000460060851324&trade_status=TRADE_SUCCESS&use_coupon=N";
		$sign="YXvg+X9ZpS5y56UmzWMlJgLV9un8FbuvciBkWuZkEUrMomOtsNQeHoKUZ0MOo3z34/33FeJlN/MQUaYCamlo7xvoCBNXaVBTHO6dGMD0v5YfNxMI2TMnEAW7KwYsH/YJ92//VVwEJmcn150D608IHjie8RasUGlC+333t6Qb5n4=";
		$sign = base64_decode($sign);
		//$public_key= file_get_contents('rsa_public_key.pem');
		$public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRA
FljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQE
B/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5Ksi
NG9zpgmLCUYuLkxpLQIDAQAB
-----END PUBLIC KEY-----';
		$pkeyid = openssl_get_publickey($public_key);
		print_r($pkeyid);
		if ($pkeyid) {
			$verify = openssl_verify($prestr, $sign, $pkeyid);
			echo 22;
			print_r($verify);
			openssl_free_key($pkeyid);
		}
		if($verify == 1){
			return true;
		}else{
			return false;
		}
}

}