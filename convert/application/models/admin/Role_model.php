<?php
class Role_model extends CI_Model{

	function __construct(){

		parent::__construct();
	}

	function search($id){         
		$this->db->where('role_id', $id);  
        $this->db->select('*');  
        $query = $this->db->get('role');  
        return $query->row_array(); 
	}
	
	function countRole(){
		$query = $this->db->query("SELECT COUNT(*) role_num FROM role");
		return $query->row_array(); 
	}
	
	function countRoleByCondition($role_name){
		$sql="SELECT COUNT(*) role_num FROM role where 1=1 ";
		if(!""==$role_name){
			$sql = $sql." and role_name='".$name."'";
		}
		$query = $this->db->query($sql);
		return $query->row_array(); 
	}
	
	function searchAll($num,$offset){         
        $query = $this->db->get('role',$num,$offset);  
        return $query->result_array(); 
	}
	
	function searchByCondition($role_name,$num,$offset){ 
		if(!""==$role_name){
			$this->db->where('role_name=',$name);
		}
        $query = $this->db->get('role',$num,$offset);  
        return $query->result_array(); 
	}

	function add($arr){
		$this->db->insert('role',$arr);

	}

	function edit($role_id,$arr){

        $this->db->where('role_id', $role_id);  
        $this->db->update('role', $arr);  

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('role_id',$id);

		$res = $this->db->delete('role');
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
//		$this->load->Model("users");//调用users模型
//
//		$data = $this->users->read($id);//调用模型read方法，参数为$id
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

