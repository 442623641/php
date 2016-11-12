<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Home_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->Model('admin/partner_model');
	}

	public function person(){	
		$this->load->view('personal_info.html');
	}

	public function join(){	
		$this->load->view('join.html');
	}

	public function umspay(){	
		$this->load->view('umspay.html');
	}

	public function us(){	
		$this->load->Model('admin/news_model');
		$data['news'] = $this->news_model->searchByType(1);
		$data['dyn'] = $this->news_model->searchByType(2);
		$this->load->view('about_us.html',$data);
	}

	public function newsList($type,$page){	
		$this->load->Model('admin/news_model');
		$data['news'] = $this->news_model->searchByCondition($type,'',10,($page-1)*10);	
		$t = '小康动态';
		if($type==2)
		{
			$t = ' 行业动态';
		}
		$countArr =  $this->news_model->countNewsByCondition($type,'');
		$tp = floor($countArr['news_num']/10)+1;
		$data['p']=array("total"=>$countArr['news_num'],"title"=>$t,"totalpages"=>$tp,"page"=>$page,"type"=>$type,"next"=>$page+1,"prev"=>$page-1);		
		$this->load->view('news.html',$data);
	}

	public function process(){	
		$this->load->view('process.html');
	}

	public function contact_us() {	
		$this->load->view('contact_us.html');
	}

	public function development(){	
		$this->load->view('development.html');
	}

	public function service(){	
		$data['title']='service';
		$this->load->view('service.html',$data);
	}
	
	public function partner(){		
		$this->load->view('partner.html');
	}

	public function news($id){
		$this->load->Model('admin/news_model');
		$data = $this->news_model->search($id);
		if($data==null)
		{
			error('该新闻已删除');
			exit();
		}
		$this->load->view('news_content.html',$data);
	}
}
?>
