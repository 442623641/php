<?php
class Menu_model extends CI_Model{

	function __construct(){

		parent::__construct();
		$this->load->database();
	}

	function search($id){         
		$this->db->where('node_id', $id);  
        $this->db->select('*');  
        $query = $this->db->get('menu');  
        return $query->row_array();
	}
	
	function countMenu(){
		$query = $this->db->query("SELECT COUNT(*) menu_num FROM menu");
		return $query->row_array(); 
	}
	
	function countMenuByCondition($node_name,$node_level){
		$sql="SELECT COUNT(*) menu_num FROM menu where 1=1 ";
		if(!""==$node_name){
			$sql = $sql." and node_name='".$node_name."'";
		}
		if(!""==$node_level){
			$sql = $sql." and node_level='".$node_level."'";
		}
		$query = $this->db->query($sql);
		return $query->row_array(); 
	}
	
	function searchByCondition($node_name,$node_level,$num,$offset){ 
		if(!""==$node_name){
			$this->db->where('node_name=',$node_name);
		}
		if(!""==$node_level){
			$this->db->where('node_level=',$node_level);
		}
        $query = $this->db->get('menu',$num,$offset);  
        return $query->result_array(); 
	}
	
	function searchAll($num,$offset){
		$this->db->order_by('node_level','asc');  
		$this->db->order_by('order_num','asc');
		$this->db->where('status',1);       
        $query = $this->db->get('menu',$num,$offset);  
        return $query->result_array(); 
	}
	
	function getAll(){         
        $query = $this->db->get('menu');  
        return $query->result_array(); 
	}

	function add($arr){
		$this->db->insert('menu',$arr);
		return $this->db->affected_rows();//返回影响行数，如果有自动增长字段，则返回新的增长id

	}

	function edit($node_id,$arr){

        $this->db->where('node_id', $node_id);  
        $this->db->update('menu', $arr);  

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('node_id',$id);

		$res= $this->db->delete('menu');
		return $res;
	}
	
	function loadParentMenu(){
		$this->db->order_by('order_num','asc');
		$this->db->where('node_level',1);
		$this->db->where('status',1);
	    $query = $this->db->get('menu');  
        return $query->result_array(); 
	}

	function getNodelevelByNodeId($id){
		$this->db->where('node_id', $id);
		$this->db->where('status',1);  
        $this->db->select('node_level');  
        $query = $this->db->get('menu');  
        return $query->row_array();
	}
}

//调用模型model 在控制其中执行，
//
//<?php
//
//class Pages extends CI_Controller {
//
//	function __construct() {
//
//		parent::__construct();
//
//	}
//
//	public function read($id) {
//
//		$this->load->Model("partner");//调用partner模型
//
//		$data = $this->partner->read($id);//调用模型read方法，参数为$id
//
//		$this->load->view('pages',$data);//调用视图pages，并传递参数为返回来的新闻$data
//
//	}
//
//}
//
//?
//
////调用模型实际方法为 $this->load->model('Model_name');
//
//$this->Model_name->function(); 可以对对象起别名 $this->load->model('Model_name',
//'newModel_name'); $this->newModel_name->function();


