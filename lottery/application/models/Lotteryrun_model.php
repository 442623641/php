<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//开奖信息
class Lotteryrun_model extends CI_Model{
	public function get_lotteryRuns($table,$num,$offset){
		$run_db=$this->load->database('caipiaokong', TRUE);
		$run_db->limit($offset,$num);
		$run_db->order_by('LotteryDate','desc');  
        $query = $run_db->get($table);  
        return $query->result_array();
	}
	public function get_lotteryRunOfLast($condition){
		$run_db=$this->load->database('caipiaokong', TRUE);
		$sql = "lotteryID as LotteryType,issue as LotteryIssue,
		dt as LotteryDate,num as LotteryValue,order_num as LotteryOrderValue";
		$run_db->select($sql);
		$run_db->from('currentlottery');
		$run_db->where($condition);
		$query = $run_db->get();
		return $query->result_array();
	}
}