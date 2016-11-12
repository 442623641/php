<?php
class Role_menu_model extends CI_Model{

	function __construct(){

		parent::__construct();

	}

	function search($id){

		$query = $this->db->get('role_menu',$id);

		return $query;//这里返回的是一个数组，可以通过$query['id'],$query['title']//进行访问

	}
	
	function searchMenuByRoleId($id){

		$this->db->where('role_id', $id); 
		$this->db->where('status', 1);  
        $this->db->select('*');  
        $query = $this->db->get('role_menu');  
        return $query->result_array(); 

	}
	
	function getAllMenu(){
		$this->db->where('status', 1);
		$this->db->where('node_level', 2);
		$query = $this->db->get('menu');  
        return $query->result_array(); 
	}

	function add($arry){

		$this->db->insert('role_menu',$arry);

		return $this->db->affected_rows();//返回影响行数，如果有自动增长字段，则返回新的增长id

	}

	function edit($id,$arry){

		$this->db->update('role_menu',$arry,array('id'=>$id));//这里的ID可以提交过来也可以，post过来

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('id',$id);

		$this->db->delete('role_menu');

	}
	
	function deleteByRoleId($id){//删除对应ID信息

		$this->db->where('role_id',$id);

		$this->db->delete('role_menu');

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
//		$this->load->Model("role_menu");//调用role_menu模型
//
//		$data = $this->role_menu->read($id);//调用模型read方法，参数为$id
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

