<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class User_role extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/User_role_model');
	}
	
    public function insert(){  
		$user_id = $this->input->post('user_id');//获取用户编号
		$role_ids = $this->input->post('role_ids');//获取角色编号
		$role_arry = explode(",",$role_ids);
		$this->User_role_model->deleteByUserId($user_id); 
		for ($i = 0; $i < count($role_arry); $i++) 
		{ 
			$arr = array('user_id'=>$user_id, 'role_id'=>$role_arry[$i],'status'=>1);
			$this->User_role_model->add($arr); 
		} 
		$this->updatePage($user_id,1);
    }  
      
	public function updatePage($user_id,$flag){
		$this->load->Model('admin/User_model');
		$data1['user_role'] = $this->User_role_model->searchRoleByUserId($user_id);  //array_merge
		$data2['roles'] = $this->User_role_model->getAllRole();
		$data3['user_info'] = $this->User_model->search($user_id);
		$data4 = array('flag' => $flag);
		$data = array_merge($data1,$data2,$data3,$data4);
		$this->load->view('admin/user_role/user_role.html',$data);
	}
    
    public function update(){  
        
        $id = $this->input->post('id');//获取用户编号
		$user_id = $this->input->post('user_id');//获取用户编号
		$role_id = $this->input->post('role_id');//获取角色编号
		
        $arr = array('user_id'=>$user_id, 'role_id'=>$role_id); 
        $this->User_role_model->edit($id, $arr);  
    }  
      
    public function select(){  
        $id = $this->input->post('id');
        $info = $this->User_role_model->search($id);  
        echo $info[0]->id;  
    }  
      
    public function delete(){  
        $id = $this->input->post('id'); 
        $this->User_role_model->delete($id);  
    }  
}  
