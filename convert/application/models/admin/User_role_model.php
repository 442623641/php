<?php
class User_role_model extends CI_Model{

	function __construct(){

		parent::__construct();

	}

	function searchRoleByUserId($id){

		$this->db->where('user_id', $id); 
		$this->db->where('status', 1);  
        $this->db->select('*');  
        $query = $this->db->get('user_role');  
        return $query->result_array(); 

	}
	
	function getAllRole(){
		$query = $this->db->get('role');  
        return $query->result_array(); 
	}

	function add($arr){
		$this->db->insert('user_role',$arr);
	}

	function edit($id,$arr){

		$this->db->update('user_role',$arr,array('id'=>$id));//这里的ID可以提交过来也可以，post过来

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('id',$id);

		$this->db->delete('user_role');

	}
	
	function deleteByUserId($id){//删除对应ID信息

		$this->db->where('user_id',$id);

		$this->db->delete('user_role');

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
//		$this->load->Model("user_role");//调用user_role模型
//
//		$data = $this->user_role->read($id);//调用模型read方法，参数为$id
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

