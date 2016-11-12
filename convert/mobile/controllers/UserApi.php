<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 交易记录API
 */
class UserApi extends Home_Controller
{
	public function __construct(){
		parent::__construct();
	}

	//记录分页显示
	public function records(){
		$params = $this->input->post();
		$page = intval($params['pageNum']);
		$type = intval($params['type']);
		$pageSize = 5;
		$offset = $page * $pageSize;
		
		switch ($type) {
			case 1:
				$sql = "select * from charge limit {$offset},{$pageSize}";
				$total = $this->db->count_all_results('charge');
				break;
			default:
				$sql = "select * from charge where charge_type = {$type} limit {$offset},{$pageSize}";
				$total = $this->db->where("charge_type={$type}")->count_all_results('charge');
				break;
		}

		$records = $this->db->query($sql)->result_array();
		$response['total'] = $total;
		$response['totalPage'] = ceil($total/$pageSize);
		$response['pageSize'] = $pageSize;
		$response['list'] = $records;

		//计算指定表的总记录数
		
		$response = json_encode($response);
		echo $response;
	}

	//搜索记录,多加了时间条件
	public function search(){
		$params = $this->input->get();

		$page = intval($params['pageNum']);
		$type = intval($params['type']);
		$start = strtotime($params['start']);
		$end = strtotime($params['end']);

		//时间为空，默认选取所有记录
		//开始时间为空，默认选取到结束时间所有记录;
		//结束时间为空，默认结束时间为至今

		$pageSize = 5;
		$offset = $pageSize * $page;

		switch ($type) {
			case 1:
				$sql = "select * from charge where charge_time between {$start} AND {$end} limit {$offset},{$pageSize}";
				$total = $this->db->where("charge_time between {$start} AND {$end}")->count_all_results('charge');
				break;
			default:
				$sql = "select * from charge where charge_type = {$type} AND charge_time between {$start} AND {$end} limit {$offset},{$pageSize}";
				$total = $this->db->where("charge_type={$type} AND charge_time between {$start} AND {$end}")->count_all_results('charge');
				break;
		}

		$records = $this->db->query($sql)->result_array();
		$response['total'] = $total;
		$response['totalPage'] = ceil($total/$pageSize);
		$response['pageSize'] = $pageSize;
		$response['list'] = $records;

		$response = json_encode($response);
		echo $response;
	}


}