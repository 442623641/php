<?php defined('BASEPATH') or exit('Access Denied!');

//快递查询
class Express extends Common_Controller {
	const EXP_API = 'http://www.kuaidi100.com/';
	private $comp = array(
		'shentong' => '申通快递',
		'ems' => 'EMS',
		'shunfeng' => '顺丰速运',
		'yunda' => '韵达快递',
		'yuantong' => '圆通速递',
		'zhongtong' => '中通快递',
		'huitongkuaidi' => '百世汇通',
		'tiantian' => '天天快递',
		'zhaijisong' => '宅急送',
		'zhongyouwuliu' => '中邮物流',
		'ztky' => '中铁物流',
	);

	public function __construct() {
		parent::__construct();
		$this->load->switch_themes_on();
	}

	public function index() {
		$data['company'] = $this->comp;
		$this->load->view('express.html', $data);

	}

	public function result() {
		$data = $this->input->post();
		$company = $data['company'];
		$rand = mt_rand() / mt_getrandmax();
		$url = self::EXP_API . 'query?type=' . $data['company'] . '&postid=' . $data['number'] . '&id=1&valicode=&temp=' . $rand;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		if ($response) {
			$result = json_decode($response, true);
			if ($result['status'] == '200') {
				$data['response'] = $result['data'];
				$data['logo'] = $this->comp[$company];
				$this->load->view('result.html', $data);
			} else {
				echo '<script>alert("查询出错!");</script>';
				echo '<script>location.href="' . site_url('Express/index') . '"</script>';
			}

		} else {
			echo '<script>alert("查询出错,稍后重试!");</script>';
			echo '<script>location.href="' . site_url('Express/index') . '"</script>';
		}
	}
}