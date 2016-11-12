<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
  
class news extends CI_Controller {  
	public function __construct(){
		parent::__construct();
		$this->load->Model('admin/News_model');
		$this->load->library('form_validation');
	}
	
	public function index(){
		$this->load->library('Pagination');
	    $res = $this->News_model->countNews();
	    $config['base_url'] = base_url().'xk.php/admin/news/index';
	    $config['total_rows']=$res['news_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->News_model->searchAll($config['per_page'],$this->uri->segment(4));
		$this->load->view('admin/news/news.html',$data);
	}
	
	public function addPage(){
		$this->load->view('admin/news/news_add.html');
	}
	
	public function editPage(){
		$url = $_SERVER['REQUEST_URI'];
		$index = strpos($url,'='); 
		$news_id = substr($url, $index+1);
		$info = $this->News_model->search($news_id);  
		$this->load->view('admin/news/news_edit.html',$info);
	}
	
    public function insert(){ 
    	$this->form_validation->set_rules('title','标题','required'); 
    	$this->form_validation->set_rules('news_type','类型','required'); 
    	$this->form_validation->set_rules('content','内容','required'); 
    	if ($this->form_validation->run() == false){
			$this->load->view('admin/news/news_add.html');
		} else{
			$title = $this->input->post('title');//获取提交过来的新闻title
			$content = $this->input->post('content');//获取提交过来的内容，推荐这种方法
			$news_type = $this->input->post('news_type');
			
			$data = $this->session->all_userdata();
			$username = $data['true_name'];
	        $arr = array('user_name'=>$username,'title'=>$title, 'content'=>$content,'news_type'=>$news_type,'status'=>1,'search_count'=>0,'publish_time'=>date('y-m-d h:i:s',time()));  
	        $this->News_model->add($arr); 
	        redirect('admin/news/index');
	        //$data['result'] = $this->News_model->searchAll();
			//$this->load->view('admin/news/news.html',$data); 
		}

    }  
      
    public function update(){          
        $news_id = $this->input->post('news_id');
		$title = $this->input->post('title');//获取提交过来的新闻title
		$content = $this->input->post('content');//获取提交过来的内容，推荐这种方法
		$news_type = $this->input->post('news_type');

        $arr = array('title'=>$title,'content'=>$content,'news_type'=>$news_type);  
        $this->News_model->edit($news_id,$arr);  
        redirect('admin/news/index');
        //$data['result'] = $this->News_model->searchAll();
		//$this->load->view('admin/news/news.html',$data); 
    }  
      
    public function select(){  
    	$news_type = $this->input->post('news_type');
        $title = $this->input->post('title');
    	$this->load->library('Pagination');
	    $res = $this->News_model->countNewsByCondition($news_type,$title);
	    $config['base_url'] = base_url().'xk.php/admin/news/select';
	    //$config['page_query_string'] = TRUE;  
	    //$config['query_string_segment'] = 'news_type';
//	    if(!empty($news_type)){
//	    	$config['base_url'].="news_type=".$news_type."/";
//	    }
//    	if(!empty($title)){
//	    	$config['base_url'].="&title=".$title;
//	    }
	    $config['total_rows']=$res['news_num'];
	    $config['per_page']=5;
	    $config['num_links']=2;//定义当前页面的前后各有几个数字链接
	    //$config['use_page_numbers']=true;//URL中的数字显示第几页，否则，显示到达第几条
		$config['first_link'] = '首页';
		$config['last_link'] = '末页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';

	    $this->load->library("pagination");//载入
	    $pagres = $this->pagination->initialize($config);//初始化配置
		$data['result'] = $this->News_model->searchByCondition($news_type,$title,$config['per_page'],$this->uri->segment(4));
        $this->load->view('admin/news/news_list.html',$data); 
    }  
    
  

    public function delete(){  
        $news_id = $this->input->post('news_id'); 
        $row = $this->News_model->delete($news_id);
        if($row===true){
        	echo 1;
        }else{
        	echo 0;
        }  
    }  
}  