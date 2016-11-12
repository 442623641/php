<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩（自购）
class JointChase extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotteryjoint_model');
		$this->load->Model('Lotteryissue_model');
	}
	/**
	 * 发起合买追号
	 * @param  issue=期号
	 * @param  multiple=倍数，
	 * @param  lottteryID=彩种，
	 * @param  text=彩票内容，格式参考{"彩票投注":["彩票类型","号码","数量","金额","倍数"],"追号":["期号","倍数"],"追号期数":"100"}
	 * {"TouZhu":[["509","0,0,0","1","2"，"1"],["509","0,1,9","1","2","1"]],"BeiShu":[["2014250","1"],["2014251","2"]],"Count":"2"}
	 * @param  shop=店铺，
	 * @param  amount=金额，
	 * @param  memo=描述，
	 * @param  issues=追号期号集合，
	 * @param  deviceID=, 0:android,1:IOS,2:phoneweb,3:web,4:终端机，
	 * @return fail:0-成功; other-error_code; mess:提示信息.
	 */
	public function launch(){
	#校验参数
		$sort_issues=json_decode(@$_REQUEST['issues'],true);
		sort($sort_issues);
		$totalMultiple=Extension::totalMultiple($sort_issues);
		#校验参数投注号码,金额
		$this->valid($totalMultiple);
		#初始化订单号,当前期号
		$chaseID=date("Ymdhism").$_REQUEST['lotteryID'].'1';//.$this->userID;
		#冻结用户金额
		$pending_id;
		$this->pending($chaseID,$pending_id);
		$current=$this->Lotteryissue_model->getIssue($_REQUEST['lotteryID']);
		$currentIssue=$current['issue']==$sort_issues[0][0]?$sort_issues[0][0]:0;
		if($current['issue']>$sort_issues[0][0]){
			$this->retFailed('追号中包含过期期号');
		}	
			$data=array('ID'=>$addID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'issues'=>$_REQUEST['issues'],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'state'=>(int)$_REQUEST['money']>$_REQUEST['launcherBuy']?8:1,
					'multiple'=>$_REQUEST['multiple'],
					'memo'=>@$_REQUEST['memo'],
					'launcher'=>$this->user,
					'schemeName'=>$_REQUEST['schemeName'],
					'schemeVal'=>@$_REQUEST['schemeVal'],
					'ensure'=>$_REQUEST['ensure'],
					'take'=>$_REQUEST['take'],
					'unit'=>isset($_REQUEST['unit'])?$_REQUEST['unit']:1,
					'financeID'=>$pending_id,
					'publicType'=>$_REQUEST['publicType'],
					'money'=>$_REQUEST['money'],
					'total'=>$totalMultiple*$_REQUEST['money']/$sort_issues[0][1],
					'sell'=>$_REQUEST['launcherBuy'],
					'launcherBuy'=>$_REQUEST['launcherBuy'],
					'stopRaiseTime'=>$current['stopTime'],
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('y-m-d h:i:s',time()),
					'updateDate'=>date('y-m-d h:i:s',time())					
		);
		if (!$this->lotteryChase_model->add_chaseUser($chaseData)){
			$this->retFailed('追号失败');
		}
		//当前期下单入库		
		if($current['issue']==$sort_issues[0][0]){	
			$firstID=date("YmdHism").$_REQUEST['lotteryID'];
			$data=array('ID'=>$firstID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'issue'=>$sort_issues[0][0],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'state'=>(int)$_REQUEST['money']>$_REQUEST['launcherBuy']?8:1,
					'multiple'=>$sort_issues[0][1],
					'memo'=>@$_REQUEST['memo'],
					'launcher'=>$this->user,
					'schemeName'=>$_REQUEST['schemeName'],
					'schemeVal'=>@$_REQUEST['schemeVal'],
					'ensure'=>$_REQUEST['ensure'],
					'take'=>$_REQUEST['take'],
					'unit'=>isset($_REQUEST['unit'])?$_REQUEST['unit']:1,
					'financeID'=>$pending_id,
					'publicType'=>$_REQUEST['publicType'],
					'money'=>$_REQUEST['money'],
					'sell'=>$_REQUEST['launcherBuy'],
					'launcherBuy'=>($_REQUEST['launcherBuy']/$_REQUEST['multiple'])*$sort_issues[0][1],
					'stopRaiseTime'=>$current['stopTime'],
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('y-m-d H:i:s',time()),
					'updateDate'=>date('y-m-d H:i:s',time()));
		$dataEntity=array(
					'ID'=>$addID,
					'lotteryJointID'=>$firstID,		
					'money'=>$_REQUEST['money'],
					'text'=>$_REQUEST['text'],
					'entityID'=>0,
					'state'=>0,
					'createDate'=>date('y-m-d H:i:s',time()));
		#入库
		if (!$this->Lotteryjoint_model->add_lotteryJoint($data,$dataEntity)){
			$this->retFailed('下单失败');
		}
		}
		$this->retAcess($data,0);
	}
	private function launch_valid(){
		$params=array('money'=>'金额','issue'=>'期号',
		'lotteryID'=>'彩种','multiple'=>'倍数',
		'text'=>'投注内容','publicType'=>'公开类型',
		'schemeName'=>'合买主题',
		'ensure'=>'保底金额','take'=>'提成比例',
		'launcherBuy'=>'发起人购买');
		Extension::params_valid($params);
		if($_REQUEST['money']<10){
			$this->retFailed('发起总量至少10元');
		}
		if($_REQUEST['launcherBuy']<6){
			$this->retFailed('发起人至少购买6元');
		}
		$count=Extension::getBetCount($_REQUEST['lotteryID'], $_REQUEST['text']);
		if($count*(int)$_REQUEST['multiple']*2!=$_REQUEST['money']){
			$this->retFailed('金额有误');
		}
		return true;
	}
	private function valid($totalMultiple){
			$params=array('money'=>'金额','issues'=>'期号','lotteryID'=>'彩种','multiple'=>'倍数',
			'text'=>'投注','publicType'=>'公开类型',
			'schemeName'=>'合买主题',
			'ensure'=>'保底金额','take'=>'提成比例',
			'launcherBuy'=>'发起人购买');
			//Extension::params_valid($params);
			$count=Extension::getBetCount($_REQUEST['lotteryID'], $_REQUEST['text']);
			if($count*(int)$_REQUEST['multiple']*$totalMultiple*2!=$_REQUEST['money']){
				$this->retFailed('金额有误');
			}
			return true;
	}
	private function buyIn_valid(){
		$params=array('money'=>'金额','id'=>'合买编号','shop'=>'店铺');
		Extension::params_valid($params);
		if($_REQUEST['money']<1){
			$this->retFailed('至少购买1元');
		}
		return true;
	}
	/**
	 * 可参与合买记录
	 * @param  issue=期号
	 */
	function buyable(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$data=$this->Lotteryjoint_model->lotteryJointOfLaunchs($num,$offset);
		return $this->retAcess($data,0);
	}
	/**
	 * 我发起的合买记录列表
	 * @param  state=状态
	 */
	function launchs(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['userID']=$this->userID;
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];
		}
		$data=$this->Lotteryjoint_model->get_lotteryJoints($condition,$num,$offset);
		return $this->retAcess($data,0);
	}
	/**
	 * 我参与的合买记录列表
	 * @param  state=状态
	 */
	function myBuyInes(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['userID']=$this->userID;
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];
		}
		$data=$this->Lotteryjoint_model->lotteryParticipantses($condition,$num,$offset);
		return $this->retAcess($data,0);
	}
	/**
	 * 发起合买记录详情
	 * @param  state=状态
	 */
	function launchDetail(){
		$condition['userID']=$this->userID;
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];
		}
		$data=$this->Lotteryjoint_model->lotteryLaunchDetail($condition,$num,$offset);
		return $this->retAcess($data,0);
	}
	/**
	 * 投注方案
	 */
	public function text(){
		$launch=$this->Lotteryjoint_model->get_lotteryJoint($_REQUEST['id']);
		$launch['text']=null;
		if($launch['userID']!=$this->userID){
			switch((int)$launch['publicType']){
				#立即公开
				case 0:
					$data = $this->Lotteryjoint_model->get_lotteryJointEntity($_REQUEST['id']);
					$launch['text']=$data['text'];
					break;
					#跟单人公开
				case 1:
					$condition['userID']=$this->userID;
					$condition['lotteryJointID']=@$_REQUEST['id'];
					$count=$this->Lotteryjoint_model->count_participants($condition);
					if($count>0){
						$data = $this->Lotteryjoint_model->get_lotteryJointEntity($_REQUEST['id']);
						$launch['text']=$data['text'];
					}
					break;
				default:
					break;
			}
		}
		#本人发起
		else{
			$data = $this->Lotteryjoint_model->get_lotteryJointEntity($_REQUEST['id']);
			$launch['text']=$data['text'];
		}
		$this->retAcess($launch,0);
	}
}