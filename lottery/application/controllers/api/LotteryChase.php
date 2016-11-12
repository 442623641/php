<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
error_reporting(0);
//用户购彩（自购）
class LotteryChase extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotterychase_model');
		$this->load->Model('Lotteryissue_model');
	}
public function test(){
		$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
	}
	/**
	 * 用户购买彩票
	 * @param  issues=[[2014250,1],[2014251,2],[2014253,2],[2014254,2]]，按期号升序排列
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
	public function add(){
		$currentIssue=0;
		#校验参数
		$sort_issues=json_decode(@$_REQUEST['issues'],true);
		sort($sort_issues);
		$totalMultiple=Extension::totalMultiple($sort_issues);
		//echo 'tttt'.$totalMultiple;
		#校验参数投注号码,金额
		$this->valid($totalMultiple,$sort_issues,$currentIssue);
		#初始化订单号,当前期号

		$chaseID=empty($_REQUEST['sms_code'])?$this->generalSeek(BUY_CHASE):$_REQUEST['order_id'];//.$this->userID;
		#冻结用户金额
		$this->pending($chaseID,$pending_id,$sort_issues[0][0].'期追号');
		$firstMoney=$currentIssue==$sort_issues[0][0]?$sort_issues[0][1]/$totalMultiple*$_REQUEST['money']:0;
		//追号下单
		$chaseData=array('ID'=>$chaseID,//.$this->userID;,
						'userID'=>$this->userID,
						'issues'=>json_encode($sort_issues),
						'shop'=>@$_REQUEST['shop'],
			    		'money'=>$_REQUEST['money'],
						'financeID'=>$pending_id,
						'lotteryID'=>$_REQUEST['lotteryID'],
						'multiple'=>@$_REQUEST['multiple'],
						'state'=>7,
						'text'=>$_REQUEST['text'],	
						'totalPhases'=>count($sort_issues),
						'chasePhases'=>$currentIssue!=0?1:0,
						'realMoney'=>$firstMoney,
						'currentIssue'=>$currentIssue,
						'type'=>isset($_REQUEST['type'])?$_REQUEST['type']:1,
						'isWinners'=>isset($_REQUEST['isWinners'])?$_REQUEST['isWinners']:1,
						'createDate'=>date('Y-m-d H:i:s',time()),
						'updateDate'=>date('Y-m-d H:i:s',time()),					
						'memo'=>@$_REQUEST['mome']
		);
		if (!$this->Lotterychase_model->add_chaseUser($chaseData)){
			$this->retFailed('追号失败');
		}
		//当前期下单入库
			$firstID=$this->generalSeek(BUY_CHASE_DETAIL);
			$data=array('ID'=>$firstID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'money'=>$firstMoney,
					'index'=>1,
					'chaseID'=>$chaseID,
					'financeID'=>$pending_id,
					'issue'=>$sort_issues[0][0],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'multiple'=>$sort_issues[0][1],
					'state'=>0,
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time()),					
					'memo'=>@$_REQUEST['mome']
			);
			$dataEntity=array(
						'ID'=>$firstID,
						'lotteryUserID'=>$firstID,
			    		'text'=>$_REQUEST['text'],
						'money'=>$_REQUEST['money'],
						'entityID'=>0,
						'state'=>0,
						'createDate'=>date('Y-m-d H:i:s',time()),
			);
			#入库
		if (!$this->Lotterychase_model->add_lotteryChase($data,$dataEntity)){
			$this->retFailed('当前期下单失败');
		}
		$chaseData['shopInfo']=$this->shopInfo();
		$this->retAcess($chaseData,0);
	}
	private function valid($totalMultiple,&$issues,&$currentIssue){
		$params=array('money'=>'金额','issues'=>'期号','lotteryID'=>'彩种','multiple'=>'倍数','text'=>'投注');
		if(!empty($_REQUEST['sms_code'])){
			$params['order_id']="订单编号";
			$params['pending_id']="pending_id";
		}
		Extension::params_valid($params);
		$count=Extension::getBetCount($_REQUEST['lotteryID'], $_REQUEST['text']);
		if($count*$totalMultiple*$_REQUEST['multiple']*2!=$_REQUEST['money']){
			$this->retFailed('金额有误');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']));
		$credit=abs(floatval(@$_REQUEST['credit']/100));
		if($cash+$credit!=abs(floatval($_REQUEST['money']))){
			$this->retFailed('支付金额有误,订单金额应为'.abs($_REQUEST['money']).'元');
		}
		for($i=0;$i<count($issues);$i++){
			$issues[$i][2]=13;
		}
		$data=$this->Lotteryissue_model->getIssue($_REQUEST['lotteryID']);
		if(empty($data)){
			$this->retFailed('无法获取正确期号');
		}
		if($data['issue']==$issues[0][0]){
			$currentIssue=$data['issue'];
			$issues[0][2]=0;
			if($data['stopTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['issue'].'期已停止购买，当前可购买'.$data['nextIssue'].'期');
			}
		}elseif($issues[0][0]==$data['nextIssue']){
			$currentIssue=$data['nextIssue'];
			$issues[0][2]=0;
			if($data['nextTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['nextIssue'].'期已停止购买');
			}
		}elseif($issues[0][0]<$data['issue']){
			$this->retFailed($issues[0][0].'已停止购买,当前期为'.$data['issue']);
		}

		return true;
	}
	/**
	 * 追號投注记录
	 */
	public function lists(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['userID']=$this->userID;
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];
		}
		$data = $this->Lotterychase_model->get_chaseUsers($condition,$num,$offset);
		if(is_array($data)){
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂时还没有追号');
		}
	}
	/**
	 * 	扯单
	 */
	private function drop($issue,$lotteryID,$chaseID){

		$current=$this->Lotteryissue_model->getIssue($lotteryID);
		//当前期扯单
		if($current['issue']==$issue&&$current['stopTime']<date("Y-m-d H:i:s:m")){
			$data['state']=5;
			$condition['issue']=$issue;
			$condition['userID']=$this->userID;
			$condition['chaseID']=$chaseID;
			$condition['state']=0;
			return $this->Lotterychase_model->update_lotteryChase($condition,$data)>0;
		}
		return false;
	}
	/**
	 * 	批量扯单
	 */
	public function drops(){
		$params=array('issues'=>'撤销期号列表','chaseID'=>'追号编号');
		Extension::params_valid($params);
		$data=array();
		$dropMultiple=0;
		$_REQUEST['issues']=json_decode($_REQUEST['issues'],true);
		sort($_REQUEST['issues']);
		$chase=$this->Lotterychase_model->get_chaseUser($_REQUEST['chaseID']);
		//var_dump($chase);
		$update=array();
		if(empty($chase)){
			$this->retFailed('系统存在异常，无法撤单');
		}
		$issues=json_decode($chase['issues'],true);
		$totalMultiple=0;
		$current=$this->Lotteryissue_model->getIssue($chase['lotteryID']);
		if($chase['state']==6){
			$this->retFailed('追号已停止,无法撤销,当前期'.$current['issue']);
		}
		$current=$this->Lotteryissue_model->getIssue($chase['lotteryID']);
		$currentIndex=0;
		for($i=0;$i<count($issues);$i++){
			$totalMultiple+=$issues[$i][1];
			if($current['issue']==$issues[$i][0]){
				$currentIndex=$i;
				continue;
			}
			if($issues[$i][2]!=13){
				continue;
			}
			if(!in_array($issues[$i][0],$_REQUEST['issues'])){
				continue;
			}
			if($current['issue']>=$issues[$i][0]||$chase['currentIssue']>$issues[$i][0]){
				continue;
			}
			$dropIssues[count($dropIssues)]=$issues[$i][0];
			$dropMultiple+=(int)$issues[$i][1];
			//$update['chasePhases']=$chase['chasePhases']+1;
			$issues[$i][2]=5;//撤销
		}
		if(count($dropIssues)<1){
			$this->retFailed('条件不符,无法撤销,当前期'.$current['issue']);
		}
		$update['issues']=json_encode($issues);
		for($i=$currentIndex;$i<count($issues);$i++){
			if(in_array($issues[$i][2],array(0,1,2,10,13))){
				$update['state']=7;
				break;
			}
		}
		$response=$this->unpending($chase['money']*$dropMultiple/$totalMultiple,$chase['ID'],$chase['lotteryID'],join(',',$data));
		if($response['fail']==0){
			if(!$this->Lotterychase_model->update_chaseUser($update,$chase['ID'])){
				$this->retFailed('订单状态修改异常,已退款,管理员稍后会处理订单状态,当前期'.$current['issue']);
			}
		}else{
			$this->retFailed('网络延时,稍后再试,当前期'.$current['issue']);
		}
		if($update['state']==6){
			$this->pay($chase['realMoney'],$chase['ID'],$chase['lotteryID'],join(',',$data));
		}
		$update['issues']=$issues;
		$data['$update']=$update;
		$data['drops']=$dropIssues;
		$data['chaseNewAmount']=@$response['amount'];
		$data['rebackMoney']=@$response['return'];
		$data['return_money']=$response['return_money'];
		$data['return_credit']=$response['return_credit'];
		$this->retAcess($data,0);
		//$this->retFailed('条件不符,无法撤销!');
	}
	/**
	 * 	已追号订单
	 */
	public function listOfRun(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['userID']=$this->userID;

		if(isset($_REQUEST['chaseID'])){
			$condition['chaseID']=$_REQUEST['chaseID'];
		}
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];
		}
		$data = $this->Lotterychase_model->get_lotteryChases($condition,$num,$offset);
		if(is_array($data)){
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂时还没有记录！');
		}
	}
	/**
	 * 	已追号订单详细列表
	 */
	public function listsChase(){
		//echo 1111111;
		//exit;
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['b.userID']=$this->userID;

		if(isset($_REQUEST['chaseID'])){
			$condition['b.chaseID']=$_REQUEST['chaseID'];
		}
		if(isset($_REQUEST['state'])){
			$condition['b.state']=$_REQUEST['state'];
		}

		$data = $this->Lotterychase_model->get_lotteryChaseList($condition,$num,$offset);

		if(is_array($data)){
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂时还没有记录！');
		}
	}
	/**
	 * 	追号详情
	 */
	public function chaseDetail_back(){
		$chase = $this->Lotterychase_model->get_lotteryChaseJoinChaseUserJointEntity($_REQUEST['id']);
		if(is_array($chase)){
			$chase['issues']=json_decode($chase['issues'],true);
			$chase['text']=json_decode($chase['text'],true);
			$data['chase']=$chase;
			$chaseds = $this->Lotterychase_model->get_lotteryChaseJoinEntity($chase['chaseID']);
			$data['chasedlist']=$chaseds;
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂时还没有这追号信息！');
		}
	}
	/**
	 * 	追号详情
	 */
	public function chaseDetail(){
		$condition['ID']=$_REQUEST['id'];
		$chase = $this->Lotterychase_model->get_chaseUsers($condition);
		if(is_array($chase)){
			$chase[0]['issues']=json_decode($chase[0]['issues'],true);
			$chase[0]['text']=json_decode($chase[0]['text'],true);
			$data['chase']=$chase[0];
			$chaseds = $this->Lotterychase_model->get_lotteryChaseJoinEntity($_REQUEST['id']);
			$data['chasedlist']=$chaseds;
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂时还没有这追号信息！');
		}
	}
}