<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
class Role extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model("admin/Role_model"); 
		$this->load->library('form_validation');
	}
	
	public function index(){
		$this->load->library('Pagination');
	    $res = $this->Role_model->countRole();
	    $config['base_url'] = base_url().'xk.php/admin/role/index';
	    $config['total_rows']=$res['role_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->Role_model->searchAll($config['per_page'],$this->uri->segment(4));
		$this->load->view('admin/role/role.html',$data);
	}

	
	public function addPage(){
		$this->load->view('admin/role/role_add.html');
	}
	
	public function editPage(){
		$url = $_SERVER['REQUEST_URI'];
		$index = strpos($url,'='); 
		$role_id = substr($url, $index+1);
		$info = $this->Role_model->search($role_id);  
		$this->load->view('admin/role/role_edit.html',$info);
	}
		
    public function insert(){  
        $this->form_validation->set_rules('role_name','角色名','required|is_unique[role.role_name]'); 
    	$this->form_validation->set_rules('order_num','排序','required'); 
    	if ($this->form_validation->run() == false){
			$this->load->view('admin/role/role_add.html');
		}else{
			$role_name = $this->input->post('role_name');//获取角色名称
			$des = $this->input->post('des');//获取角色描述
			$order_num = $this->input->post('order_num');
			
			$arr = array('role_name'=>$role_name, 'des'=>$des,'order_num'=>$order_num,'status'=>1); 
	        $this->Role_model->add($arr); 
	        redirect('admin/role/index');
		}       	
 

    }  
      
    public function update(){  
        
        $role_id = $this->input->post('role_id');
		$role_name = $this->input->post('role_name');//获取角色名称
		$des = $this->input->post('des');//获取角色描述
		$order_num = $this->input->post('order_num');
		
        $arr = array('role_name'=>$role_name, 'des'=>$des,'order_num'=>$order_num);
        $this->Role_model->edit($role_id, $arr);
		redirect('admin/role/index');
    }  
      
    public function select(){  
    	$role_name = $this->input->post('role_name');
        
    	$this->load->library('Pagination');
	    $res = $this->Role_model->countRoleByCondition($role_name);
	    $config['base_url'] = base_url().'xk.php/admin/role/select';
	    $config['total_rows']=$res['role_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->Role_model->searchByCondition($role_name,$config['per_page'],$this->uri->segment(4));
        $this->load->view('admin/role/role_list.html',$data);
    }  
      
    public function delete(){  
        $role_id = $this->input->post('role_id'); 
       	$row = $this->Role_model->delete($role_id); 
       	
        if($row===true){
        	echo 1;
        }else{
        	echo 0;
        }
    }  
}  
