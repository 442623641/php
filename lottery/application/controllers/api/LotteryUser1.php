<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//用户购彩（自购）
class LotteryUser1 extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotteryuser_model');
		$this->load->Model('Lotteryissue_model');
		$this->load->Model('Lotteryuser1_model');
	}
	/**
	* 用户购买彩票
	* @param  issue=期号
 	* @param  multiple=倍数，
 	* @param  lottteryID=彩种，
 	* @param  text=彩票内容，格式参考{"彩票投注":["彩票类型","号码","数量","金额","倍数"],"追号":["期号","倍数"],"追号期数":"100"} 
  	* @param  shop=店铺，
  	* @param  amount=金额，
   	* @param  memo=描述，
	* @return fail:0-成功; other-error_code; mess:提示信息
	*/
	public function add(){
		$this->valid();
		#生成订单号
		$addID=$this->generalSeek(BUY_GENERAL);//.$this->userID;
		//$this->retFailed(str_pad((int)$this->userID,6,0,STR_PAD_LEFT));
		$detail='1期,'.$_REQUEST['multiple'].'倍';
		$response=$this->prepare($addID,$detail);
		$data=array('ID'=>$addID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'money'=>$_REQUEST['money'],
					'financeID'=>$response['pendingID'],
					'issue'=>$_REQUEST['issue'],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'multiple'=>$_REQUEST['multiple'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'state'=>@(int)config_item('isTicket'),
					//'deviceID'=>$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time()),				
					'memo'=>$_REQUEST['text']
		);
		if (!$this->Lotteryuser1_model->add_lotteryUser($data)){
			$this->retFailed('订单提交失败'); 
		} 
		$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
	}
	
	public function verify(){
		$params=array('id'=>'订单号','payType'=>'支付方式');
		Extension::params_valid($params);
		$order=$this->Lotteryuser1_model->get_lotteryUser($_REQUEST['id']);
		if(empty($order)){
			$this->retFailed('订单不存在');
		}
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']))*100;
		$credit=abs(floatval(@$_REQUEST['credit']));
		$money=abs($order['money'])*100;
		if($cash+$credit!=$money){
			$this->retFailed('支付金额有误,订单金额应为'.$order['money'].'元');
		}
		$lotteryID=$order['lotteryID'];
		//var_dump($response);
		$issueData=$this->Lotteryissue_model->getIssue($lotteryID);
		if(empty($issueData)){
			$this->retFailed('无法获取正确期号');
		}
		$lotteryConfig=$this->getLotteryInfo($lotteryID);
		$interval=$lotteryConfig['payInterval'];
		if($order['issue']==$issueData['issue']){
			if((strtotime($issueData['stopTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				//$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
				$res['fail']=6001;
				$res['mess']='订单失效,'.$order['issue'].'期已停止支付';
				$res['issue']=$issueData['nextIssue'];
				echo json_encode($res);
				exit;
			}
		}elseif($order['issue']==$issueData['nextIssue']){
		if((strtotime($issueData['nextTime'])-strtotime(date("Y-m-d H:i:s")))<$interval){
				$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
			}
		}elseif($order['issue']<$issueData['issue']){
				//$this->retFailed('订单失效,'.$order['issue'].'期已停止支付');
			$res['fail']=6001;
			$res['mess']='订单失效,'.$order['issue'].'期已停止支付';
			$res['issue']=$issueData['issue'];
			echo json_encode($res);
		}
		//$this->session->set_userdata($order['ID'], array('payType'=>$_REQUEST['payType'],'id'=>$order['ID'],'cash'=>$cash,'credit'=>$credit,'time'=>date("Y-m-d H:i:s")));
		//确认支付
		$response=$this->prepay($order['ID'],$cash,$credit,$money,$lotteryID);
		$data['id']=$order['ID'];
		$data['money']=$order['money'];
		//$data['secCode']=session_id();
		$data['credit']=$credit;
		$data['cash']=$cash;
		//$data['pending_id']=$response['pending_id'];
	 	$this->retAcess($data);
	}
	private function valid(){
		$params=array('money'=>'金额','issue'=>'期号','lotteryID'=>'彩种','multiple'=>'倍数','text'=>'投注');
		#校验参数投注号码,金额
		Extension::params_valid($params);
		//var_dump($_REQUEST);
		$count=Extension::getBetCount($_REQUEST['lotteryID'],$_REQUEST['text']);
		if($count*(int)$_REQUEST['multiple']*2!=$_REQUEST['money']){
			$this->retFailed('金额有误');
		}
		$data=$this->Lotteryissue_model->getIssue($_REQUEST['lotteryID']);
		if(empty($data)){
			$this->retFailed('无法获取正确期号');
		}
		if($_REQUEST['issue']==$data['issue']){
			if($data['stopTime']<date("Y-m-d H:i:s:m")){
				//$this->retFailed($_REQUEST['issue'].'期已停止购买，当前可购买'.$data['nextIssue'].'期');
				$res['fail']=6001;
				$res['mess']=$_REQUEST['issue'].'期已停止购买';
				$res['issue']=$data['nextIssue'];
				echo json_encode($res);
				exit;
			}
			//$stopTime=$data['nextTime'];
		}elseif($_REQUEST['issue']==$data['nextIssue']){
			if($data['nextTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['nextIssue'].'期已停止购买');
			}
			//$stopTime=$data['stopTime'];
		}elseif($_REQUEST['issue']<$data['issue']){
				//$this->retFailed($_REQUEST['issue'].'期已停止购买，当前可购买'.$data['issue'].'期');
			$res['fail']=6001;
			$res['mess']=$_REQUEST['issue'].'期已停止购买';
			$res['issue']=$data['issue'];
			echo json_encode($res);
			exit;
		}
		else if($_REQUEST['issue']>$data['nextIssue']){
			//$min=$_REQUEST['issue'](int)$_REQUEST['issue']
			//$stopTime=date("Y-m-d H:i:s",strtotime($data['nextTime']."+ 9 minute"));
		}
		return true;
	}
	/**
	 * 自购投注记录
	 */
	public function lists(){
		$index=@$_REQUEST['index'];
		$offset=@$_REQUEST['pageSize'];
		$num=($index*$offset)-$offset;
		$condition['userID']=$this->userID;	
		if(isset($_REQUEST['state'])){
			$condition['state']=$_REQUEST['state'];	
		}
		$data = $this->Lotteryuser_model->get_lotteryUsers($condition,$num,$offset);
		if(is_array($data)){
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂无投注信息！');
		}
	}
	/**
	 * 更新
	 */
	public function update(){
		$params=array('id'=>'编号','text'=>'内容');
		#校验参数投注号码,金额
		Extension::params_valid($params);
		$condition['ID']=$_REQUEST['id'];
		$data['text']=$_REQUEST['text'];
		$re = $this->Lotteryuser_model->update_lotteryUser($condition,$data);
		if($re){
			$this->retAcess('success');
		}
		$this->retAcess('fail');
	}
	/**
	 * 用户投注详细信息
	 */
	public function detail(){
		$userid = $condition['userID']=$this->userID;
		$lotteryUserID = $_REQUEST['id'];  //0：全部 1：已中奖 2：未中奖
		$data = $this->Lotteryuser_model->get_lotteryUserEntity($lotteryUserID);
		if(is_array($data)){	
			$data[0]['text']=json_decode($data[0]['text'],true);		
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂无投注信息！');
		}
	}
}
