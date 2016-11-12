<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class Menu extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/Menu_model');
		$this->load->library('form_validation');
	}
	
	public function index(){
		$this->load->library('Pagination');
	    $res = $this->Menu_model->countMenu();
	    $config['base_url'] = base_url().'xk.php/admin/menu/index';
	    $config['total_rows']=$res['menu_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->Menu_model->searchAll($config['per_page'],$this->uri->segment(4));
		$this->load->view('admin/menu/menu.html',$data);
	}

	
	public function addPage(){
		$data['result'] = $this->Menu_model->loadParentMenu();
		$this->load->view('admin/menu/menu_add.html',$data);
	}
	
	public function editPage(){
		$url = $_SERVER['REQUEST_URI'];
		$index = strpos($url,'='); 
		$menu_id = substr($url, $index+1);
		$info = $this->Menu_model->search($menu_id);  
		$data['result'] = $this->Menu_model->loadParentMenu();
		$data['result']['info'] = $info;
		$this->load->view('admin/menu/menu_edit.html',$data);
	}
	
    public function insert(){  
    	$this->form_validation->set_rules('parent_node_id','父节菜单','required'); 
    	$this->form_validation->set_rules('node_name','菜单名称','required'); 
    	$this->form_validation->set_rules('node_url','链接','required'); 
    	$this->form_validation->set_rules('order_num','排序','required');
    	if ($this->form_validation->run() == false){
    		
			$this->load->view('admin/menu/menu_add.html');
			
		} else{
	        $parent_node_id = $this->input->post('parent_node_id'); 
	        $pInfo = $this->Menu_model->getNodelevelByNodeId($parent_node_id);
        	$node_level = $pInfo['node_level']+1;
			$node_name = $this->input->post('node_name');        			
			$node_url = $this->input->post('node_url');	 
			$order_num = $this->input->post('order_num');
	        $arr = array('parent_node_id'=>$parent_node_id, 'node_name'=>$node_name,'node_url'=>$node_url,'node_level'=>$node_level,'order_num'=>$order_num,'status'=>1);  

	        $this->Menu_model->add($arr);  
			redirect('admin/menu/index');
		}
    }  
    
    
	function do_upload()
	{
	  $config['upload_path'] = '././uploadfile/admin';
	  $config['allowed_types'] = 'gif|jpg|png';
	  $config['max_size'] = '100';
	  $config['max_width']  = '1024';
	  $config['max_height']  = '768';
	  
  
  	  $this->load->library('upload', $config);
	  if ( ! $this->upload->do_upload())
	  {
	   	$error = array('error' => $this->upload->display_errors());
	   	return false;
	  } 
	  else
	  {
	  	$data = array('upload_data' => $this->upload->data());
	  	return $data['upload_data']['file_name'];
	  }

	}
    
    public function update(){    
        $menu_id = $this->input->post('node_id');
        $parent_node_id = $this->input->post('parent_node_id'); 
        $pInfo = $this->Menu_model->getNodelevelByNodeId($parent_node_id);
        $node_level = $pInfo['node_level']+1;
		$node_name = $this->input->post('node_name');        			
		$node_url = $this->input->post('node_url');	 
		$order_num = $this->input->post('order_num');
        $arr = array('parent_node_id'=>$parent_node_id, 'node_name'=>$node_name,'node_url'=>$node_url,'node_level'=>$node_level,'order_num'=>$order_num,'status'=>1); 
		
        $this->Menu_model->edit($menu_id, $arr);  
		$this->index();
    }  
      
    public function select(){ 
    	$node_name = $this->input->post('node_name');
        $node_level = $this->input->post('node_level');
        
    	$this->load->library('Pagination');
	    $res = $this->Menu_model->countMenuByCondition($node_name,$node_level);
	    $config['base_url'] = base_url().'xk.php/admin/menu/select';
	    $config['total_rows']=$res['menu_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->Menu_model->searchByCondition($node_name,$node_level,$config['per_page'],$this->uri->segment(4));
        $this->load->view('admin/menu/menu_list.html',$data); 
    }  

    public function delete(){  
        $menu_id = $this->input->post('menu_id');
        $row = $this->Menu_model->delete($menu_id);  
        if($row===true){
        	echo 1;
        }else{
        	echo 0;
        }
    } 
    
    
}  