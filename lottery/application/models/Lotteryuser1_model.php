<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩模型（自购）
class LotteryUser1_model extends CI_Model{
	const TBL_LOTTERYUSER_1 = 'lotteryuser_1';
	const TBL_LOTTERYUSERENTITY_1 = 'lotteryuserentity_1';
	#添加用户订单
	public function add_lotteryUser($data){
		//$this->db->trans_start();
		$this->db->insert(self::TBL_LOTTERYUSER_1,$data);
		return $this->db->affected_rows() > 0;
		//$this->db->insert(self::TBL_LOTTERYUSERENTITY_1,$dataEntity);
		//$this->db->trans_complete();
		//if ($this->db->trans_status() === FALSE)
		//{
		//  return false;
		//}
		
	}
	#自购订单信息
	public function get_lotteryUser($id){
		$this->db->where('ID=',$id);
        $query = $this->db->get(self::TBL_LOTTERYUSER_1);  
        return $query->first_row('array');
	}
	#自购订单记录
	public function get_lotteryUsers($condition,$num,$offset){
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');  
		$this->db->order_by('updateDate','desc');
        $query = $this->db->get(self::TBL_LOTTERYUSER_1);  
        return $query->result_array(); 
	}
	
	#订单详细信息
	public function get_lotteryUserEntity($lotteryUserID){
		$this->db->where('lotteryUserID=',$lotteryUserID);
		$this->db->order_by('createDate','desc');  
        $query = $this->db->get(self::TBL_LOTTERYUSERENTITY_1);  
        return $query->result_array(); 
	}


}