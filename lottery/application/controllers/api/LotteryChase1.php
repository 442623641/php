<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩（自购）
class Lotterychase1 extends Home_Controller{
	protected $issue;
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotterychase1_model');
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
		//var_dump($_REQUEST);
		$sort_issues=json_decode(@$_REQUEST['issues'],true);
		sort($sort_issues);
		$totalMultiple=Extension::totalMultiple($sort_issues);
		//echo 'tttt'.$totalMultiple;
		#校验参数投注号码,金额
		$this->valid($totalMultiple,$sort_issues,$currentIssue);
		#初始化订单号,当前期号
		$chaseID=$this->generalSeek(BUY_CHASE);//.$this->userID;
		#冻结用户金额
		//$this->pending($chaseID,$pending_id,$sort_issues[0][0].'期追号');
		$firstMoney=$currentIssue==$sort_issues[0][0]?$sort_issues[0][1]/$totalMultiple*$_REQUEST['money']:0;
		$detail='追号'.count($sort_issues).'期-'.$_REQUEST['issues'];
		$response=$this->prepare($chaseID,$sort_issues[0][0],$detail); 
		//$this->request($class, $function, $parameter);//($chaseID,$pending_id,$sort_issues[0][0].'期追号');
		//追号下单
		$chaseData=array('ID'=>$chaseID,//.$this->userID;,
						'userID'=>$this->userID,
						'issues'=>json_encode($sort_issues),
						'shop'=>@$_REQUEST['shop'],
			    		'money'=>$_REQUEST['money'],
						'financeID'=>$response['pendingID'],
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
		//首期下单入库
		$firstID=$this->generalSeek(BUY_CHASE_DETAIL);
		$data=array('ID'=>$firstID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'money'=>$firstMoney,
					'index'=>1,
					'chaseID'=>$chaseID,
					'financeID'=>$response['pendingID'],
					'issue'=>$sort_issues[0][0],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'multiple'=>$sort_issues[0][1],
					'state'=>@config_item('isTicket'),
					'deviceID'=>@$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time()),					
					'memo'=>$_REQUEST['text']
		);
		#入库
		if (!$this->Lotterychase1_model->add_lotterychase($chaseData,$data)){
			$this->retFailed('追号失败');
		}
		$chaseData['shopInfo']=$this->shopInfo(); 
		//$chaseData['fID']=$firstID;
		$this->retAcess($chaseData,0);
	}
	public function verify(){
		$params=array('id'=>'订单号','payType'=>'支付方式');
		Extension::params_valid($params);
		$chase=$this->Lotterychase1_model->get_chaseUser($_REQUEST['id']);
		if(empty($chase)){
			$this->retFailed('订单不存在1');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']))*100;
		$credit=abs(floatval(@$_REQUEST['credit']));
		$money=abs($chase['money'])*100;
		if($cash+$credit!=$money){
			$this->retFailed('支付金额有误,订单金额应为'.$chase['money'].'元');
		}
		
		$order=$this->Lotterychase1_model->get_lotteryChase($_REQUEST['id']);
		if(empty($order)){
			$this->retFailed('订单不存在');
		}
		$lotteryID=$order['lotteryID'];
		$issueData=$this->Lotteryissue_model->getIssue($lotteryID);
		if(empty($issueData)){
			$this->retFailed('无法获取正确期号');
		}
		$lotteryConfig=$this->getLotteryInfo($lotteryID);
		$interval=$lotteryConfig['payInterval'];
		if($order['issue']==$issueData['issue']){
			if((strtotime($issueData['stopTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
			}
		}elseif($order['issue']==$issueData['nextIssue']){
			if((strtotime($issueData['nextTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
			}
		}elseif($order['issue']<$issueData['issue']){
			$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
		}
		$this->session->set_userdata($order['ID'], array('payType'=>$_REQUEST['payType'],'id'=>$order['ID'],'time'=>date("Y-m-d H:i:s")));
		//确认支付
		$response=$this->prepay($order['ID'],$cash,$credit,$money,$lotteryID);
		$data['id']=$_REQUEST['id'];
		$data['money']=$chase['money'];
		$data['secCode']=session_id();
		$this->retAcess($data);
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
		//$cash=abs(floatval(@$_REQUEST['cash']));
		//$credit=abs(floatval(@$_REQUEST['credit']/100));
		//if($cash+$credit!=abs(floatval($_REQUEST['money']))){
		//	$this->retFailed('支付金额有误,订单金额应为'.abs($_REQUEST['money']).'元');
		//}
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
				$res['fail']=6001;
				$res['mess']=$_REQUEST['issue'].'期已停止购买';
				$res['issue']=$data['nextIssue'];
				echo json_encode($res);
				exit;
			}
		}elseif($issues[0][0]==$data['nextIssue']){
			$currentIssue=$data['nextIssue'];
			$issues[0][2]=0;
			if($data['nextTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['nextIssue'].'期已停止购买');
			}
		}elseif($issues[0][0]<$data['issue']){
			$res['fail']=6001;
			$res['mess']=$issues[0][0].'期已停止购买';
			$res['issue']=$data['issue'];
			echo json_encode($res);
			exit;
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
}
