<?php
class News_model extends CI_Model {

	function __construct() {

		parent::__construct();

	}

	function search($id) {

		$this->db->where('news_id', $id);
		$this->db->select('*');
		$query = $this->db->get('news');
		return $query->row_array();

	}

	//1小康动态，2 行业动态
	function searchByType($news_type) {
		$this->db->limit(6);
		$this->db->where('news_type', $news_type);
		$this->db->order_by('publish_time', 'desc');
		$this->db->select('*');
		$query = $this->db->get('news');
		return $query->result_array();
	}

	function searchAll($num, $offset) {
		$query = $this->db->get('news', $num, $offset);
		return $query->result_array();
	}

	function searchByCondition($news_type, $title, $num, $offset) {
		if (!"" == $news_type) {
			$this->db->where('news_type=', $news_type);
		}
		if (!"" == $title) {
			$this->db->where('title=', $title);
		}
		$query = $this->db->get('news', $num, $offset);
		return $query->result_array();
	}

	function countNews() {
		$query = $this->db->query("SELECT COUNT(*) news_num FROM news");
		return $query->row_array();
	}

	function countNewsByCondition($news_type, $title) {
		$sql = "SELECT COUNT(*) news_num FROM news where 1=1 ";
		if (!"" == $news_type) {
			$sql = $sql . " and news_type='" . $news_type . "'";
		}
		if (!"" == $title) {
			$sql = $sql . " and title='" . $title . "'";
		}
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function add($arry) {

		$this->db->insert('news', $arry);

		return $this->db->affected_rows(); //返回影响行数，如果有自动增长字段，则返回新的增长id

	}

	function edit($id, $arr) {

		$this->db->where('news_id', $id);
		$this->db->update('news', $arr);

		return $this->db->affected_rows(); //返回一想行数

	}

	function delete($id) {
//删除对应ID信息

		$this->db->where('news_id', $id);

		$res = $this->db->delete('news');
		return $res;
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
//		$this->load->Model("news");//调用news模型
//
//		$data = $this->news->read($id);//调用模型read方法，参数为$id
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
