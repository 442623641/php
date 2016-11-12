<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//商品模型
class Lotteryissue_model extends CI_Model{
	const TBL_LOTTERYISSUE = 'lotteryissue';
	const TBL_LOTTERYSTOP = 'lotterystop';
	const TBL_SHOP = 'shop';
	#获取当期期号
	public function getIssue($lotteryID){
		$condition['lotteryID']=$lotteryID;
		$query = $this->db->where($condition)->get(self::TBL_LOTTERYISSUE);
		return $query->row_array();
	}
	#获取休市时间
	public function getLotteryStop($lotteryID){
		$condition['lotteryID']=$lotteryID;
		$query = $this->db->where($condition)->get(self::TBL_LOTTERYSTOP);
		return $query->row_array();
	}
	#获取店铺信息
	public function getShop($condition){
		$this->db->where($condition);
		$this->db->order_by('RAND()');  
		$this->db->limit('1'); 
		$query = $this->db->get(self::TBL_SHOP);
		return $query->first_row('array');
	}
}