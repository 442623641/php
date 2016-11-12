<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class Partner extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/Partner_model');
		$this->load->library('form_validation');
	}
	
	public function index(){
		$this->load->library('Pagination');
	    $res = $this->Partner_model->countPartner();
	    $config['base_url'] = base_url().'xk.php/admin/partner/index';
	    $config['total_rows']=$res['partner_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->Partner_model->searchAll($config['per_page'],$this->uri->segment(4));
		$this->load->view('admin/partner/partner.html',$data);
	}

	
	public function addPage(){
		$this->load->view('admin/partner/partner_add.html');
	}
	
	public function editPage(){
		$url = $_SERVER['REQUEST_URI'];
		$index = strpos($url,'='); 
		$partner_id = substr($url, $index+1);
		$info = $this->Partner_model->search($partner_id);  
		$this->load->view('admin/partner/partner_edit.html',$info);
	}
	
    public function insert(){  
    	$this->form_validation->set_rules('partner_name','合作者名称','required|is_unique[partner.partner_name]'); 
    	$this->form_validation->set_rules('url','链接','required'); 
    	$this->form_validation->set_rules('order_num','序号','required|numeric'); 
    	if ($this->form_validation->run() == false){
    		
			$this->load->view('admin/partner/partner_add.html');
			
		} else{
			
	        $isUp =  $this->do_upload();
			if($isUp===false){
				$pic = '';
			}else{
				$pic = 'uploadfile/admin/'.$isUp;
				
			}  
	        $partner_name = $this->input->post('partner_name'); //合作者名称
			$url = $this->input->post('url');        			//链接
			$order_num = $this->input->post('order_num');	 	//排序
	        $arr = array('partner_name'=>$partner_name, 'pic'=>$pic,'url'=>$url,'order_num'=>$order_num,'status'=>1);  

	        $this->Partner_model->add($arr);  
	        $this->generateStaticPage();
	        redirect('admin/partner/index');
	        //$data['result'] = $this->Partner_model->getAll();
			//$this->load->view('admin/partner/partner.html',$data);
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
		$isUp =  $this->do_upload();
		if($isUp===false){
			$pic = $this->input->post('pic');
		}else{
			$pic = 'uploadfile/admin/'.$isUp;
		}    
        $partner_id = $this->input->post('partner_id');
        $partner_name = $this->input->post('partner_name'); //合作者名称
		$url = $this->input->post('url');        			//链接
		$order_num = $this->input->post('order_num');	 	//排序
		
        $arr = array('partner_name'=>$partner_name, 'pic'=>$pic,'url'=>$url,'order_num'=>$order_num);
        $this->Partner_model->edit($partner_id, $arr);  
        $this->generateStaticPage();
        redirect('admin/partner/index');
        //$data['result'] = $this->Partner_model->getAll();
		//$this->load->view('admin/partner/partner.html',$data);
    }  
      
    public function select(){ 
        $partner_id = $this->input->post('partner_id');
        $info = $this->Partner_model->search($partner_id);  
        echo $info[0]->partner_name;  
    }  

    public function delete(){  
        $partner_id = $this->input->post('partner_id'); 
        $row = $this->Partner_model->delete($partner_id);  
        if($row===true){
        	echo 1;
        }else{
        	echo 0;
        }
    } 

       #生成静态页面 --
	function generateStaticPage(){
		$data['result'] = $this->Partner_model->getAll();
		ob_start();	
		$this->load->view('template/partner.html',$data);
		$content = ob_get_contents();//取得php页面输出的全部内容
		$fp = fopen(FCPATH.'web/partner.html', "w");
		fwrite($fp, $content);
		fclose($fp);
	}
    
    
}  