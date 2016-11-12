<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	error_reporting(0);
//用户购彩（自购）
class RunTicket extends Common_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Runticket_model');
		$this->load->library('CacheLock');
	}
	/**
	* 获取未出票信息
 	* @param  size=条目数，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function test(){
		while(true){			  
			$data=$this->getOrder;
			if(count($data)==0){
				exit;
			}
		}
	}
	public function getOrder(){
		
		//进程加锁
		$lock = new CacheLock('Runticket');
		$lock->lock();
		$isCallback=isset($_REQUEST['isCallback'])?(int)$_REQUEST['isCallback']:0;
		$size=isset($_REQUEST['size'])?$_REQUEST['size']:10;
		$whereStr='';
		$condition[0]=0;
		if(isset($_REQUEST['lotteryID'])){
			$condition['lotteryID']=$_REQUEST['lotteryID'];
			$whereStr=' and lotteryID='.(int)$_REQUEST['lotteryID'];
		}
		$data=$this->Runticket_model->get($condition,$size);
		if(count($data)<1){
			$this->retAcess($data,0);
		}
		$arrids=array();
		for($i=0;$i<count($data);$i++){
			$buytype=substr($data[$i]['ID'],-1);
			switch($buytype){
				case BUY_GENERAL:
					$arrids['lotteryUsers'].="'".$data[$i]['ID']."',";	
					break;
				case BUY_LAUNCH:
					$arrids['lotteryJoints'].="'".$data[$i]['ID']."',";	
					break;
				case BUY_CHASE_DETAIL:
					$arrids['lotteryChases'].="'".$data[$i]['ID']."',";	
					break;
				default:
					break;
			}
		}
		$state=$isCallback==0?1:10;
		// 未出票：0
		$this->Runticket_model->update($arrids,$state,0,$whereStr);
		$lock->unlock();
		for($i=0;$i<count($data);$i++){
			$data[i]['text']=json_decode($data[i]['text'],true);
		}
		$this->retAcess($data,0);
	}
	public function updateOrder(){	
		$ids=@json_decode($_REQUEST['ids'],true);
		if(empty($ids)){
			$data['rows']=0;
			$data['sysNow']=date('Y-m-d H:i:s',time());
			$this->retAcess($data,0);
		}
		$arrids=array();
		for($i=0;$i<count($data);$i++){
			$buytype=substr($data[$i]['ID'],-1);
			switch($buytype){
				case BUY_GENERAL:
					$arrids['lotteryUsers'].="'".$data[$i]['ID']."',";	
					break;
				case BUY_LAUNCH:
					(string)$arrids['lotteryJoints'].="'".$data[$i]['ID']."',";	
					break;
				case BUY_CHASE_DETAIL:
					(string)$arrids['lotteryChases'].="'".$data[$i]['ID']."',";	
					break;
				default:
					break;
			}
		}
		//出票中 10
		//等待开奖 2
		//$lock = new CacheLock('Runticket');	
		//进程加锁
		//$lock->lock();
		$data['rows']=$this->Runticket_model->update($arrids,2,10,$whereStr);
		$data['sysNow']=date('Y-m-d H:i:s',time());
		//$lock->unlock();
		$this->retAcess($data,0);
	}
}