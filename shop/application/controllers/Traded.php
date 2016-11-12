<?php
//记录分页显示
class Traded extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
//列表
//已完成
//{
//    "id": "311",
//    "operateType": "3",
//    "phoneNumber": "10011",
//    "app": "cai-lan-zi",
//    "shop": "shop-003",
//    "orderID": "930291839928193",
//    "cost": "-12000",
//    "balance": "0",
//    "create_date": "2015-07-11",
//    "create_time": "10:07:31",
//    "finish_date": "2015-07-11",
//    "finish_time": "11:44:43",
//    "subapp": "0",
//    "state_rcs": "0",
//    "comment": "2个苹果，1个梨\r\n",
//    "user": "10011"
//}
//	-->}

	public function index($page=1){
		$response=array('totalPage'=>1);
		$params['type'] = 3;
		$params['shop'] = "shop-003";//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
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
		$this->load->view('Traded.html',$response);
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
		$this->load->view('Traded_detail.html',$response);
	}
	function cancle($orderId){
				
	$this->load->view('Traded_detail.html');
		}
	function confirm($orderId){
				
	$this->load->view('Traded_detail.html');

		}
}
