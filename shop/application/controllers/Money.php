<?php
//记录分页显示
class Money extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
	public function index($page=1){
		$response=array('totalPage'=>1);	
		$params['shop'] = "shop-003";//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
		$params['type'] = isset($_REQUEST['runningType'])?$_REQUEST['runningType']:0;
		$params['start'] = @$_REQUEST['start'];
		$params['end'] = @$_REQUEST['end'];
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		$response = $this->request('money', 'running/shop', $params);
		$params['pageSize']=$pageSize;
		$response['params']=$params;	
		$response['pageIndex']=$page;		
		if ($response['fail'] == 0) {
			$response['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		//$response["tradeTypes"]=array("0"=>"所有","1"=>"充值","2"=>"取现","3"=>"消费","4"=>"转入","5"=>"转出","6"=>"冻结");
		//print_r(config_item("tradeTypes"));	
		$this->load->view('money_running.html',$response);
	}
	public function running($page=1){
		$response=array('totalPage'=>1);	
		$params['shop'] = "shop-003";//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
		$params['operateType'] = isset($_REQUEST['runningType'])?$_REQUEST['runningType']:0;
		$params['start'] = @$_REQUEST['start'];
		$params['end'] = @$_REQUEST['end'];
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		$response = $this->request('money', 'running/shop', $params);
		$params['pageSize']=$pageSize;
		$response['params']=$params;	
		$response['pageIndex']=$page;		
		if ($response['fail'] == 0) {
			$response['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		//$response["tradeTypes"]=array("0"=>"所有","1"=>"充值","2"=>"取现","3"=>"消费","4"=>"转入","5"=>"转出","6"=>"冻结");
		//print_r(config_item("tradeTypes"));	
		$this->load->view('money_running.html',$response);
	}
	function detail($id,$page=1){
		$response['totalPage']=1;
		$response['pageIndex']=1;	
		$params['shop'] = "shop-003";
		$params['id']=$id;
		$params['lastcode'] = $this->get_session('code');
		$response = $this->request('money', 'running/shop', $params);				
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 
		if(count($response['data'])<1){
			error("查无此记录");
			return ;
		}	
		//获取用户信息
		unset($params);
		$params['user'] =@$response['data'][0]['user'];
		$userinfo = $this->request('user', 'info/get', $params);
		$response['data']=$response['data'][0];
		$response['data']['consumer']=@$userinfo['Data'][0]['name'];
		$response['data']['phone']=@$userinfo['Data'][0]['phoneNumber'];
		$response['data']['commission']=$response['data']['cost']*0.01;
		//var_dump($response);
		$this->load->view('money_running_detail.html',$response);
	}

}
