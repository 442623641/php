<?php
//记录分页显示
class Trading extends Home_Controller{

	public function __construct(){
		parent::__construct();
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
		$response = $this->request('money', 'shop/pending', $params);	
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
		$this->load->view('trading.html',$response);
	}
	
function detail($id,$page=1){	
		$params['shop'] = "shop-003";
		$params['id']=$id;
		$params['lastcode'] = $this->get_session('code');
		$response = $this->request('money', 'shop/pending', $params);				
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
		$this->load->view('trading_detail.html',$response);
	}
	function undeal($id){		
		$params['shop'] = "shop-003";
		$params['pending_id']=$id;
		$params['clerk']=$this->dealerID;
		$params['lastcode'] = $this->get_session('code');
		$response = $this->request('money', 'order/undeal', $params);				
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 
		else{
			success("Trading/index/1", "订单取消成功");
		}
		return;		
	}
	function deal($id,$url=null){		
		$params['shop'] = "shop-003";
		$params['pending_id']=$id;
		$params['clerk']=$this->dealerID;
		$params['lastcode'] = $this->get_session('code');
		$response = $this->request('money', 'order/deal', $params);				
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 
		else{
			$url=$url==null?"Traded/detail\/".$id:$url;
			success($url, "订单已经完成");
		}
		return;		
	}
}
