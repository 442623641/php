<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class Role_menu extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/Role_menu_model');
	}
    public function insert(){  
		$role_id = $this->input->post('role_id');//获取用户编号
		$node_ids = $this->input->post('node_ids');//获取角色编号
		$node_arry = explode(",",$node_ids);
		$this->Role_menu_model->deleteByRoleId($role_id); 
		for ($i = 0; $i < count($node_arry); $i++) 
		{ 
			$arr = array('role_id'=>$role_id, 'node_id'=>$node_arry[$i],'status'=>1);
			$this->Role_menu_model->add($arr); 
		} 
		$this->updatePage($role_id,1);
    }  
      
	public function updatePage($role_id,$flag){
		$this->load->Model('admin/Role_model');
		$data1['role_menu'] = $this->Role_menu_model->searchMenuByRoleId($role_id);  //array_merge
		$data2['menus'] = $this->Role_menu_model->getAllMenu();
		$data3['role_info'] = $this->Role_model->search($role_id);
		$data4 = array('flag' => $flag);
		$data = array_merge($data1,$data2,$data3,$data4);
		$this->load->view('admin/role_menu/role_menu.html',$data);
	}
      
    public function update(){          
        $id = $this->input->post('id');
		$role_id = $this->input->post('role_id');//获取角色编号
		$node_id = $this->input->post('node_id');//获取菜单节点编号
		
        $arr = array('role_id'=>$role_id, 'node_id'=>$node_id);
        $this->Role_menu_model->edit($id, $arr);  
    }  
      
    public function select(){  
        $id = $this->input->post('id');
        $info = $this->Role_menu_model->search($id);  
        echo $info[0]->name;  
    }  

    public function delete(){  
        $id = $this->input->post('id');
        $this->Role_menu_model->delete($id);  
    }  
}  