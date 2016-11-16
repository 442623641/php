<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class Menu extends API_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->Model ( 'Menu_model' );
		$this->load->Model ( 'Stack_model' );
	}
	public function index_get() {
		//最多收藏
		$data = $this->Stack_model->get_menusTop(9,"favo_count");
		$re[0]['title']='well-chosen';
		$re[0]['name']='精心挑选';
		$re[0]['data']=[];
		$re[0]['hots']=['早餐食谱','午餐','下午茶','晚餐','宵夜','家常菜','下饭菜','快手菜','私家菜','减肥食谱','朋友聚餐','懒人食谱','二人世界','二人世界','单身食谱','一家三口'];
		if($data&&count($data)>1)
			$re[0]['data']=json_decode(str_replace("/170_","/400x300_",json_encode($data)));
		//最新食谱
		unset($data);
		$data = $this->Stack_model->get_menusTop(9,'createTime');
		$re[1]['title']='popular';
		$re[1]['name']='大家都在看';
		$re[1]['data']=[];
		$re[1]['hots']=['五花肉','排骨','牛肉','鸡肉','鸡翅','鸡爪','鸡腿','鲫鱼','草鱼','胖头鱼','鲢鱼','螃蟹','大闸蟹','土豆','青椒','洋葱'];
		if($data&&count($data)>1)
			$re[1]['data']=json_decode(str_replace("/170_","/400x300_",json_encode($data)));
		$this->retAccess ($re);
	}
	public function menu_get() {
		if (! empty ( $this->get ( 'name' ) )) {
			$index = empty ( $this->get ( 'index' ) ) ? 1 : $this->get ( 'index' );
			$offset = @$this->get ( 'pageSize' );
			$num = ($index * $offset) - $offset;
			$data = $this->Menu_model->get_menuByName ( urldecode ( $this->get ( 'name' ) ), $num, $offset );
			$this->retAccess ( $data );
		}
		if (! empty ( $this->get ( 'id' ) )) {
			$condition ['ID'] = $this->get ( 'id' );
			$data = $this->Menu_model->get_menu ( $condition );
		}
		$data['material']=json_decode($data['material']);
		$data['step']=json_decode($data['step']);
		$data['minor']=json_decode($data['minor']);
		$data['tags']=json_decode($data['tags']);
		$this->retAccess ( $data );
	}
	public function menus_get() {
		$index = empty ( $this->get ( 'index' ) ) ? 1 : $this->get ( 'index' );
		$offset = @$this->get ( 'pageSize' );
		$num = ($index * $offset) - $offset;
		$data ['total'] = 0;
		$data ['data'] = $this->Menu_model->get_menus ( urldecode ( $this->get ( 'name' ) ), $num, $offset );
		if ($index == 1) {
			$data ['total'] = $this->Menu_model->get_menusCounts ( urldecode ( $this->get ( 'name' ) ) );
		}
		$this->retAccess ( $data );
	}
	public function stack_get() {
		if( $this->get ( 'top' )){
			$sort=$this->get ( 'sort' );
			$sort||$sort="favo_count";
			$data = $this->Stack_model->get_menusTop($this->get ( 'top' ),$sort);
			$this->retAccess ( $data );
		}
		if ($this->get ( 'id' )) {
			$data = $this->Stack_model->get_menu ( $this->get ( 'id' ) );
			$data['material']=json_decode($data['material']);
			$data['step']=json_decode($data['step']);
			$data['minor']=json_decode($data['minor']);
			$data['tags']=json_decode($data['tags']);
			$this->retAccess ( $data );
		}
		$index = empty ( $this->get ( 'index' ) ) ? 1 : $this->get ( 'index' );
		$offset = @$this->get ( 'pageSize' );
		$num = ($index * $offset) - $offset;
		$data ['data'] = $this->Stack_model->get_menus( $this->get ( 'type' ), urldecode ( $this->get ( 'tag' ) ), $num, $offset );
		
		if ($index == 1) {
			$data ['total'] = 200; // $this->Stack_model->get_menusCounts($name,$value);
		}
		//$this->request(base_url().'/index.php/api/SetKey/key',$para['tag']=$this->get ( 'tag' ));
		$this->retAccess ( $data );
	}
}
?>