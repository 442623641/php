<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//�û�������
class Xw11xuan5 extends Common_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->Model('lotteryUser_model');
		$this->load->Model('lotteryIssue_model');
	}
	/**
	 */
	public function index(){
		$this->load->view('index.html');
	}
	
	public function gcdt(){
		$this->load->view('gcdt.html');
	}
	
	public function hmdt(){
		$this->load->view('hmdt.html');
	}
	
	public function wdcp(){
		$this->load->view('wdcp.html');
	}
	
	public function ljtz_11x5(){
		$data['type'] = $this->input->post('cur_type');
		$data['nums'] = $this->input->post('cur_num');
		$this->load->view('syxw_rxw/syxw_qszx.html',$data);
	}
	
	public function tz_11x5(){
		$this->load->view('syxw_rxw/syxw_tz.html');
	}
	
	public function tzjl(){
		$this->load->view('tzjl.html');
	}
	
	public function hmjl(){
		$this->load->view('hmjl.html');
	}
	
	public function zhjl(){
		$this->load->view('zhjl.html');
	}
	
	public function fqdhm(){
		$this->load->view('fqdhm.html');
	}
	
	public function hmxq(){
		$this->load->view('hmdt_xq.html');
	}
	
	public function fqhm(){
		$data['lotteryID'] =  $this->input->post('lotteryID');
		//$data['totalPhases'] =  $this->input->post('totalPhases');
		$data['multiple'] =  $this->input->post('multiple');
		$data['totalmoney'] =  $this->input->post('totalmoney');
		$data['text'] =  $this->input->post('text');
		$data['currentIssue'] =  $this->input->post('cur_issue');
		$data['shop'] =  $this->input->post('shop');
		$this->load->view('fqhm.html',$data);
	}
}
?>
