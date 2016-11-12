<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//�û�������
class Home extends Common_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->Model('lotteryJoint_model');
	}
	/**
	 */
	public function index(){
		$this->load->view('index.html');
	}
	
	public function kjxx(){
		$this->load->view('kjxx.html');
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
	
	
	/**
	 * 合买详情
	 */
	public function hmxq($id){
		$launch=$this->lotteryJoint_model->get_lotteryJoint($id);
		$launch['text']=null;
		$this->userID = '10017';
		if($launch['userID']!=$this->userID){
			switch((int)$launch['publicType']){
				#立即公开
				case 0:
					$data = $this->lotteryJoint_model->get_lotteryJointEntity($id);
					$launch['text']=$data['text'];
					break;
					#跟单人公开
				case 1:
					$condition['userID']=$this->userID;
					$condition['lotteryJointID']=@$id;
					$count=$this->lotteryJoint_model->count_participants($condition);
					if($count>0){
						$data = $this->lotteryJoint_model->get_lotteryJointEntity($id);
						$launch['text']=$data['text'];
					}
					break;
				default:
					break;
			}
		}
		#本人发起
		else{
			$data = $this->lotteryJoint_model->get_lotteryJointEntity($id);
			$launch['text']=$data['text'];
		}
		$launch['text']=json_encode($launch['text'],true);
		$res['data'] = $launch;	
		//var_dump($res['data']);
		$this->load->view('hmdt_xq.html',$res);
	}
}
?>
