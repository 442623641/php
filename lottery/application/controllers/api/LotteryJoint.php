<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩（自购）
class LotteryJoint extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('LotteryJoint_model');
		$this->load->Model('LotteryIssue_model');
	}
	/**
	 * 发起合买
	 * @param  issue=期号
	 * @param  multiple=倍数，
	 * @param  lottteryID=彩种，
	 * @param  text=彩票内容，格式参考{"彩票投注":["彩票类型","号码","数量","金额","倍数"],"追号":["期号","倍数"],"追号期数":"100"}
	 * {"TouZhu":[["509","0,0,0","1","2"，"1"],["509","0,1,9","1","2","1"]],"BeiShu":[["2014250","1"],["2014251","2"]],"Count":"2"}
	 * @param  shop=店铺，
	 * @param  amount=金额，
	 * @param  memo=描述，
	 * @param  deviceID=, 0:android,1:IOS,2:phoneweb,3:web,4:终端机，
	 * @return fail:0-成功; other-error_code; mess:提示信息
	 */
	public function launch(){
		$stopTime=null;
		$this->launch_valid($stopTime);
//		$current=$this->LotteryIssue_model->getIssue($_REQUEST['lotteryID']);
//		if($current['issue']!=$_REQUEST['issue']){
//			$this->retFailed('期号过期');
//		}
		#生成订单号
		$addID=$this->generalSeek(BUY_LAUNCH);//.$this->userID;	
		$pending_id;
		$this->pending($addID,$pending_id,$_REQUEST['issue'].'期发起合买');

		$data=array('ID'=>$addID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'issue'=>$_REQUEST['issue'],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'state'=>(int)$_REQUEST['money']>$_REQUEST['launcherBuy']?8:0,
					'multiple'=>$_REQUEST['multiple'],
					'memo'=>@$_REQUEST['memo'],
					'launcher'=>$_REQUEST['userName'],
					'schemeName'=>$_REQUEST['schemeName'],
					'schemeVal'=>@$_REQUEST['schemeVal'],
					'ensure'=>$_REQUEST['ensure'],
					'take'=>$_REQUEST['take'],
					'unit'=>isset($_REQUEST['unit'])?$_REQUEST['unit']:1,
					'financeID'=>$pending_id,
					'publicType'=>$_REQUEST['publicType'],
					'money'=>$_REQUEST['money'],
					'sell'=>$_REQUEST['launcherBuy'],
					'launcherBuy'=>$_REQUEST['launcherBuy'],
					'stopRaiseTime'=>$stopTime,
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time())					
		);
		$dataEntity=array(
					'ID'=>$addID,
					'lotteryJointID'=>$addID,		
					'money'=>$_REQUEST['money'],
					'text'=>$_REQUEST['text'],
					'entityID'=>0,
					'state'=>0,
					'createDate'=>date('Y-m-d H:i:s',time())
		);
		#入库
		if (!$this->LotteryJoint_model->add_lotteryJoint($data,$dataEntity)){
			$this->retFailed('下单失败');
		}
		//$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
	}
	/**
	 * 参与合买
	 * @param  issue=期号
	 * @param  multiple=倍数，
	 * @param  lottteryID=彩种，
	 * @param  text=彩票内容，格式参考{"彩票投注":["彩票类型","号码","数量","金额","倍数"],"追号":["期号","倍数"],"追号期数":"100"}
	 * {"TouZhu":[["509","0,0,0","1","2"，"1"],["509","0,1,9","1","2","1"]],"BeiShu":[["2014250","1"],["2014251","2"]],"Count":"2"}
	 * @param  shop=店铺，
	 * @param  amount=金额，
	 * @param  memo=描述，
	 * @param  deviceID=, 0:android,1:IOS,2:phoneweb,3:web,4:终端机，
	 * @return fail:0-成功; other-error_code; mess:提示信息
	 */
	public function buyIn(){
		$this->buyIn_valid();
		#获取合买记录
		$launch=$this->LotteryJoint_model->get_lotteryJoint($_REQUEST['id']);
		if(!$launch){
			$this->retFailed('合买不存在');
		}
		$remaind=$launch['money']-$launch['sell'];
		if($_REQUEST['money']>$remaind){
			$this->retFailed('最多可购买'.$remaind.'元');
		}
		if($launch['stopRaiseTime']<date("Y-m-d H:i:s:m")){
			$this->retFailed('合买已过期');
		}
		$state=((int)$launch['money']==(int)$launch['sell']+(int)$_REQUEST['money']/(int)$launch['unit'])?0:8;
		//更新合买
		$arr['state']=$state;
		$arr['sell']=$launch['sell']+$_REQUEST['money']/(int)$launch['unit'];
		$this->LotteryJoint_model->update_lotteryJoint($launch['ID'],$arr);
		$addID=$this->generalSeek(BUY_JOINT);
		$pending_id;
		$this->pending($addID,$pending_id,$launch['issue'].'期,'.$launch['schemeName'].'参与合买',$launch['lotteryID']);
		$data=array('ID'=>$addID,//.$this->userID;,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'lotteryJointID'=>$_REQUEST['id'],		
					'state'=>$state,
					'money'=>$_REQUEST['money'],
					'financeID'=>$pending_id,
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time())					
		);
		#入库
		if (!$this->LotteryJoint_model->add_lotteryParticipants($data)){
			$this->retFailed('参与合买失败');
		}
		//$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
	}

	private function launch_valid(&$stopTime){
		$params=array('money'=>'金额','issue'=>'期号',
		'lotteryID'=>'彩种','multiple'=>'倍数',
		'text'=>'投注内容','publicType'=>'公开类型',
		'schemeName'=>'合买主题','userName'=>'发起人名称',
		'ensure'=>'保底金额','take'=>'提成比例',
		'launcherBuy'=>'发起人购买');
		Extension::params_valid($params);
		if($_REQUEST['money']<8){
			$this->retFailed('发起总量至少8元');
		}
		if(floor($_REQUEST['take'])!=(int)$_REQUEST['take']||(int)$_REQUEST['take']>10|| (int)$_REQUEST['take']<1){
			$this->retFailed('提成比例必须为1-10之间整数');
		}
		$min=(int)$_REQUEST['money']*(int)$_REQUEST['take']/100;
		if((int)$_REQUEST['launcherBuy']<ceil($min)){
			$this->retFailed('发起人至少购买'+ceil($min)+'元');
		}
		$count=Extension::getBetCount($_REQUEST['lotteryID'], $_REQUEST['text']);
		if($count*(int)$_REQUEST['multiple']*2!=$_REQUEST['money']){
			$this->retFailed('金额有误');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']));
		$credit=abs(floatval(@$_REQUEST['credit']/100));
		if($cash+$credit!=floatval($_REQUEST['money'])){
			$this->retFailed('支付金额有误,订单金额应为'.$_REQUEST['money'].'元');
		}
		//验证期号
		$data=$this->LotteryIssue_model->getIssue($_REQUEST['lotteryID']);
		if(empty($data)){
			$this->retFailed('无法获取正确期号');
		}
		if($_REQUEST['issue']==$data['issue']){
			$stopTime=$data['stopTime'];
			if($data['stopTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['issue'].'期已停止购买，当前可购买'.$data['nextIssue'].'期');
			}
		}elseif($_REQUEST['issue']==$data['nextIssue']){
			$stopTime=$data['nextTime'];
			if($data['nextTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['nextIssue'].'期已停止购买');
			}
		}elseif($_REQUEST['issue']<$data['issue']){
				$this->retFailed($_REQUEST['issue'].'期已停止购买，当前可购买'.$data['issue'].'期');
		}
		return true;
	}
	private function buyIn_valid(){
		$params=array('money'=>'金额','id'=>'合买编号');
		Extension::params_valid($params);
		if($_REQUEST['money']<1){
			$this->retFailed('至少购买1元');
		}
		if(!is_int((int)$_REQUEST['money'])){
			$this->retFailed('购买份数应为整数');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']));
		$credit=abs(floatval(@$_REQUEST['credit']/100));
		if($cash+$credit!=floatval($_REQUEST['money'])){
			$this->retFailed('支付金额有误,订单金额应为'.$_REQUEST['money'].'元');
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
		$data=$this->LotteryJoint_model->lotteryJointOfLaunchs($num,$offset);
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
		$data=$this->LotteryJoint_model->get_lotteryJoints($condition,$num,$offset);
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
		$condition['b.userID']=$this->userID;
		if(isset($_REQUEST['state'])){
			$condition['a.state']=$_REQUEST['state'];
		}
		$data=$this->LotteryJoint_model->get_lotteryParticipantses($condition,$num,$offset);
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
		$data=$this->LotteryJoint_model->lotteryLaunchDetail($condition,$num,$offset);
		return $this->retAcess($data,0);
	}
	/**
	 * 投注方案
	 */
	public function text(){
		$launch=$this->LotteryJoint_model->get_lotteryJoint($_REQUEST['id']);
		if(empty($launch)){
			$this->retFailed('合买编号不存在');
		}
		//var_dump($launch);
		$launch['text']=null;
		if($launch['userID']!=$this->userID){
			switch((int)$launch['publicType']){
				#立即公开
				case 0:
					$data = $this->LotteryJoint_model->get_lotteryJointEntity($_REQUEST['id']);
					$launch['text']=$data['text'];
					break;
					#跟单人公开
				case 1:
					$condition['userID']=$this->userID;
					$condition['lotteryJointID']=@$_REQUEST['id'];
					$count=$this->LotteryJoint_model->count_participants($condition);
					if($count>0){
						$data = $this->LotteryJoint_model->get_lotteryJointEntity($_REQUEST['id']);
						$launch['text']=$data['text'];
					}
					break;
				default:
					break;
			}
		}
		#本人发起
		else{
			$data = $this->LotteryJoint_model->get_lotteryJointEntity($_REQUEST['id']);
			$launch['text']=$data['text'];
			
		}
		$launch['runNumbers']=@$data['runNumbers'];
		$launch['text']=json_decode($launch['text'],true);	
		$this->retAcess($launch,0);
	}
}
