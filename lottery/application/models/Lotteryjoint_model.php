<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩模型（自购）
class lotteryJoint_model extends CI_Model{
	const TBL_LOTTERJOINT = 'lotteryjoint';
	const TBL_LOTTERYJOINTENTITY = 'lotteryjointentity';
	const TBL_LOTTERYPARTICIPANTS = 'lotteryparticipants';
	#添加发起合买订单
	public function add_lotteryJoint($data,$dataEntity){
		
		$this->db->trans_start();
		$this->db->insert(self::TBL_LOTTERJOINT,$data);
		$this->db->insert(self::TBL_LOTTERYJOINTENTITY,$dataEntity);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
		   return false;
		}
		return true;
	}
	#添加合买订单
	public function add_lotteryParticipants($data){	
		return $this->db->insert(self::TBL_LOTTERYPARTICIPANTS,$data);
	}
	
	#获取参与的合买订单列表
	public function lotteryParticipantses($condition,$num,$offset){
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');  
        $query = $this->db->get(self::TBL_LOTTERYPARTICIPANTS);  
        return $query->result_array(); 
	}
	#获取参与的合买订单列表
	public function get_lotteryParticipantses($condition,$num,$offset){
		$sql = "a.issue,a.lotteryID,a.state as launchState,a.multiple,a.launcher,a.memo,a.schemeName,
		a.ensure,a.take,a.unit,a.publicType,a.money as launchMoney,a.sell,a.launcherBuy,a.stopRaiseTime,
		b.lotteryJointID,b.userID,b.money,b.winMoney,b.financeID";
		$this->db->select($sql);
		$this->db->from('lotteryjoint as a');
		$this->db->join('lotteryparticipants as b','a.ID= b.lotteryJointID','left');
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('b.updateDate','desc');  
		$this->db->order_by('b.createDate','desc');  
        $query = $this->db->get();  
        return $query->result_array();
	}
	#获取发起的合买
	public function get_lotteryJoint($id){
		$this->db->where('id=',$id);
        $query = $this->db->get(self::TBL_LOTTERJOINT);  
        return $query->first_row('array'); 
	}
	#获取发起的合买订单明细
	public function get_lotteryJointEntity($lotteryJointID){
		$this->db->where('lotteryJointID=',$lotteryJointID);
        $query = $this->db->get(self::TBL_LOTTERYJOINTENTITY);  
        return $query->first_row('array'); 
	}
	#获取参与的合买订单列表
	public function count_participants($condition){
		$this->db->where($condition);
      	return $this->db->count_all(self::TBL_LOTTERYPARTICIPANTS);      
	} 
	
	#获取发起的合买订单列表
	public function lotteryJointOfLaunchs($num,$offset){
		$this->db->limit($offset,$num);
		$this->db->where('sell<money');
		$this->db->where('stopRaiseTime>',date('y-m-d H:i:s',time())); 	
		$this->db->order_by('createDate','desc');  
        $query = $this->db->get(self::TBL_LOTTERJOINT);  
        return $query->result_array(); 
	} 
	#获取发起的合买订单列表
	public function get_lotteryJoints($condition,$num,$offset){
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');  
        $query = $this->db->get(self::TBL_LOTTERJOINT);  
        return $query->result_array(); 
	}

	public function lotteryJointDetailOf($lotteryJointID){
		$this->db->where('lotteryJointID=',$lotteryJointID);
		$this->db->order_by('createDate','desc');  
        $query = $this->db->get(self::TBL_LOTTERYJOINTENTITY);  
        return $query->result_array(); 
	} 
	#更新合买
	public function update_lotteryJoint($id,$data){	
		$data['updateDate']=date('y-m-d H:i:s',time());
        $this->db->where('id=',$id);
        $this->db->update(self::TBL_LOTTERJOINT,$data);
	}
}