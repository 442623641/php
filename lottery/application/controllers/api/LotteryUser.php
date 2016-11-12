<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩（自购）
class LotteryUser extends Home_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->Model('Lotteryuser_model');
		$this->load->Model('Lotteryissue_model');
	}
	public function test(){
		$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
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
		$pending_id;
		$this->pending($addID,$pending_id,$_REQUEST['issue'].'期',null);
		$data=array('ID'=>$addID,
					'userID'=>$this->userID,
					'shop'=>@$_REQUEST['shop'],
		    		'money'=>$_REQUEST['money'],
					'financeID'=>$pending_id,
					'issue'=>$_REQUEST['issue'],
					'lotteryID'=>$_REQUEST['lotteryID'],
					'multiple'=>$_REQUEST['multiple'],
					'state'=>0,
					//'deviceID'=>$_REQUEST['deviceID'],
					'createDate'=>date('Y-m-d H:i:s',time()),
					'updateDate'=>date('Y-m-d H:i:s',time()),					
					'memo'=>@$_REQUEST['mome']
		);
		$dataEntity=array(
					'ID'=>$addID,
					'lotteryUserID'=>$addID,
		    		'text'=>$_REQUEST['text'],
					'money'=>$_REQUEST['money'],
					'entityID'=>0,
					'state'=>0,
					'createDate'=>date('Y-m-d H:i:s',time())
		);
		#入库
		if (!$this->Lotteryuser_model->add_lotteryUser($data,$dataEntity)){
			$this->retFailed('下单失败');
		} 
		$data['shopInfo']=$this->shopInfo();
		$this->retAcess($data,0);
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
		//金额
		$cash=abs(floatval(@$_REQUEST['cash']));
		$credit=abs(floatval(@$_REQUEST['credit']/100));
		if($cash+$credit!=abs(floatval($_REQUEST['money']))){
			$this->retFailed('支付金额有误,订单金额应为'.$_REQUEST['money'].'元');
		}
		//验证期号
		$data=$this->Lotteryissue_model->getIssue($_REQUEST['lotteryID']);
		if(empty($data)){
			$this->retFailed('无法获取正确期号');
		}
		if($_REQUEST['issue']==$data['issue']){
			if($data['stopTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($_REQUEST['issue'].'期已停止购买，当前可购买'.$data['nextIssue'].'期');
			}
		}elseif($_REQUEST['issue']==$data['nextIssue']){
			if($data['nextTime']<date("Y-m-d H:i:s:m")){
				$this->retFailed($data['nextTime'].'期已停止购买');
			}
		}elseif($_REQUEST['issue']<$data['issue']){
				$this->retFailed($_REQUEST['issue'].'期已停止购买，当前可购买'.$data['issue'].'期');
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
	 * 用户投注详细信息
	 */
	public function detail(){
		$userid = $condition['userID']=$this->userID;
		$lotteryUserID = $_REQUEST['id'];  //0：全部 1：已中奖 2：未中奖
		$data = $this->Lotteryuser_model->get_lotteryUserEntity($lotteryUserID);
		//var_dump($data[0]);
		if(is_array($data)){	
			$data[0]['text']=json_decode($data[0]['text'],true);		
			$this->retAcess($data,0);
		}else{
			$this->retFailed('用户暂无投注信息！');
		}
	}
}
