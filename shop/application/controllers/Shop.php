<?php
//记录分页显示
class Shop extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
	public function index($page=1){
		$response=array('totalPage'=>1);	
		$params['user'] =$this->dealer;//$this->dealer;
		$params['lastcode'] = $this->get_session('code');
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		$response = $this->request('dealer', 'shop/lists', $params);		
		if ($response['fail'] == 0) {
			$data['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		$data['pageSize']=$pageSize;
		$data['pageIndex']=$page;	
		$data['params']=$params;
		$data['data']=$response['content'];
		$this->load->view('shop_list.html',$data);
	}
	public function lists($page=1){
		$response=array('totalPage'=>1);	
		$params['user'] =$this->dealer;//$this->dealer;//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		$response = $this->request('dealer', 'shop/lists', $params);	
		if ($response['fail'] == 0) {
			$data['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		$data['pageSize']=$pageSize;
		$data['pageIndex']=$page;	
		$data['params']=$params;
		$data['data']=$response['content'];
		$this->load->view('shop_list.html',$data);
	}
	public function collects($page=1){
		$response=array('totalPage'=>1);	
		$params['user'] =$this->dealer;//$this->dealer;//$this->get_session('code');
		$params['lastcode'] = $this->get_session('code');
		$pageSize = 9;
		$offset = ($page-1) * $pageSize+1;
		$params['limit'] = "{$offset}, {$pageSize}";
		$response = $this->request('money', 'collect/collect', $params);	
		if ($response['fail'] == 0) {
			$data['totalPage']=ceil($response['total']/$pageSize);	
		}
		//return $response;
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		$data['pageSize']=$pageSize;
		$data['pageIndex']=$page;	
		$data['params']=$params;
		$data['data']=$response['content'];
		$this->load->view('shop_list.html',$data);
	}
	function info($shop=null){
		if($shop)
		{
			$params['user'] =$this->dealer;//;'13993001010';		
			$params['$shop'] = $shop;
			$params['lastcode'] = $this->get_session('code');
			$response = $this->request('dealer', 'shop/lists', $params);				
			if ($response['fail'] != 0) {
				error($response['mess']);
				return ;
			} 
			if(count($response['content'])<1){
				error("查无此记录");
				return ;
			}	
			$response['content'][0]['pass']="000000";
			$data=$response['content'][0];
			$data['title']="店铺信息";
			$data['action']=site_url("Shop/update");
		}
		else{
			$data['title']="添加店铺";
			$data['action']=site_url("Shop/add");	
		}
		$this->load->view('shopInfo.html',$data);
		
	}
	function action($actionStr){
		
		call_user_func("Shop::".$actionStr,$_REQUEST);
	}
	//      'add_city' => string '合肥' (length=6)
//      'address' => string '金寨路96号' (length=14)
//      'add_GPS_lng' => string '-45.0938476' (length=11)
//      'add_GPS_lat' => string '32.09876485' (length=11)
	function update(){
		if(empty($_REQUEST['id'])){
			error("店铺编号不能为空");	
			return;	
		}
		if(empty($_REQUEST['address'])){
			error("请输入详细地址");
			return;		
		}
		if(strlen(trim($_REQUEST['pass']))<6){
			error("密码长度为6位");
			return;		
		}
		if(trim($_REQUEST['pass'])!='000000'){
			$params['pass'] = $_REQUEST['pass'];	
		}
		$params['shopid'] = $_REQUEST['shop'];
		$params['user'] =$this->dealer;
		$params['province'] = $_REQUEST['province'];
		$params['city'] = $_REQUEST['city'];
		$params['address']=@$_REQUEST['area'].'|'.$_REQUEST['address'];
		$params['gps_lng'] = $_REQUEST['gps_lng'];
		$params['gps_lat'] = $_REQUEST['gps_lat'];
		$params['shoppass'] = $_REQUEST['pass'];
		$params['lastcode'] = $this->get_session('code');		
		$response = $this->request('dealer', 'shop/update', $params);				
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		}
		success("Shop/info\/".$_REQUEST['shopID'], "更新成功")	;
	}
//	user=用户名(手机号），
// shopid=店铺号 ，
// shoppass=密码，
// shopaddress=地址，
// province=省份，
// city=市，
// gps_lng=纬度，
// gps_lat=经度
	function add(){
		if(empty($_REQUEST['shop'])){
			error("店铺名不能为空");	
			return;	
		}
		if(empty($_REQUEST['address'])){
			error("请输入详细地址");
			return;		
		}
		if(empty($_REQUEST['pass'])){
			error("请输入店铺密码");
			return;		
		}
		$params['shopid'] = $_REQUEST['shop'];
		$params['user'] =$this->dealer;;
		$params['province'] = $_REQUEST['province'];
		$params['city'] = $_REQUEST['city'];
		$params['address']=@$_REQUEST['area'].'|'.$_REQUEST['address'];
		$params['gps_lng'] = $_REQUEST['gps_lng'];
		$params['gps_lat'] = $_REQUEST['gps_lat'];
		$params['lastcode'] = $this->get_session('code');
		$params['shoppass'] = $_REQUEST['pass'];
		$response = $this->request('dealer', 'shop/add', $params);		
		if ($response['fail'] != 0) {
			error($response['mess']);
			return;
		} 
		success("Shop/lists", "添加成功");
	}
	function delete($shop){
		$params['user'] =$this->dealer;
		$params['shop'] = $shop;
		$params['lastcode'] = $this->get_session('code');
		//$params['shoppass'] = '123456';
		$response = @$this->request('dealer', 'shop/delete', $params);	
		if (empty($response)) {
			error("网路异常，请稍候再试");
			return ;
		}			
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 
		success("Shop/lists", "删除成功");	
	}
	
}
