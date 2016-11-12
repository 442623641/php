<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户购彩模型（自购）
class LotteryChase_model extends CI_Model{
	const TBL_CHASEUSER = 'chaseuser';
	const TBL_LOTTERYCHASE = 'lotterychase';
	const TBL_LOTTERYCHASEENTITY = 'lotterychaseentity';
	#添加追号订单
	public function add_chaseUser($data){
		return $this->db->insert(self::TBL_CHASEUSER,$data);
		
	}
	#添加追号订单
	public function add_lotteryChase($data,$dataEntity){
		
		$this->db->trans_start();
		$this->db->insert(self::TBL_LOTTERYCHASE,$data);
		$this->db->insert(self::TBL_LOTTERYCHASEENTITY,$dataEntity);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
		   return false;
		}
		return true;
	}
	#获取用户追号信息
	public function get_chaseUser($id){
		$this->db->where('ID=',$id);
        $query = $this->db->get(self::TBL_CHASEUSER);  
        return $query->first_row('array'); 
	}
	#获取用户追号信息
	public function update_lotteryUser($condition,$data){
		$data['updateDate']=date('y-m-d H:i:s',time());
        $this->db->where($condition);
        $this->db->update(self::TBL_LOTTERYCHASE,$data);
        return $this->db->affected_rows();
	}  
	#获取用户追号列表
	public function get_chaseUsers($condition,$num,$offset){
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');  
		$this->db->order_by('updateDate','desc');
        $query = $this->db->get(self::TBL_CHASEUSER);  
        return $query->result_array(); 
	} 
	#获取用户已追号订单列表
	public function get_lotteryChaseList($condition,$num,$offset){
		$sql = "a.type,a.isWinners,a.state as chaseState,
		a.totalPhases,a.currentIssue,a.money as totalMoney,
		a.realMoney,a.multiple as chaseMultiple,
		a.createDate as chaseCreateDate,b.ID,b.lotteryID,b.money,b.multiple,b.index,b.state,b.issue,b.winMoney";
		$this->db->select($sql);
		$this->db->from('lotterychase as b');
		$this->db->join('chaseuser as a','a.ID= b.chaseID','left');
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('b.createDate','desc');  
		$this->db->order_by('b.updateDate','desc');
        $query = $this->db->get();  
        return $query->result_array(); 
	} 	
	#获取用户已追号订单详情
	public function get_lotteryChaseJoinChaseUserJointEntity($id){
		$sql = "a.issues,a.type,a.isWinners,a.state as chaseState,a.winMoney as chaseWinMoney,
		a.totalPhases,a.chasePhases,a.currentIssue,a.money as totalMoney,
		a.realMoney,b.chaseID,b.money,b.multiple,b.index,b.state,b.issue,b.winMoney,c.runNumbers,a.text";
		$this->db->select($sql);
		$this->db->from('lotterychase as b');
		$this->db->join('chaseuser as a','a.ID= b.chaseID','left');
		$this->db->join('lotterychaseentity as c','b.ID= c.lotteryUserID','left');
		$this->db->where('b.ID=',$id);
		$query = $this->db->get();  
        return $query->first_row('array'); 
	}
	#获取用户已追号订单列表
	public function get_lotteryChases($condition){
		$this->db->limit($offset,$num);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');  
		$this->db->order_by('updateDate','desc');
        $query = $this->db->get(self::TBL_LOTTERYCHASE);  
        return $query->result_array(); 
	}
	#获取用户已追号订单列表
	public function get_lotteryChaseJoinEntity($chaseID){
		$sql = "a.state,a.money,a.issue,a.winMoney,a.multiple,b.runNumbers";
		$this->db->select($sql);
		$this->db->from('lotterychase as a');
		$this->db->join('lotterychaseentity as b','a.ID= b.lotteryUserID','left');
		$this->db->where('a.chaseID=',$chaseID);
		$this->db->order_by('a.issue'); 
        $query = $this->db->get();
        return $query->result_array(); 
	}
	#获取用户已追号订单详情
	public function get_detail($condition){
       return $this->db->where($condition)->get(self::TBL_LOTTERYCHASEENTITY);    
	}
	#获取用户已追号订单详情
	public function get_lotteryChaseEntity($lotteryUserID){
       $query= $this->db->where('lotteryUserID=',$lotteryUserID)->get(self::TBL_LOTTERYCHASEENTITY);   
       return $query->first_row('array'); 
	}
	#更新订单信息
	public function update_chaseUser($data,$id){	
		$data['updateDate']=date('Y-m-d H:i:s',time());
        $this->db->where('id=',$id);
        $this->db->update(self::TBL_CHASEUSER,$data);
        return $this->db->affected_rows()>0;
	}
}