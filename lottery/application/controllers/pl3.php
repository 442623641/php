<?php
class Pl3 extends Common_Controller{

public function __construct(){
		parent::__construct();
		$this->load->Model('lotteryIssue_model');
		
	}
	public function index(){
		$this->load->view('pls.html');
	}
	public function addbet(){
		$this->load->view('pls_tz.html');
	}
	private function getIssue(){
		
		
	}
}
	