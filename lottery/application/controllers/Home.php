<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//�û�������
class Home extends Common_Controller{

	public function __construct(){
		parent::__construct();
	}
	/**
	 */
	public function index(){
		$this->load->view('404/index.html');
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
	
	public function ljtz_pls(){
		$this->load->view('pls.html');
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
	
}
?>
