<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩支付状态跟新模型
class Orderpay_model extends CI_Model{
	const PRO = 'pay_pro';
	//const TBL_LOTTERYUSERENTITY_1 = 'lotteryuserentity_1';
	#添加用户订单
	public function pay_pro_excute($id){
		$sql = "call ".self::PRO."('$id',@outcount)";
		//var_dump($sql);
		$this->db->query($sql);
		$result = $this->db->query("select @outcount as outcount");
		return $result->first_row('array');
		 
	}
}