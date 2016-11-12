<?php
//小康应用控制器
class XkApp extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
	function index(){
		$this->load->view('umspay.html');
	}
	/**
	 * 以post方式发送请求请求
	 *
	 * @param array $data
	 *        	请求参数
	 * @param string $url
	 *        	请求地址
	 * @return array 请求结果
	 */
	//http://localhost:81/Web/index.php/umspay/umspay?user=15056999753
	function umspay($model) {
		$data['model']=$model;
		$this->load->view('umspay.html',$data);
	}
	function submit($model){
		$modelArray=array('water','gas','power','creditCard','phoneRecharge');
		$uniqueId=null;
		$mobileNum=null;
		if (!in_array($model,$modelArray))
		{
			exit();
		}
		$bizName=$model;
		$user = $this->session->userdata('user');
		if(empty($user['user'])){
			$uniqueId=time();
		}else{
			$mobileNum=$user['user'];
		}
		$signature=$this->sign('1000391139'.($mobileNum==null?$uniqueId:$mobileNum),APPPATH.'config/rsa_private_key.pem');
		$param=array(
		"url"=>'https://mpos.quanminfu.com/QmfWeb/',
		"channelId"=>100039,
		"customizeId"=>1139,
		"bizName"=>$model,
		"uniqueId"=>$uniqueId,
		"mobileNum"=>$mobileNum,
		"signature"=>$signature
		);
		echo $this->buildRequest($param);
	}
	 private function buildRequest($param){
		return	'<body style="height:500px;weight:900px;text-align:center;"><div  style="margin:200px -100px"><img src="'.base_url().'web/images/loading1.gif"></div>
				<form  style="display:none;" id="umspaysubmit" name="umspaysubmit" method="post" action="'.$param['url'].'">
				<input name="channelId" type="text" value="'.$param['channelId'].'" />
				<input name="customizeId" type="text" value="'.$param['customizeId'].'"/>
				<input name="mobileNum" type="text" value="'.$param['mobileNum'].'"/>
				<input name="uniqueId" type="text" value="'.$param['uniqueId'].'"/>
				<input name="bizName" type="text" value="'.$param['bizName'].'"/>
				<input name="signature" type="text" value="'.$param['signature'].'"/>
				</form>
				<script>document.forms["umspaysubmit"].submit();</script></body>';
	}
		/**
	 * 签名数据
	 *
	 * @param string $data
	 *        	要签名的数据
	 * @param string $private
	 *        	私钥文件
	 * @return string 签名的16进制数据
	 */
	private function sign($data, $private = 'private.pem') {
		$p = openssl_pkey_get_private ( file_get_contents ( $private ) );
		openssl_sign ( $data, $signature, $p );
		openssl_free_key ( $p );
		return bin2hex ( $signature );
	}
	
}
?>