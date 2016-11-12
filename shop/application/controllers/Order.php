<?php
//记录分页显示
class Order extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('form');
	}
		//列表
		
		//{未完成
//    <!--"id": "81",
//    "operateType": "3",
//    "orderID": "930291839928193",
//    "serialNumber": "1506305302780067",
//    "phoneNumber": "13993001010",
//    "app": "cai-lan-zi",
//    "shop": "shop-003",
//    "cost": "12000",
//    "money": "0",
//    "create_date": "2015-06-30",
//    "create_time": "16:06:27",
//    "subapp": "0",
//    "flag": "0",
//    "shop_flag": "0",
//    "sms_code": "",
//    "callback": null,
//    "comment": "2个苹果，1个梨\r\n",
//    "user": "13993001010"
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
		$params['shop'] = "shop-003";//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
		//$params['user'] = $this->user;
		$params['start'] = @$_REQUEST['start'];
		$params['end'] = @$_REQUEST['end'];
		$status = @isset($_REQUEST['status'])?$_REQUEST['status']:1;
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		//var_dump($params);
		if($status==1)
		{
			$response = $this->request('money', 'running/shop', $params);			
		}else{
			$response = $this->request('money', 'shop/pending', $params);	
		}
		$params['status']=$status;
		$response['params']=$params;
		$response['pageIndex']=$page;
		if ($response['fail'] == 0) {
				$response['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
		} 	
		$this->load->view('order.html',$response);
	}
	function detail($id){
		$params['shop'] = "shop-003";
		$params['id']=$id;
		$params['lastcode'] = $this->get_session('code');
		//var_dump($params);
		if($_REQUEST['status']==1)
		{
			$response = $this->request('money', 'running/shop', $params);			
		}else{
			$response = $this->request('money', 'shop/pending', $params);	
		}	
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 
		if(count($response['data'])<1){
			error("查无此记录");
			return ;
		}
		$order=	$response['data'][0];	
		$this->load->view('order_detail.html');
	}
	function cancle($orderId){
				
		$this->load->view('order.html',$response);
		}
	function confirm($orderId){
				
		$this->load->view('order.html',$response);
		}
}
