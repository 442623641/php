<?php
class Partner_model extends CI_Model{

	function __construct(){

		parent::__construct();
		$this->load->database();
	}

	function search($id){         
		$this->db->where('partner_id', $id);  
        $this->db->select('*');  
        $query = $this->db->get('partner');  
        return $query->row_array();
	}
	
	function countPartner(){
		$query = $this->db->query("SELECT COUNT(*) partner_num FROM partner");
		return $query->row_array(); 
	}
	
	function searchAll($num,$offset){         
        $query = $this->db->get('partner',$num,$offset);  
        return $query->result_array(); 
	}
	
	function getAll(){     
		$this->db->order_by('order_num', 'asc'); 
        $query = $this->db->get('partner');  
        return $query->result_array(); 
	}

	function add($arr){
		$this->db->insert('partner',$arr);
		return $this->db->affected_rows();//返回影响行数，如果有自动增长字段，则返回新的增长id

	}

	function edit($partner_id,$arr){

        $this->db->where('partner_id', $partner_id);  
        $this->db->update('partner', $arr);  

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('partner_id',$id);

		$res= $this->db->delete('partner');
		return $res;
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


