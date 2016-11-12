<?php defined('BASEPATH') or die('Access Denied');

#--------------------------------------------------------------
# 经销商默认控制器
# Home
#--------------------------------------------------------------
# yulei <464001224@qq.com>
# 2015-7-14
#--------------------------------------------------------------

class Home extends Home_Controller
{	
	/**
	 * 经销商管理首页
	 * @return [type] [description]
	 */
	public function index(){
		$data['name'] = $this->get_session('name');
		$this->load->view('index.html', $data);
	}

	/**
	 * 默认首页展示
	 * @return [type] [description]
	 */
	public function show(){
		$this->load->view('index_gl.html');
	}

	/**
	 * 订单管理
	 * @return [type] [description]
	 */
	public function order(){
		$this->load->view('order.html');
	}

	/**
	 * 商品管理-商品列表
	 * @return [type] [description]
	 */
	public function shopLists(){
		$this->load->view('shop_list.html');
	}

	/**
	 * 新增商品
	 */
	public function addShop(){
		$this->load->view('add_shop.html');
	}

	/**
	 * 商品分类
	 * @return [type] [description]
	 */
	public function shopCate(){
		$this->load->view('shop_cate.html');
	}

	/**
	 * 资金管理-资金明细
	 * @return [type] [description]
	 */
	public function account(){
		$this->load->view('account.html');
	}

	/**
	 * 下级管理-经销商
	 * @return [type] [description]
	 */
	public function salesAgent(){
		$this->load->view('jxsmd.html');
	}

	/**
	 * 下级管理-店铺
	 * @return [type] [description]
	 */
	public function shop(){
		$this->load->view('dpmd.html');
	}

	/**
	 * 下级管理-业绩查询
	 * @return [type] [description]
	 */
	public function grade(){
		$this->load->view('dpyj.html');
	}

	public function shopManage(){
		$this->load->view('shop_manage.html');
	}

	public function shopGrade(){
		$this->load->view('shop_grade.html');
	}

	//系统设置
	public function loginPass(){
		$data['name'] = $this->get_session('name');
		$this->load->view('dlmm.html', $data);
	}

	public function payPass(){
		$this->load->view('pay_pass.html');
	}

	public function infoCenter(){
		$this->load->view('info_center.html');
	}
}