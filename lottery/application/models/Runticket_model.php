<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//出票
class Runticket_model extends CI_Model{
	const TBL_UNTICKET_VIEW = 'unticket_view';
	//const TBL_UNTICKET_VIEW = 'test';
	#获取未出票列表
	public function get($condition,$size){
		$this->db->limit($size);
		$this->db->where($condition);
		$this->db->order_by('createDate','desc');
		$this->db->order_by('buyType');
		$query = $this->db->get(self::TBL_UNTICKET_VIEW);
		return $query->result_array();
	}
	public function update($ids,$state,$oldstate,$wherStr){
		$count=0;
		$idscount=0;
		$updateDate=date('Y-m-d H:i:s',time());
		$this->db->trans_begin();
		foreach ( $ids as $key => $val ){
			$val=substr_replace($val, '', -1);
			switch ($key)
			{
					case 'lotteryJoints':
						$this->db->query('update lotteryjoint SET state = '.$state.",updateDate='".$updateDate."' where and ID in(".$val.') '.$wherStr.'');
						$this->db->query('update lotteryjointentity SET state = '.$state.' where lotteryJointID in('.$val.')');
						$this->db->query('update lotteryparticipants SET state = '.$state.",updateDate='".$updateDate."' where lotteryJointID in(".$val.')');
						break;
					case 'lotteryUsers':
						$this->db->query('update lotteryuser SET state = '.$state.",updateDate='".$updateDate."' where state=".$oldstate." and ID in(".$val.') '.$wherStr.'');
						$this->db->query('update lotteryuserentity SET state = '.$state.' where state=0 and lotteryUserID in('.$val.')');
						break;
					case 'lotteryChases':
						$this->db->query('update lotterychase SET state = '.$state.",updateDate='".$updateDate."' where state=".$oldstate." and ID in(".$val.') '.$wherStr.'');
						$this->db->query('update lotterychaseentity SET state = '.$state.' where state=0 and lotteryUserID in('.$val.')');
						break;
					default:
						break;
			}
			$count+=$this->db->affected_rows();
			$idscount+=count($val);
		}
		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();
		} else {
			$this->db->trans_rollback();
			return 0;
		}
		return $count;
	}
}