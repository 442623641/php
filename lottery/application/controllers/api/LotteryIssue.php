<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//用户购彩（自购）
class LotteryIssue extends Common_Controller{
	public function __construct(){
		parent::__construct();	
		$this->load->Model('Lotteryissue_model');
		$this->load->Model('Lotteryrun_model');
	}
	/**
	* 用户购买彩票
 	* @param  lottteryID=彩种，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function currentIssue(){
		$params=array('lotteryID'=>'彩种');
		#校验参数投注号码,金额
		Extension::params_valid($params);
		for($i=0;$i<10;$i++){
			$data=$this->Lotteryissue_model->getIssue($_REQUEST['lotteryID']);
			if(count($data)>0){
				break;
			}else{
				usleep(100);
				continue;
			}
		}
		$data['now']=date('Y-m-d H:i:s',time());
		$this->retAcess($data,0);
	}
	/**
	* 彩票休市
 	* @param  lottteryID=彩种，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function lotteryStop(){
		$params=array('lotteryID'=>'彩种');
		#校验参数投注号码,金额
		Extension::params_valid($params);
		for($i=0;$i<10;$i++){
			$data=$this->Lotteryissue_model->getLotteryStop($_REQUEST['lotteryID']);
			if(count($data)>0){
				break;
			}else{
				usleep(100);
				continue;
			}
		}
		//$data['now']=date('Y-m-d H:i:s',time());
		$this->retAcess($data,0);
	}
	/**
	* 获取开奖信息
 	* @param  type=彩种，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function lotteryAll(){
		$params=array('type'=>'彩种');
		Extension::params_valid($params);
		$configlottery=config_item('lottery');
		if(!isset($configlottery[$_REQUEST['type']])){
			$this->retFailed('彩种类型字典错误');
		}
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$data = $this->Lotteryrun_model->get_lotteryRuns($configlottery[(int)$_REQUEST['type']]['lotteryrun_table'],$num,$offset);
		if(is_array($data)){
			$ret['rows']=$data;
			$ret['fail']=0;
			echo json_encode($ret);
			exit;	
		}else{
			$this->retFailed('暂无开奖信息！');
		}
	}
//	public function lotteryAll(){
//		//var_dump($_REQUEST);
//		$params=array('type'=>'彩种');
//		#校验参数投注号码,金额
//		Extension::params_valid($params);
//		$params['type'] =$_REQUEST['type'];
//		$params['begindate'] = @$_REQUEST['begindate'];
//		$params['enddate'] = @$_REQUEST['enddate'];
//		$params['order'] = @$_REQUEST['order'];
//		$params['size'] = @$_REQUEST['pageSize'];
//		$params['index'] = @$_REQUEST['index'];
//		$response=$this->requestOfurl(config_item("kj_url"),'Lottery.ashx',$params);
//		if(empty($response)){
//			$this->retFailed('网络延时,稍后再试');
//		}
//		//var_dump($response);
//		if($response['fail']!=0){
//			echo json_encode($response);
//			exit;
//		}
//		echo json_encode($response);
//		exit;
//	}
	/**
	* 上一期开奖号码
 	* @param  type=彩种，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function lotteryLast(){
		$params=array();
		if(isset($_REQUEST['type'])){
			$params['lotteryID'] = $_REQUEST['type'];
		}
		for($i=0;$i<10;$i++){
			$data = $this->Lotteryrun_model->get_lotteryRunOfLast($params);
			if(count($data)>0){
				break;
			}else{
				usleep(100);
				continue;
			}
		}
		if(is_array($data)){
			$ret['rows']=$data;
			$ret['fail']=0;
			echo json_encode($ret);
			exit;	
		}else{
			$this->retFailed('暂无开奖信息！');
		}
	}
	/**
	* 获取店铺信息
 	* @param  type=彩种，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function shopInfo(){
		$params=array();
		if(isset($_REQUEST['type'])){
			$params['lotteryID'] = $_REQUEST['type'];
		}
		for($i=0;$i<10;$i++){
			$data = $this->Lotteryrun_model->get_lotteryRunOfLast($params);
			if(count($data)>0){
				break;
			}else{
				usleep(100);
				continue;
			}
		}
		if(is_array($data)){
			$ret['rows']=$data;
			$ret['fail']=0;
			echo json_encode($ret);
			exit;	
		}else{
			$this->retFailed('暂无开奖信息！');
		}
	}
}