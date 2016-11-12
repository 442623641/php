<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class User extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/User_model');
		$this->load->library('form_validation');
		$this->load->helper('url');
	}
	
	public function index(){
		$this->load->library('Pagination');
	    $res = $this->User_model->countUser();
	    $config['base_url'] = base_url().'xk.php/admin/user/index';
	    $config['total_rows']=$res['user_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->User_model->searchAll($config['per_page'],$this->uri->segment(4));
		$this->load->view('admin/user/user.html',$data);
	}
	
	public function addPage(){
		$this->load->view('admin/user/user_add.html');
	}
	
	public function editPage(){
		$url = $_SERVER['REQUEST_URI'];
		$index = strpos($url,'='); 
		$user_id = substr($url, $index+1);
		$info = $this->User_model->search($user_id);  
		$this->load->view('admin/user/user_edit.html',$info);
	}
	
    public function insert(){  
        $this->form_validation->set_rules('account','账号','required|is_unique[users.account]'); 
    	$this->form_validation->set_rules('password','密码','required|min_length[6]'); 
    	$this->form_validation->set_rules('name','姓名','required'); 
    	$this->form_validation->set_rules('depart','部门','required'); 
    	if ($this->form_validation->run() == false){
			$this->load->view('admin/user/user_add.html');
		}else{
		    $username = $this->input->post('account');//获取用户账号
			$password = $this->input->post('password');//获取用户密码
			$name = $this->input->post('name');        //姓名
			$depart = $this->input->post('depart');	 //部门
	        $arr = array('account'=>$username, 'password'=>md5($password),'name'=>$name,'depart'=>$depart,'status'=>1,'create_time'=>date('y-m-d h:i:s',time()));  
	        $this->User_model->add($arr); 
	        redirect('admin/user/index');
	        //$data['result'] = $this->User_model->searchAll();
	        //$this->load->view('admin/user/user.html',$data);
		}
 
    }  
      
    public function update(){     
    	$flag = $this->input->post('flag'); 
        $user_id = $this->input->post('user_id');
        $username = $this->input->post('account');//获取用户账号
		$password = $this->input->post('password');//获取用户密码
		$name = $this->input->post('name');        //姓名
		$depart = $this->input->post('depart');	 //部门
		if(0===$flag){
			$arr = array('account'=>$username,'name'=>$name,'depart'=>$depart);
		}else{
			$arr = array('account'=>$username, 'password'=>md5($password),'name'=>$name,'depart'=>$depart);
		}		
        $this->User_model->edit($user_id, $arr); 
        redirect('admin/user/index');
    }  
      
    public function select(){  
    	$account = $this->input->post('account');
        $name = $this->input->post('name');
        $depart = $this->input->post('depart');
        
    	$this->load->library('Pagination');
	    $res = $this->User_model->countUserByCondition($account,$name,$depart);
	    $config['base_url'] = base_url().'xk.php/admin/user/select';
	    $config['total_rows']=$res['user_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->User_model->searchByCondition($account,$name,$depart,$config['per_page'],$this->uri->segment(4));
        $this->load->view('admin/user/user_list.html',$data);
    }  

    public function delete(){
        $user_id = $this->input->post('user_id'); 
        $row = $this->User_model->delete($user_id);
        if($row===true){
        	echo 1;
        }else{
        	echo 0;
        }
    }  
	
}  